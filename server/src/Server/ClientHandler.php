<?php

namespace Game\Server;

use Game\Db\Database;
use Game\Db\PlayerState;
use Game\Library\BiMap;
use Game\Library\Uuid;
use Ratchet\ConnectionInterface;

class ClientHandler
{
    private $clients;
    private PlayerState $db;
    private BiMap $clientBiMap; //key uuid, value hash

    public function __construct(Database $db)
    {
        $this->clients = array();
        $this->clientBiMap = new BiMap();
        $this->db = new PlayerState($db);
    }

    public function addClient(ConnectionInterface $client)
    {
        $this->clients[$this->getClientHash($client)] = ($client);
    }

    public function getClientHash(ConnectionInterface $client)
    {
        try {
            return $client->resourceId;
        } catch (\RuntimeException $e) {
            return "";
        }
    }

    public function getClientByPlayerId($playerId): ConnectionInterface
    {
        return $this->clients[$this->clientBiMap->getValue($playerId)];
    }

    public function removeClient($hash): void
    {
        if ($hash !== "" && $this->clientBiMap->hasValue($hash)) {
            $key = $this->clientBiMap->getKey($hash);
            $this->clientBiMap->put($key, "");
            unset($this->clients);
            $this->db->updateClientHash($key);
        }
    }

    public function clientIsConnected($playerId): bool
    {
        return $this->clientBiMap->getValue($playerId) !== "";
    }

    public function getClientByHash($hash): ConnectionInterface
    {
        return $this->clients[$hash];
    }

    public function getPlayerIdByClient(ConnectionInterface $client): string
    {
        return $this->clientBiMap->getKey($this->getClientHash($client));
    }

    private function registerUser(ConnectionInterface $client): bool
    {
        $uuid = Uuid::v4();
        $hash = $this->getClientHash($client);
        if ($hash === "") {
            return false;
        }
        $this->clientBiMap->put($uuid, $hash);
        $this->saveUserToDb($uuid, $hash);
        return true;
    }

    private function updateHash(string $playerId, string $hash)
    {
        $this->clientBiMap->removeKey($playerId);
        $this->clientBiMap->put($playerId, $hash);
        $this->db->updateClientHash($playerId, $hash);
    }

    private function saveUserToDb(string $playerId, string $hash)
    {
        $this->db->savePlayer($playerId, $hash);
    }

    public function validateClient(ConnectionInterface $client, string $playerId): bool
    {
        $hash = $this->getClientHash($client);
        if ($hash === "") {
            return false;
        }

        /*
        check if client has identifier,
        if identifier is not equal to supplied return false
        */
        if ($this->clientBiMap->hasValue($hash)) {
            return $playerId === $this->clientBiMap->getKey($hash);
        }

        //check if uuid is valid if not register
        if (!Uuid::isUuid($playerId)) {
            return $this->registerUser($client);
        }

        //check if new client
        if ($this->db->playerExistsByToken($playerId)) {
            $this->updateHash($playerId, $hash);
            return true;
        }

        return false;
    }
}
