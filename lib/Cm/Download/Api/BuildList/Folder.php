<?php

namespace Cm\Download\Api\BuildList {


    use Cm\Download\Api;
    use Cm\Download\Api\Build;
    use Cm\Download\Api\BuildList;
    use Fw\Util;

    /**
     * Folder class allows loading of build data from a directory structure containing the actual builds
     *
     * The directory structure is expected to look like this:
     *
     * <root>/
     *   shamu/
     *     cm-13.0-20160715-UNOFFICIAL-shamu.zip
     *     cm-13.0-20160715-UNOFFICIAL-shamu.zip.md5sum
     *     cm-13.0-20160715-UNOFFICIAL-shamu.changes
     *     ...
     *   grouper/
     *     ...
     *
     * @package Cm\Download\Api\BuildList
     */
    class Folder extends BuildList
    {
        const BUILD_PREFIX = 'cm-';
        const BUILD_PROP_PATH = 'system/build.prop';

        /**
         * @var string
         */
        private $root;

        /**
         * Web server base URL that corresponds to $this->root
         * @var string
         */
        protected $baseUrl;

        /**
         * FolderBuildList constructor
         *
         * @param string $root
         * @param string $baseUrl
         */
        public function __construct($root, $baseUrl = null)
        {
            $this->root = $root;
            $this->baseUrl = $baseUrl;
        }

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
         * @param string $baseUrl
         */
        public function setBaseUrl($baseUrl)
        {
            $this->baseUrl = $baseUrl;
        }

        /**
         * Return MD5 sum of a build
         *
         * @param string $buildPath
         * @return string|boolean MD5 sum of the build or false on failure
         */
        protected static function getMd5Sum($buildPath)
        {
            $md5sum_file = $buildPath . '.md5sum';
            if (is_file($md5sum_file)) {
                $md5sum_contents = file_get_contents($md5sum_file);
                list($md5sum_line) = explode("\n", $md5sum_contents);
                if (preg_match('/^([0-9a-f]{32}) /', $md5sum_line, $m)) {
                    return $m[1];
                }
            }
            return false;
        }

        /**
         * Return the Android API level of theb build
         *
         * The API level is obtained from a build.prop file inside the build zip file.
         *
         * @param string $buildPath
         * @return int Android API level of the build
         */
        protected static function getApiLevel($buildPath)
        {
            try {
                $zip = new \ZipArchive();
                $zip->open($buildPath);
                $zip->extractTo(sys_get_temp_dir(), self::BUILD_PROP_PATH);
                $zip->close();
                $buildPropPathExtracted = sys_get_temp_dir() . DIRECTORY_SEPARATOR . self::BUILD_PROP_PATH;
                $buildProp = file_get_contents($buildPropPathExtracted);
                unlink($buildPropPathExtracted);
                rmdir(dirname($buildPropPathExtracted));
                $m = [];
                if (preg_match('/ro\.build\.version\.sdk=(\d+)/s', $buildProp, $m)) {
                    return (int)$m[1];
                }
            } catch (\Exception $e) {
                error_log('getApiLevel: Caught exception: ' . $e->getTraceAsString());
            }
            return 0;
        }

        /**
         * Return the filename of the changes file
         *
         * @param string $buildFilename
         * @return string filename of the changes file
         */
        protected static function getChangesFilename($buildFilename)
        {
            return preg_replace('/\.zip$/', '.changes', $buildFilename);
        }

        /**
         * Return the absolute path corresponding to the relative one
         *
         * @param string $relativePath
         * @return string absolute path
         */
        protected function getAbsPath($relativePath)
        {
            return  $this->root . DIRECTORY_SEPARATOR . $relativePath;
        }

        /**
         * Return the URL corresponding to the relative path
         *
         * @param string $relativePath
         * @return string absolute URL
         */
        protected function getUrl($relativePath)
        {
            return  $this->baseUrl . '/' . $relativePath;
        }

        /**
         * Find builds for a device
         *
         * @param string $device device codename
         * @param string $channel update channel (nightly/snapshot/stable)
         */
        protected function findBuilds($device, $channel = Api::CHANNEL_NIGHTLY)
        {
            $deviceDir = $this->root . DIRECTORY_SEPARATOR . $device;
            if (is_dir($deviceDir)) {
                $files = scandir($deviceDir);
            } else {
                $files = [];
            }
            $this->builds[$device] = [];
            foreach ($files as $filename) {
                $filePathRel = $device . DIRECTORY_SEPARATOR . $filename;
                $filePathFull = $this->getAbsPath($filePathRel);
                // only process ZIP files with the CM build prefix
                if (is_file($filePathFull)
                    && substr($filename, 0, strlen(self::BUILD_PREFIX)) == self::BUILD_PREFIX
                    && substr($filename, -4) == '.zip'
                ) {
                    $buildUrl = $this->getUrl($filePathRel);
                    $timestamp = Util::getTimeStamp($filePathFull);
                    $md5sum = self::getMd5Sum($filePathFull);
                    $apiLevel = self::getApiLevel($filePathFull);
                    // incremental updates not implemented - using a random incremental hash
                    $incremental = substr(md5($md5sum), 0, 10);
                    $changesUrl = $this->getUrl($device . DIRECTORY_SEPARATOR . self::getChangesFilename($filename));
                    $build = new Build(
                        $buildUrl,
                        $filename,
                        $timestamp,
                        $md5sum,
                        $incremental,
                        $changesUrl,
                        $channel,
                        $apiLevel
                    );
                    $this->builds[$device][] = $build;
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
            if (!isset($this->builds[$device])) {
                $this->findBuilds($device, $channel);
            }
            return $this->builds[$device];
        }

    }

}
