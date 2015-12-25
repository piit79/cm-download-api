<?php

namespace Fw\DI {


    class Service
    {
        /**
         * @var string
         */
        private $name;

        /**
         * @var string|callable
         */
        private $definition;

        /**
         * @var mixed
         */
        private $instance = null;

        /**
         * @var boolean
         */
        private $shared;

        /**
         * Service constructor
         *
         * @param string $name service name
         * @param string $definition service definition
         * @param boolean|true $shared whether the service is shared
         */
        public function __construct($name, $definition, $shared = true)
        {
            $this->name = $name;
            $this->definition = $definition;
            $this->shared = $shared;
        }

        public function getName()
        {
            return $this->name;
        }

        /**
         * Get the instance of the service
         *
         * @return mixed
         */
        public function get()
        {
            if ($this->shared && $this->instance != null) {
                return $this->instance;
            }
            $instance = $this->getServiceInstance();
            if ($this->shared) {
                $this->instance = $instance;
            }
            return $instance;
        }

        /**
         * Return the definition of the service
         *
         * @return callable|string
         */
        public function getDefinition()
        {
            return $this->definition;
        }

        /**
         * @return boolean
         */
        public function isShared()
        {
            return $this->shared;
        }

        /**
         * @param boolean|true $shared
         */
        public function setShared($shared = true)
        {
            $this->shared = $shared;
        }

        /**
         * Get an instance of the service
         *
         * @return mixed
         * @throws \RuntimeException
         */
        protected function getServiceInstance()
        {
            if (is_object($this->definition) && !($this->definition instanceof \Closure)) {
                // definition is an object
                if ($this->shared || $this->instance == null) {
                    return $this->definition;
                } else {
                    // when returning a non-shared service again we need to clone it
                    return clone $this->definition;
                }
            } elseif (is_string($this->definition) && class_exists($this->definition)) {
                // definition is a class
                $class = $this->definition;
                return new $class();
            } elseif (is_callable($this->definition)) {
                // definition is a callable
                return call_user_func($this->definition);
            }
            throw new \RuntimeException("Cannot get instance using definition " . var_export($this->definition, true));
        }

    }

}
