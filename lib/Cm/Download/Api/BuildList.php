<?php

namespace Cm\Download\Api {


    use Cm\Download\Api;

    abstract class BuildList implements BuildListInterface, \Iterator, \ArrayAccess, \Countable
    {
        /**
         * Array of arrays of builds by device
         * @var Build[][]
         */
        protected $builds = [];

        /**
         * Return the list of available builds for a given device and channel
         *
         * @param string $device device codename
         * @param string $channel update channel (nightly/snapshot/stable)
         * @return \Cm\Download\Api\Build[]
         */
        abstract public function getBuilds($device, $channel = Api::CHANNEL_NIGHTLY);

        /*
         * Iterator interface methods
         */
        public function current()
        {
            return current($this->builds);
        }

        public function key()
        {
            return key($this->builds);
        }

        public function next()
        {
            next($this->builds);
        }

        public function rewind()
        {
            reset($this->builds);
        }

        public function valid()
        {
            return $this->key() !== null;
        }

        /*
         * ArrayAccess interface methods
         */
        public function offsetExists($offset)
        {
            return isset($this->builds[$offset]);
        }

        public function offsetGet($offset)
        {
            return $this->builds[$offset];
        }

        public function offsetSet($offset, $value)
        {
            $this->builds[$offset] = $value;
        }

        public function offsetUnset($offset)
        {
            unset($this->builds[$offset]);
        }

        /*
         * Countable interface methods
         */
        public function count()
        {
            return count($this->builds);
        }
    }
}
