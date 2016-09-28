<?php

namespace Tests\Cases\Api {


    use Cm\Download\Api\Build;
    use Cm\Download\Api\BuildList\Folder;
    use Tests\Fixtures\FolderBuildListFixture;

    class FolderBuildListTest extends \PHPUnit_Framework_TestCase
    {

        /**
         * @var FolderBuildListFixture
         */
        protected static $fixture;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            self::$fixture = new FolderBuildListFixture();
            self::$fixture->setup();
        }

        public static function tearDownAfterClass()
        {
            parent::tearDownAfterClass();
            self::$fixture->cleanup();
        }

        public function testExistingDeviceBuilds()
        {
            // compare builds by their MD5 hash
            $compareBuildsByMd5sum = function(Build $a, Build $b) {
                return strcmp($a->getMd5sum(), $b->getMd5sum());
            };

            $root = self::$fixture->getRoot();
            $urlBase = self::$fixture->getUrlBase();
            $folderBuildList = new Folder($root, $urlBase);

            foreach (self::$fixture->getDevices() as $device) {
                $foundBuilds = $folderBuildList->getBuilds($device);
                $fixtureBuilds = self::$fixture->getBuilds($device);

                // the two arrays should contain the same objects (regardless of order)
                usort($fixtureBuilds, $compareBuildsByMd5sum);
                usort($foundBuilds, $compareBuildsByMd5sum);
                $this->assertEquals($fixtureBuilds, $foundBuilds,
                    'Build lists for device ' . $device . ' differ!');
            }

            // test that no builds are found and an empty array is correctly returned
            $foundBuildsUnknown = $folderBuildList->getBuilds('wedonthavethisdevice');
            $this->assertEquals([], $foundBuildsUnknown);
        }

    }

}
