<?php

namespace Tests\Fixtures {


    use Cm\Download\Api;
    use Cm\Download\Api\Build;
    use Cm\Download\Api\BuildList\BuildListInterface;
    use Cm\Download\Api\BuildList\FolderBuildList;
    use Fw\Util;

    class FolderBuildListFixture extends \PHPUnit_Framework_Assert implements BuildListInterface
    {

        const BUILD_FILENAME_TEMPLATE = "cm-%s-%s-UNOFFICIAL-suffix-%s.zip";
        const DEVICE1 = 'shamu';
        const DEVICE2 = 'titan';

        /**
         * @var string[]
         */
        protected static $devices = [self::DEVICE1, self::DEVICE2];

        /**
         * @var string
         */
        protected $root;

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
         * @param string $root filesystem root of the build storage
         * @param string $urlBase base URL of the build storage
         */
        public function __construct($root = null, $urlBase = "http://example.com/get")
        {
            if ($root == null) {
                $root = Util::tempdir();
            }
            $this->root = $root;
            $this->urlBase = $urlBase;
        }

        /**
         * @return \string[]
         */
        public function getDevices()
        {
            return self::$devices;
        }

        /**
         * @return string
         */
        public function getRoot()
        {
            return $this->root;
        }

        /**
         * @return string
         */
        public function getUrlBase()
        {
            return $this->urlBase;
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
        }

        /**
         * Clean-up the fixture
         */
        public function cleanup()
        {
            Util::rm_rf($this->root);
        }

        /**
         * Create a fake build file with md5sum
         *
         * @param string $cmVersion CyanogenMod version
         * @param string $device device codename
         * @param string $date build date in YYYYmmdd format
         * @param int $apiLevel build Android API level
         */
        protected function createBuild($cmVersion, $device, $date, $apiLevel = 23)
        {
            $buildDir = $this->root . DIRECTORY_SEPARATOR . $device;
            if (!is_dir($buildDir)) {
                mkdir($buildDir, 0755, true);
            }
            $buildFilename = sprintf(self::BUILD_FILENAME_TEMPLATE, $cmVersion, $date, $device);
            $buildPath = $buildDir . DIRECTORY_SEPARATOR . $buildFilename;
            $buildUrl = $this->urlBase . '/' . $device . '/' . $buildFilename;
            $md5SumPath = $buildPath . ".md5sum";
            $changesPath = str_replace(".zip", ".changes", $buildUrl);
            // create the build with just the system/build.prop file
            $zip = new \ZipArchive();
            $zip->open($buildPath, \ZipArchive::CREATE);
            $zip->addFromString(FolderBuildList::BUILD_PROP_PATH,
                  "# begin build properties\n"
                . "# this is a fake build.prop file\n"
                . "ro.build.version.sdk=" . $apiLevel . "\n");
            $zip->close();
            // create the build md5sum
            $md5Sum = md5(file_get_contents($buildPath));
            $md5String = $md5Sum . "  " . $buildFilename;
            file_put_contents($md5SumPath, $md5String);
            // random timestamp on the build date
            $dateStamp = strtotime($date);
            $randomSeconds = rand(0, 24*3600-1);
            $timestamp = $dateStamp + $randomSeconds;
            touch($buildPath, $timestamp);
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
                $this->builds[$device] = array();
            }
            $this->builds[$device][] = $build;
        }

        /**
         * Return the list of available builds for a given device and channel
         *
         * @param string $device device codename
         * @param string $channel update channel (nightly/snapshot/stable)
         * @return Build[]
         */
        public function getBuilds($device, $channel = Api::CHANNEL_NIGHTLY)
        {
            if (isset($this->builds[$device])) {
                return $this->builds[$device];
            }
            return array();
        }

    }

}
