<?php

namespace Cm\Download\Api\BuildList {


    use Cm\Download\Api;
    use Cm\Download\Api\Build;

    /**
     * Class AbstractBuildList
     * @package Cm\Download\Api\BuildList
     */
    abstract class AbstractBuildList implements BuildListInterface, \Iterator, \ArrayAccess, \Countable
    {
        /**
         * @var string
         */
        protected $baseUrl;

        /**
         * Device codename
         * @var string
         */
        protected $device;

        /**
         * @var Build[][]
         */
        protected $builds = [];

        /**
         * Get the download server base URL
         *
         * @return string
         */
        public function getBaseUrl()
        {
            return $this->baseUrl;
        }

        /**
         * Set the download server base URL
         *
         * @param $baseUrl
         */
        public function setBaseUrl($baseUrl)
        {
            $this->baseUrl = $baseUrl;
        }

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
