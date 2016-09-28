<?php

namespace Tests\Cases\Api {


    use Cm\Download\Api\Build;
    use Cm\Download\Api\BuildList\File;
    use Tests\Fixtures\FileBuildListFixture;

    class FileBuildListTest extends \PHPUnit_Framework_TestCase
    {

        /**
         * @var FileBuildListFixture
         */
        protected static $fixture;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            self::$fixture = new FileBuildListFixture();
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

            $fixtureBuilds = [];
            foreach (self::$fixture->getDevices() as $device) {
                $fixtureBuilds[$device] = self::$fixture->getBuilds($device);
                usort($fixtureBuilds[$device], $compareBuildsByMd5sum);
            }
            // test each file type
            foreach (self::$fixture->getFiles() as $fileName) {
                $fileBuildList = new File($fileName);
                foreach (self::$fixture->getDevices() as $device) {
                    $foundBuilds[$fileName][$device] = $fileBuildList->getBuilds($device);
                    usort($foundBuilds[$fileName][$device], $compareBuildsByMd5sum);

                    // the two arrays should contain the same objects (regardless of order)
                    $this->assertEquals(
                        $fixtureBuilds[$device],
                        $foundBuilds[$fileName][$device],
                        'Build lists for file ' . $fileName . ', device ' . $device . ' differ!'
                    );
                }

                // test that no builds are found and an empty array is correctly returned
                $foundBuildsUnknown = $fileBuildList->getBuilds('wedonthavethisdevice');
                $this->assertEquals([], $foundBuildsUnknown);
            }
        }

    }

}
