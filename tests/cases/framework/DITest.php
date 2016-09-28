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

    class DITest extends \PHPUnit_Framework_TestCase
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
         * Test all possible service definition types
         *
         * @param string $serviceName
         * @param mixed $definition service definition
         * @param string $className service class name
         * @dataProvider serviceDefinitionsTestProvider
         */
        public function testServiceDefinitions($serviceName, $definition, $className)
        {
            // set service using the standard set() method
            $service1 = $serviceName . 'a';
            self::$di->set($service1, $definition, true);

            // set service using the magic set<ServiceName>() method
            $service2 = $serviceName . 'b';
            $method = 'set' . ucfirst($service2);
            self::$di->$method($definition);

            // set service using the attribute notation: $di->service = <definition>;
            $service3 = $serviceName . 'c';
            self::$di->$service3 = $definition;

            // get services using the standard get() method
            $service1a = self::$di->get($service1);
            $service2a = self::$di->get($service2);
            $service3a = self::$di->get($service3);
            $this->assertTrue(is_a($service1a, $className));
            $this->assertTrue(is_a($service2a, $className));
            $this->assertTrue(is_a($service3a, $className));

            // get services using the magic get<ServiceName>() method
            $method1 = 'get' . ucfirst($service1);
            $method2 = 'get' . ucfirst($service2);
            $method3 = 'get' . ucfirst($service3);
            $service1b = self::$di->$method1();
            $service2b = self::$di->$method2();
            $service3b = self::$di->$method3();
            $this->assertTrue(is_a($service1b, $className));
            $this->assertTrue(is_a($service2b, $className));
            $this->assertTrue(is_a($service3b, $className));
            // service is shared - the same instance should be returned
            $this->assertTrue($service1b === $service1a, $service1 . ': $service1b !== $service1a');

            // get services using the attribute notation: $di->service
            $service1c = self::$di->$service1;
            $service2c = self::$di->$service2;
            $service3c = self::$di->$service3;
            $this->assertTrue(is_a($service1c, $className));
            $this->assertTrue(is_a($service2c, $className));
            $this->assertTrue(is_a($service3c, $className));
            // service is shared - the same instance should be returned
            $this->assertTrue($service1c === $service1a, $service1 . ': $service1c !== $service1a');
        }

        /**
         * Test shared and non-shared services
         *
         * @param string $serviceName
         * @dataProvider serviceDefinitionsTestProvider
         */
        public function testSharedNonShared($serviceName, $definition, $className)
        {
            // services were set in testServiceDefinitions()
            $serviceName .= 'a';

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

    }

}
