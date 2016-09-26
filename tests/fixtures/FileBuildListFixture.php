<?php

namespace Tests\Fixtures {


    use Cm\Download\Api;
    use Cm\Download\Api\Build;
    use Cm\Download\Api\BuildList\BuildListInterface;
    use Cm\Download\Api\BuildList\FileBuildList;
    use Fw\Util;
    use Symfony\Component\Yaml\Yaml;

    class FileBuildListFixture extends \PHPUnit_Framework_Assert implements BuildListInterface
    {

        const BUILD_FILENAME_TEMPLATE = "cm-%s-%s-UNOFFICIAL-suffix-%s.zip";
        const DEVICE1 = 'shamu';
        const DEVICE2 = 'titan';

        const BUILD_BASE_NAME = 'builds';

        /**
         * @var string
         */
        protected $dir;

        /**
         * List of data files
         * @var string[]
         */
        protected $files = [];

        /**
         * @var string
         */
        protected $urlBase;

        /**
         * @var Build[][]
         */
        protected $builds;

        /**
         * FolderBuildListFixture constructor
         * If no root directory is specified a temporary one will be created
         *
         * @param string $dir directory to put
         * @param string $urlBase base URL of the build storage
         */
        public function __construct($dir = null, $urlBase = "http://example.com/get")
        {
            if ($dir == null) {
                $dir = Util::tempdir();
            }
            $this->dir = $dir;
            $this->urlBase = $urlBase;
        }

        /**
         * Set up the fixture
         */
        public function setup()
        {
            $this->createBuild("13.0", self::DEVICE1, '20160102');
            $this->createBuild("13.0", self::DEVICE1, '20160105');
            $this->createBuild("13.0", self::DEVICE1, '20160106');
            $this->createBuild("13.0", self::DEVICE2, '20160102');
            $this->createBuild("13.0", self::DEVICE2, '20160103');

            $this->saveBuilds();
        }

        /**
         * Clean-up the fixture
         */
        public function cleanup()
        {
            Util::rm_rf($this->dir);
        }

        /**
         * @return string[]
         */
        public function getFiles()
        {
            return $this->files;
        }

        /**
         * Return the list of all devices we have builds for
         *
         * @return string[]
         */
        public function getDevices()
        {
            return array_keys($this->builds);
        }

        /**
         * Create a build instance
         *
         * @param string $cmVersion CyanogenMod version
         * @param string $device device codename
         * @param string $date build date in YYYYmmdd format
         * @param int $apiLevel build Android API level
         */
        protected function createBuild($cmVersion, $device, $date, $apiLevel = 23)
        {
            $buildFilename = sprintf(self::BUILD_FILENAME_TEMPLATE, $cmVersion, $date, $device);
            $buildUrl = $this->urlBase . '/' . $device . '/' . $buildFilename;
            $changesPath = str_replace(".zip", ".changes", $buildUrl);
            // create the build md5sum
            $md5Sum = md5(Util::getRandomString(32));
            // random timestamp on the build date
            $dateStamp = strtotime($date);
            $randomSeconds = mt_rand(0, 24*3600-1);
            $timestamp = $dateStamp + $randomSeconds;
            // random incremental
            $incremental = substr(md5($md5Sum), 0, 10);
            $channel = Api::CHANNEL_NIGHTLY;
            $build = new Build($buildUrl, $buildFilename, $timestamp, $md5Sum, $incremental, $changesPath, $channel,
                $apiLevel);
            $this->addBuild($device, $build);
        }

        /**
         * Add a build to the list of fixture builds
         *
         * @param string $device
         * @param Build $build
         */
        protected function addBuild($device, Build $build) {
            if (!isset($this->builds[$device])) {
                $this->builds[$device] = [];
            }
            $this->builds[$device][] = $build;
        }

        /**
         * Get the full file name for the given file type
         *
         * @param $type
         * @return string
         * @throws \InvalidArgumentException
         */
        protected function getFileName($type)
        {
            $extension = array_search($type, FileBuildList::$FILE_TYPES);
            if ($extension === false) {
                throw new \InvalidArgumentException('Extension for file type ' . $type . ' not found!');
            }

            return $this->dir . DIRECTORY_SEPARATOR . self::BUILD_BASE_NAME . '.' . $extension;
        }

        protected function getBuildData()
        {
            $data = [];
            foreach ($this->builds as $device => $deviceBuilds) {
                $data[$device] = [];
                foreach ($deviceBuilds as $build) {
                    $data[$device][] = $build->toArray();
                }
            }

            return $data;
        }

        /**
         * Save the builds to a file of specified type
         */
        protected function saveBuilds()
        {
            $this->saveBuildsJson();
            $this->saveBuildsYaml();
            $this->saveBuildsCsv();
            $this->saveBuildsXml();
        }

        /**
         * Save builds to a JSON file
         */
        protected function saveBuildsJson()
        {
            $fileName = $this->getFileName(FileBuildList::TYPE_JSON);
            $data = $this->getBuildData();
            file_put_contents($fileName, json_encode($data, JSON_PRETTY_PRINT));
            $this->files[] = $fileName;
        }

        /**
         * Save builds to a YAML file
         */
        protected function saveBuildsYaml()
        {
            $fileName = $this->getFileName(FileBuildList::TYPE_YAML);
            $data = $this->getBuildData();
            file_put_contents($fileName, Yaml::dump($data, 99));
            $this->files[] = $fileName;
        }

        /**
         * Save builds to a CSV file
         */
        protected function saveBuildsCsv()
        {
            $fileName = $this->getFileName(FileBuildList::TYPE_CSV);
            $data = $this->getBuildData();
            $fp = fopen($fileName, 'w');
            foreach ($data as $device => $deviceBuilds) {
                foreach ($deviceBuilds as $buildArray) {
                    array_unshift($buildArray, $device);
                    fputcsv($fp, $buildArray);
                }
            }
            fclose($fp);
            $this->files[] = $fileName;
        }

        /**
         * Save builds to an XML file
         */
        protected function saveBuildsXml()
        {
            $fileName = $this->getFileName(FileBuildList::TYPE_XML);
            $root = new \SimpleXMLElement('<builds></builds>');
            $data = $this->getBuildData();
            foreach ($data as $device => $deviceBuilds) {
                $deviceNode = $root->addChild('device');
                $deviceNode->addAttribute('name', $device);
                foreach ($deviceBuilds as $buildArray) {
                    $buildNode = $deviceNode->addChild('build');
                    foreach ($buildArray as $name => $value) {
                        $buildNode->addAttribute($name, $value);
                    }
                }
            }
            $root->asXML($fileName);
            $this->files[] = $fileName;
        }

        /**
         * Return the list of available builds for a given file type, device and channel
         *
         * @param string $device device codename
         * @param string $channel update channel (nightly/snapshot/stable)
         * @return Api\Build[]
         */
        public function getBuilds($device, $channel = Api::CHANNEL_NIGHTLY)
        {
            if (isset($this->builds[$device])) {
                return $this->builds[$device];
            }
            return [];
        }

    }

}
