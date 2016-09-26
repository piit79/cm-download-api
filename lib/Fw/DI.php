<?php

namespace Fw {


    class DI implements \ArrayAccess
    {
        /**
         * @var self
         */
        private static $instance = null;

        /**
         * @var DI\Service[]
         */
        private $services = array();

        /**
         * DI constructor - not used in singleton
         */
        protected function __construct()
        {
        }

        /**
         * Singleton get method
         * @return DI
         */
        public static function getInstance()
        {
            if (static::$instance == null) {
                static::$instance = new static();
            }
            return static::$instance;
        }

        /**
         * Return true if a DI service exists
         *
         * @param string $name service name
         * @return boolean true if the specified DI service exists
         */
        public function has($name)
        {
            return isset($this->services[$name]);
        }

        /**
         * Return a DI service
         *
         * @param string $name service name
         * @return mixed
         */
        public function get($name)
        {
            if ($this->has($name))
            {
                $service = $this->services[$name];
                return $service->get();
            }
            return null;
        }

        /**
         * Return a shared DI service. Subsequent calls to get will return the same instance
         *
         * @param string $name service name
         * @return DI\Service|null
         */
        public function getShared($name)
        {
            if ($this->has($name)) {
                $this->services[$name]->setShared();
                return $this->get($name);
            }
            return null;
        }

        /**
         * Return the definition of a DI service
         *
         * @param string $name service name
         * @return string|callable|false service definition
         */
        public function getRaw($name)
        {
            if ($this->has($name)) {
                return $this->services[$name]->getDefinition();
            }
            return null;
        }

        /**
         * Return a Fw\DI\Service instance
         *
         * @param string $name service name
         * @return DI\Service|null
         */
        public function getService($name)
        {
            if ($this->has($name)) {
                return $this->services[$name];
            }
            return null;
        }

        /**
         * Register a DI service
         *
         * @param string $name service name
         * @param string|callable $definition service definition
         * @param boolean $shared whether the service is shared
         */
        public function set($name, $definition, $shared = false)
        {
            if ($shared == null) {
                $shared = false;
            }
            $this->services[$name] = new DI\Service($name, $definition, $shared);
        }

        /**
         * Register a shared DI service
         *
         * @param string $name service name
         * @param string|callable $definition service definition
         */
        public function setShared($name, $definition)
        {
            $this->set($name, $definition, true);
        }

        /**
         * Attempt to set a DI service. Only succeeds if there is no such service yet
         *
         * @param string $name service name
         * @param string|callable $definition service definition
         * @param boolean|false $shared whether the service is shared
         * @return boolean returns true if service was registered
         */
        public function attempt($name, $definition, $shared = null)
        {
            if (!$this->has($name)) {
                $this->set($name, $definition, $shared);
                return true;
            }
            return false;
        }

        /**
         * Unregister a DI service
         *
         * @param string $name
         * @return boolean true if service was unregistered, false otherwise
         */
        public function remove($name)
        {
            if ($this->has($name)) {
                unset($this->services[$name]);
                return true;
            }
            return false;
        }

        /**
         * Magic method to get/set services using getters/setters
         *
         * @param string $method method name
         * @param mixed[] $arguments method arguments
         * @return boolean|DI\Service|null
         */
        public function __call($method, array $arguments)
        {
            if (substr($method, 0, 3) == "get") {
                $name = strtolower(substr($method, 3));
                return $this->get($name);
            } elseif (substr($method, 0, 3) == "set") {
                $name = strtolower(substr($method, 3));
                $definition = $arguments[0];
                if (isset($arguments[1])) {
                    $shared = $arguments[1];
                } else {
                    $shared = null;
                }
                $this->set($name, $definition, $shared);
            }
            throw new \RuntimeException("Method does not exist: " . $method);
        }

        /*
         * ArrayAccess interface methods
         */
        public function offsetExists($offset)
        {
            return $this->has($offset);
        }

        public function offsetGet($offset)
        {
            return $this->get($offset);
        }

        public function offsetSet($offset, $value)
        {
            $this->set($offset, $value);
        }

        public function offsetUnset($offset)
        {
            $this->remove($offset);
        }

    }

}
