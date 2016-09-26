<?php

namespace Cm\Download\Api {


    class Build
    {
        /**
         * @var string
         */
        private $url;

        /**
         * @var string
         */
        private $filename;

        /**
         * @var int
         */
        private $timestamp;

        /**
         * @var string
         */
        private $md5sum;

        /**
         * @var string
         */
        private $incremental;

        /**
         * @var string
         */
        private $changes;

        /**
         * @var string
         */
        private $channel;

        /**
         * @var string
         */
        private $apiLevel;

        /**
         * Build constructor
         * @param string $url
         * @param string $filename
         * @param int $timestamp
         * @param string $md5sum
         * @param string $incremental
         * @param string $changes
         * @param string $channel
         * @param int $api_level
         */
        public function __construct($url = "", $filename = "", $timestamp = null, $md5sum = "", $incremental = "",
                                    $changes = "", $channel = "", $api_level = null)
        {
            $this->url = $url;
            $this->filename = $filename;
            $this->timestamp = $timestamp;
            $this->md5sum = $md5sum;
            $this->incremental = $incremental;
            $this->changes = $changes;
            $this->channel = $channel;
            $this->apiLevel = $api_level;
        }

        /**
         * @return string
         */
        public function getUrl()
        {
            return $this->url;
        }

        /**
         * @param string $url
         * @return Build updated object
         */
        public function setUrl($url)
        {
            $this->url = $url;
        }

        /**
         * @return string
         */
        public function getFilename()
        {
            return $this->filename;
        }

        /**
         * @param string $filename
         * @return Build updated object
         */
        public function setFilename($filename)
        {
            $this->filename = $filename;
        }

        /**
         * @return int
         */
        public function getTimestamp()
        {
            return $this->timestamp;
        }

        /**
         * @param int $timestamp
         * @return Build updated object
         */
        public function setTimestamp($timestamp)
        {
            $this->timestamp = $timestamp;
        }

        /**
         * @return string
         */
        public function getMd5sum()
        {
            return $this->md5sum;
        }

        /**
         * @param string $md5sum
         * @return Build updated object
         */
        public function setMd5sum($md5sum)
        {
            $this->md5sum = $md5sum;
        }

        /**
         * @return string
         */
        public function getIncremental()
        {
            return $this->incremental;
        }

        /**
         * @param string $incremental
         * @return Build updated object
         */
        public function setIncremental($incremental)
        {
            $this->incremental = $incremental;
        }

        /**
         * @return string
         */
        public function getChanges()
        {
            return $this->changes;
        }

        /**
         * @param string $changes
         * @return Build updated object
         */
        public function setChanges($changes)
        {
            $this->changes = $changes;
        }

        /**
         * @return string
         */
        public function getChannel()
        {
            return $this->channel;
        }

        /**
         * @param string $channel
         * @return Build updated object
         */
        public function setChannel($channel)
        {
            $this->channel = $channel;
        }

        /**
         * @return string
         */
        public function getApiLevel()
        {
            return $this->apiLevel;
        }

        /**
         * @param string $apiLevel
         * @return Build updated object
         */
        public function setApiLevel($apiLevel)
        {
            $this->apiLevel = $apiLevel;
        }

        /**
         * Return the build info as array
         * @return array
         */
        public function toArray()
        {
            return array(
                'url' => $this->url,
                'filename' => $this->filename,
                'timestamp' => $this->timestamp,
                'md5sum' => $this->md5sum,
                'incremental' => $this->incremental,
                'changes' => $this->changes,
                'channel' => $this->channel,
                'api_level' => $this->apiLevel,
            );
        }

        /**
         * Factory function to create a Build object from an associative array
         *
         * @param $array array
         * @return Build
         */
        public static function fromArray(array $array)
        {
            return new self(
                $array['url'],
                $array['filename'],
                $array['timestamp'],
                $array['md5sum'],
                $array['incremental'],
                $array['changes'],
                $array['channel'],
                $array['api_level']
            );
        }

    }
}
