<?php

namespace Tests\Cases\Framework {


    use Fw\Http\Router\Route;
    use Tests\Mocks\RequestMock;

    class RouteTest extends \PHPUnit_Framework_Testcase
    {

        /**
         * Test if routes match requests
         *
         * @param string $pattern route pattern
         * @param string $method request method
         * @param string $uri request uri
         * @param array|false $expectedMatches expected matched URI parameters or false if route shouldn't match
         * @dataProvider routesTestProvider
         */
        public function testRoutes($pattern, $method, $uri, $expectedMatches)
        {
            $r = new Route($pattern, null);
            $req = new RequestMock($method, $uri);
            $matches = $r->matches($req);
            //var_export($matches);
            if ($expectedMatches === false) {
                $this->assertFalse($matches);
            } else {
                foreach ($expectedMatches as $par => $val) {
                    $this->assertTrue(isset($matches[$par]));
                    if (isset($matches[$par])) {
                        $this->assertEquals($val, $matches[$par]);
                    }
                }
            }
        }

        public function routesTestProvider()
        {
            return array(
                array("GET /api/v1", "GET", "/api/v1", array(
                    0 => '/api/v1',
                )),
                array("GET /api/v1", "POST", "/api/v1", false),
                array("GET /api/v1", "GET", "/api/v1.1", false),
                array("GET|POST /api/v1", "POST", "/api/v1", array(
                    0 => '/api/v1',
                )),
                array("GET /item/@id", "GET", "/item/5", array(
                    0 => '/item/5',
                    1 => '5',
                    'id' => '5',
                )),
                array("GET /item/@id", "GET", "/item/5/edit", false),
                array("POST /item/@id/@action", "POST", "/item/7/edit", array(
                    0 => '/item/7/edit',
                    1 => '7',
                    2 => 'edit',
                    'id' => '7',
                    'action' => 'edit',
                )),
            );
        }

    }

}
