<?php


namespace Game\Library\PrivateClass {
    class Pair
    {
        public $key;
        public $value;

        public function __construct($key, $value)
        {
            $this->key = $key;
            $this->value = $value;
        }

        public function increment()
        {
            $this->value = $this->value + 1;
            return $this;
        }

        public function decrement()
        {
            $this->value = $this->value - 1;
            return $this;
        }
    }
}

namespace Game\Library {

    use Game\Library\PrivateClass\Pair;

    class Lobby
    {
        private array $queue;

        public function __construct()
        {
            $this->queue = array();
        }

        public function queue($key)
        {
            if (array_key_exists($key, $this->queue)) {
                $this->queue[$key] = $this->queue[$key]->increment();
            } else {
                $this->queue += [$key => new Pair($key, 1)];
            }
        }

        public function shift()
        {
            $temp = array_reverse($this->queue);
            $result = array_pop($temp);
            $this->remove($result->key);
            if ($result->value > 1) {
                $this->queue += [$result->key => $result->decrement()];
            }
            return $result->key;
        }

        public function size(): int
        {
            return count($this->queue);
        }

        public function remove($key)
        {
            unset($this->queue[$key]);
        }

        public function isEmpty(): bool
        {
            return count($this->queue) === 0;
        }

        public function print()
        {
            return print_r($this->queue);
        }
    }
}
