<?php

namespace Cm\Download\Api\BuildList {


    use Cm\Download\Api;
    use Cm\Download\Api\Build;
    use Symfony\Component\Yaml\Yaml;

    /**
     * FileBuildList class allows loading of build data from various types of text files
     *
     * @package Cm\Download\Api\BuildList
     */
    class FileBuildList extends AbstractBuildList
    {
        /**
         * File type constants
         */
        const TYPE_JSON = 1;
        const TYPE_YAML = 2;
        const TYPE_CSV = 3;
        const TYPE_XML = 4;

        /**
         * @var array
         */
        public static $FILE_TYPES = [
            'json' => self::TYPE_JSON,
            'yaml' => self::TYPE_YAML,
            'csv'  => self::TYPE_CSV,
            'xml'  => self::TYPE_XML,
        ];

        /**
         * @var string
         */
        protected $file;

        /**
         * @var int
         */
        protected $type;

        /**
         * FolderBuildList constructor
         *
         * @param string $file
         * @param string|null $type force file type
         */
        public function __construct($file, $type = null)
        {
            $this->builds = null;
            $this->file = $file;
            $this->type = $type;
        }

        /**
         * Return file type detected from the file extension
         *
         * @param $file string file name
         * @return bool|mixed file type code or false if it cannot be detected
         */
        protected static function getFileType($file)
        {
            $extensionWithDot = strrchr($file, '.');
            if ($extensionWithDot === false) {
                return false;
            }
            $extension = substr($extensionWithDot, 1);
            if (!isset(self::$FILE_TYPES[$extension])) {
                return false;
            }

            return self::$FILE_TYPES[$extension];
        }

        /**
         * Load build data from a JSON file
         *
         * The JSON file should have the following structure:
         *
         * {
         *   "shamu": [
         *     {
         *       "url": "http://...",
         *       "filename": "cm-13.0-20160925-UNOFFICIAL-shamu.zip",
         *       "timestamp": 1474932005,
         *       "md5sum": "96b3004abbaab6b3c90cde34a85be4b1",
         *       "changes": "http://...",
         *       "channel": "nightly",
         *       "api_level": 23
         *     },
         *     {
         *       "url": ...
         *     }
         *   ]
         * }
         *
         * @param $file string
         * @return array[] build data
         */
        protected static function loadDataFromJson($file)
        {
            return json_decode(file_get_contents($file), true);
        }

        /**
         * Load build data from a YAML file
         *
         * The YAML file should have the following structure:
         *
         * shamu:
         * - url: "http://...",
         *   filename: "cm-13.0-20160925-UNOFFICIAL-shamu.zip"
         *   timestamp: 1474932005
         *   md5sum: "96b3004abbaab6b3c90cde34a85be4b1"
         *   changes: "http://..."
         *   channel: "nightly"
         *   api_level: 23
         * - url: ...
         *
         * @param $file string
         * @return array[] build data
         */
        protected static function loadDataFromYaml($file)
        {
            return Yaml::parse(file_get_contents($file));
        }

        /**
         * Load build data from a CSV file
         * The fields in the CSV file should be in the following order:
         * device, url, filename, timestamp, md5sum, changes, channel, api_level
         *
         * @param $file string
         * @return array[] build data
         */
        protected static function loadDataFromCsv($file)
        {
            $data = [];
            $fh = fopen($file, 'r');
            while ($row = fgetcsv($fh)) {
                $device = $row[0];
                $buildData = [
                    'url'         => $row[1],
                    'filename'    => $row[2],
                    'timestamp'   => (int)$row[3],
                    'md5sum'      => $row[4],
                    // incremental updates not implemented - using a random incremental hash
                    'incremental' => substr(md5($row[4]), 0, 10),
                    'changes'     => $row[6],
                    'channel'     => $row[7],
                    'api_level'   => (int)$row[8],
                ];
                if (!isset($data[$device])) {
                    $data[$device] = [];
                }
                $data[$device][] = $buildData;
            }

            return $data;
        }

        /**
         * Load build data from a XML file
         * The XML file should have the following structure:
         *
         * <builds>
         *   <device name="shamu">
         *     <build
         *       url="http://..."
         *       filename="cm-13.0-20160925-UNOFFICIAL-shamu.zip"
         *       timestamp="1474932005"
         *       md5sum="96b3004abbaab6b3c90cde34a85be4b1"
         *       changes="http://..."
         *       channel="nightly"
         *       api_level="23" />
         *     <build
         *       ... />
         *   </device>
         * </builds>
         *
         * @param $file string
         * @return array[] build data
         */
        protected static function loadDataFromXml($file)
        {
            $data = [];
            try {
                $root = new \SimpleXMLElement(file_get_contents($file));
            } catch (\Exception $ex) {
                return [];
            }
            foreach ($root->device as $deviceEl) {
                $device = (string)$deviceEl['name'];
                $data[$device] = [];

                foreach ($deviceEl->build as $buildEl) {
                    $data[$device][] = [
                        'url'         => (string)$buildEl['url'],
                        'filename'    => (string)$buildEl['filename'],
                        'timestamp'   => (int)$buildEl['timestamp'],
                        'md5sum'      => (string)$buildEl['md5sum'],
                        // incremental updates not implemented - using a random incremental hash
                        'incremental' => substr(md5((string)$buildEl['md5sum']), 0, 10),
                        'changes'     => (string)$buildEl['changes'],
                        'channel'     => (string)$buildEl['channel'],
                        'api_level'   => (int)$buildEl['api_level'],
                    ];
                }
            }

            return $data;
        }

        /**
         * Load builds from file
         *
         * @throws \InvalidArgumentException
         */
        protected function loadBuilds()
        {
            $this->builds = [];
            // if the file type hasn't been specified try to detect it from the file extension
            if (!$this->type) {
                $this->type = self::getFileType($this->file);
            }

            // load the build data
            $data = [];
            switch ($this->type) {
                case self::TYPE_JSON:
                    $data = self::loadDataFromJson($this->file);
                    break;

                case self::TYPE_YAML:
                    $data = self::loadDataFromYaml($this->file);
                    break;

                case self::TYPE_CSV:
                    $data = self::loadDataFromCsv($this->file);
                    break;

                case self::TYPE_XML:
                    $data = self::loadDataFromXml($this->file);
                    break;

                default:
                    throw new \InvalidArgumentException('Unsupported file type: ' . $this->type);
            }

            foreach ($data as $device => $deviceBuilds) {
                foreach ($deviceBuilds as $buildData) {
                    $this->builds[$device][] = Build::fromArray($buildData);
                }
            }
        }

        /**
         * Return array of available builds for a given device and channel
         *
         * @param string $device device codename
         * @param string $channel update channel (nightly/snapshot/stable)
         * @return Build[]
         */
        public function getBuilds($device, $channel = Api::CHANNEL_NIGHTLY)
        {
            if (!is_array($this->builds)) {
                $this->loadBuilds();
            }
            if (!isset($this->builds[$device])) {
                return [];
            }

            return $this->builds[$device];
        }

    }

}

