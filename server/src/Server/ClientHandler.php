<?php

namespace Game\Server;

use Exception;
use Game\Db\Database;
use Game\Db\PlayerState;
use Game\Library\BiMap;
use Game\Library\Uuid;
use Ratchet\ConnectionInterface;
use React\Socket\ConnectorInterface;

class ClientHandler
{
    private $clients;
    private PlayerState $db;
    private BiMap $clientBiMap; //key playerId, value hash

    public function __construct(Database $db)
    {
        $this->clients = array();
        $this->clientBiMap = new BiMap();
        $this->db = new PlayerState($db);
    }

    public function addClient(ConnectionInterface $client, string $playerId, SocketServer $socketServer): bool
    {
        $hash = $this->getClientHash($client);
        if ($this->isInvalidValidClient($hash, $playerId)) {
            return false;
        }

        // one connection per client disconnect old instance
        if ($this->playerIdExists($playerId) && $this->playerIsConnected($playerId)) {
            $oldClient = $this->getClientByPlayerId($playerId);
            $socketServer->onClose($oldClient);
        }

        /* 
            if player does not exists register them, 
            if they do exist leave validateClient to update hash so other players are notified
        */
        if ((!$this->playerIdExists($playerId) && $this->registerUser($client, $playerId))
            || $this->playerIdExists($playerId)
        ) {
            $this->clients[$hash] = ($client);
            return true;
        }

        return false;
    }

    public function getClientHash(ConnectionInterface $client)
    {
        try {
            return $client->resourceId;
        } catch (\RuntimeException $e) {
            return "";
        }
    }

    public function getClientByPlayerId(string $playerId): ConnectionInterface | NULL
    {
        if (!$this->clientBiMap->hasKey($playerId)) {
            return NULL;
        }
        return $this->clients[$this->clientBiMap->getValue($playerId)];
    }

    public function playerIdExists(string $playerId): bool
    {
        return $this->clientBiMap->hasKey($playerId);
    }

    public function clientHasPlayerId(ConnectionInterface $client): bool
    {
        return $this->clientBiMap->hasValue($this->getClientHash($client));
    }

    public function clientExists(ConnectionInterface $client): bool
    {
        $hash = $this->getClientHash($client);
        return $this->hashExists($hash);
    }

    public function hashExists($hash): bool
    {
        return key_exists($hash, $this->clients) && $this->clientBiMap->hasValue($hash);
    }

    public function removeClient(ConnectionInterface $client): void
    {
        try {
            $hash = $this->getClientHash($client);
            if ($this->hashExists($hash)) {
                $playerId = $this->clientBiMap->getKey($hash);
                $this->clientBiMap->put($playerId, "");
                unset($this->clients[$hash]);
                $this->db->updateClientHash($playerId);
            }
        } catch (\Exception $e) {
            echo $e;
            return;
        }
    }

    public function playerIsConnected($playerId): bool
    {
        return $this->clientBiMap->getValue($playerId) !== "";
    }

    public function getClientByHash($hash): ConnectionInterface | NULL
    {
        return $this->clients[$hash];
    }

    public function deletePlayer($playerId): void
    {
        $this->db->deletePlayer($playerId);
    }

    public function getPlayerIdByClient(ConnectionInterface $client): string
    {
        return $this->clientBiMap->getKey($this->getClientHash($client));
    }

    private function registerUser(ConnectionInterface $client, string $playerId): bool
    {
        $hash = $this->getClientHash($client);
        if (!$this->db->getPlayerDataFromToken($playerId) && $this->saveUserToDb($playerId, $hash)) {
            $this->clientBiMap->put($playerId, $hash);
            return true;
        }
        return false;
    }

    private function updateHash(ConnectionInterface $client, $playerId, $hash): bool
    {
        $oldHash = $this->clientBiMap->getValue($playerId);
        if (key_exists($oldHash, $this->clients)) {
            $oldClient = $this->clients[$oldHash];
            $oldClient->close();
            unset($this->clients[$oldHash]);
        }
        $this->clients[$hash] = $client;
        $this->clientBiMap->removeKey($playerId);
        $this->clientBiMap->put($playerId, $hash);
        return $this->db->updateClientHash($playerId, $hash);
    }

    private function saveUserToDb(string $playerId, string $hash): bool
    {
        return $this->db->savePlayer($playerId, $hash);
    }

    private function isInvalidValidClient(string $hash, string $playerId): bool
    {
        return is_null($hash) || is_null($playerId) || $hash === "" || $playerId === "" || !Uuid::isUuid($playerId);
    }

    public function validateClient(ConnectionInterface $client, string $playerId): bool
    {
        $hash = $this->getClientHash($client);
        if ($this->isInvalidValidClient($hash, $playerId)) {
            $client->close();
            return false;
        }

        /*
        check if client has identifier,
        if identifier is not equal to supplied return false
        */
        if ($this->clientBiMap->hasValue($hash)) {
            return $playerId === $this->clientBiMap->getKey($hash);
        }

        //check if new client
        if ($this->db->playerExistsByToken($playerId)) {

            $this->updateHash($client, $playerId, $hash);
            return true;
        }

        return false;
    }
}
