<?php

namespace Fw\Http\Router {


    class Route
    {
        /**
         * @var string
         */
        private $pattern;

        /**
         * @var callable
         */
        private $handler;

        /**
         * @var string[]
         */
        private $methods;

        /**
         * @var string
         */
        private $compiledPattern;

        /**
         * Route constructor
         *
         * @param string $pattern request pattern this route can handle
         * @param callable $handler route handler
         */
        public function __construct($pattern, $handler)
        {
            $this->pattern = $pattern;
            $this->handler = $handler;
            $this->methods = self::getPatternMethods($pattern);
            //echo "METHODS: " . var_export($this->methods, true) . "\n";
            $this->compiledPattern = self::compilePattern($pattern);
            //echo "COMPILED: " . var_export($this->compiledPattern, true) . "\n";
        }

        /**
         * Return URI matches if a request matches this route, false otherwise
         *
         * @param \Fw\Http\RequestInterface $request
         * @return boolean
         */
        public function matches($request) {
            if (in_array($request->getMethod(), $this->methods)
                && preg_match("#" . $this->compiledPattern . "#", $request->getURI(), $matches)) {
                return $matches;
            }
            return false;
        }

        /**
         * Handle a request if possible
         *
         * @param \Fw\Http\RequestInterface $request
         * @return boolean returns the return value of the handler, false if request cannot be handled
         */
        public function handle($request) {
            $matches = $this->matches($request);
            if ($matches !== false) {
                return call_user_func($this->handler, $matches);
            }
            return false;
        }

        /**
         * Get HTTP methods defined in a pattern and strip them from the pattern
         *
         * @param string $pattern route pattern; the method definitions will be stripped by this method
         * @return string[] array of methods specified by the pattern
         */
        protected static function getPatternMethods(&$pattern)
        {
            $methods = array();
            if (preg_match('/^((?:(?:(?:GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS)\|)*(?:GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS))|\*)\s+/', $pattern, $m)) {
                if ($m[1] == "*") {
                    $m[1] = "GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS";
                }
                $methods = array_unique(explode('|', $m[1]));
                // strip the method specification from the pattern
                $pattern = substr($pattern, strlen($m[0]));
            }
            return $methods;
        }

        /**
         * Compile a URI pattern (without method) into a regular expression
         *
         * @param string $pattern
         * @return string regular expression representing the pattern
         */
        protected static function compilePattern($pattern)
        {
            $stripSlash = false;
            if (substr($pattern, -1) != '/') {
                // add a trailing slash temporarily
                $pattern .= '/';
                $stripSlash = true;
            }
            $compiledPattern = '^' . preg_replace('#/@([a-zA-Z0-9_]+)#', '/(?P<$1>[^/]+)', $pattern);
            if ($stripSlash) {
                $compiledPattern = rtrim($compiledPattern, "/");
            }
            $compiledPattern .= '$';

            return $compiledPattern;
        }

    }

}
