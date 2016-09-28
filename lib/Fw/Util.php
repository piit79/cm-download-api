<?php

namespace Fw {


    class Util
    {
        /**
         * Return the file/directory modification time
         *
         * @param string $path full path to file/directory
         * @return int|false unix timestamp of file modification time or false if failed
         */
        public static function getTimeStamp($path)
        {
            if (is_file($path) || is_dir($path)) {
                $stat = stat($path);
                return $stat[9];
            }

            return false;
        }

        /**
         * Return true if path is absolute
         *
         * @param string $path
         * @return bool true if path is absolute
         */
        public static function isAbsolutePath($path)
        {
            return substr($path, 0, 1) == DIRECTORY_SEPARATOR;
        }

        /**
         * Recursively remove a directory tree
         *
         * @param string $path
         * @throws \InvalidArgumentException
         */
        public static function rm_rf($path)
        {
            if (realpath($path) == "/") {
                throw new \InvalidArgumentException("I refuse to be stupid and rm -rf /!");
            }
            $dh = dir($path);
            while ($entry = $dh->read()) {
                // skip the special directory entries
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                $entryPath = $path . DIRECTORY_SEPARATOR . $entry;
                if (is_dir($entryPath)) {
                    // recursively delete directories
                    self::rm_rf($path . DIRECTORY_SEPARATOR . $entry);
                } else {
                    // unlink files
                    unlink($entryPath);
                }
            }
            // and finally delete the root directory
            rmdir($path);
        }

        /**
         * Create a unique temporary directory in a given directory or in system temporary directory
         *
         * @param string $tempDir
         * @return string|false
         */
        public static function tempdir($tempDir = null)
        {
            if ($tempDir == null) {
                $tempDir = sys_get_temp_dir();
            }
            $tempfile = tempnam($tempDir, "");
            if (file_exists($tempfile)) {
                unlink($tempfile);
            }
            mkdir($tempfile);
            if (is_dir($tempfile)) {
                return $tempfile;
            }

            return false;
        }

        /**
         * Get random string of a specified length
         *
         * @param int $length
         * @return string
         */
        public static function getRandomString($length)
        {
            $str = '';
            for ($i = 0; $i < $length; $i++) {
                $str .= chr(mt_rand(33, 126));
            }

            return $str;
        }

    }

}
