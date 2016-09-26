<?php

namespace Fw\Http {


    use Fw\Http\Router\Route;

    class Router implements RouterInterface
    {
        /**
         * @var Route[]
         */
        private $routes = array();

        /**
         * Route constructor
         */
        public function __create()
        {

        }

        /**
         * Add new route
         *
         * @param string $pattern
         * @param callable $handler
         */
        public function add($pattern, $handler)
        {
            $this->routes[] = new Route($pattern, $handler);
        }

        /**
         * Return true if a request can be routed, false otherwise
         *
         * @param \Fw\Http\Request $request
         * @return boolean
         */
        public function isRoutable($request) {
            foreach ($this->routes as $route) {
                if ($route->matches($request) !== false) {
                    return true;
                }
            }
            return false;
        }

        /**
         * Route a request
         *
         * @param \Fw\Http\Request $request
         * @return boolean
         */
        public function route($request)
        {
            foreach ($this->routes as $route) {
                if ($route->handle($request) !== false) {
                    return true;
                }
            }
            return false;
        }

    }

}
