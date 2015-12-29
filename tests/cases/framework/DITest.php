<?php

namespace {


    class Test1
    {
    }

    class Test2 extends Test1
    {
    }

    class Test3
    {
    }

    function createService3()
    {
        return new \Test3();
    }

    class Test4
    {
    }

    class Test5
    {
    }

    class Test5Creator
    {
        public function get()
        {
            return new Test5();
        }
    }

    class Test6
    {
    }

    class Test6CreatorStatic
    {
        public static function getService()
        {
            return new Test6();
        }
    }

}


namespace Tests\Cases\Framework {


    use Fw\DI;

    class DITest extends \PHPUnit_Framework_Testcase
    {

        /**
         * @var DI
         */
        protected static $di;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            self::$di = DI::getInstance();
        }

        /**
         * Test all possible service definition types
         *
         * @param string $serviceName
         * @param mixed $definition service definition
         * @param string $className service class name
         * @dataProvider serviceDefinitionsTestProvider
         */
        public function testServiceDefinitions($serviceName, $definition, $className)
        {
            self::$di->set($serviceName, $definition);
            $svc = self::$di->get($serviceName);
            $this->assertTrue(is_a($svc, $className));
        }

        public function serviceDefinitionsTestProvider()
        {
            return array(
                array('svc1', new \Test1, '\Test1'),
                array('svc2', '\Test2', '\Test2'),
                array('svc3', 'createService3', '\Test3'),
                array('svc4', function() { return new \Test4(); }, '\Test4'),
                array('svc5', array(new \Test5Creator(), 'get'), '\Test5'),
                array('svc6', array('\Test6CreatorStatic', 'getService'), '\Test6'),
            );
        }

        /**
         * Test shared and non-shared services
         *
         * @param string $serviceName
         * @dataProvider sharedNonSharedTestProvider
         */
        public function testSharedNonShared($serviceName)
        {
            // test that two instances of a shared service are identical
            self::$di->getService($serviceName)->setShared(true);
            $s1a = self::$di->get($serviceName);
            $s1b = self::$di->get($serviceName);
            $this->assertTrue($s1a === $s1b);

            // test that two instances of a non-shared service are different
            self::$di->getService($serviceName)->setShared(false);
            $s2a = self::$di->get($serviceName);
            $s2b = self::$di->get($serviceName);
            $this->assertTrue($s2a !== $s2b);
        }

        public function sharedNonSharedTestProvider()
        {
            $serviceDefinitions = $this->serviceDefinitionsTestProvider();
            $data = array();
            foreach ($serviceDefinitions as $svc) {
                $data[] = array($svc[0]);
            }
            return $data;
        }

    }

}
