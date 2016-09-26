<?php

namespace Fw\Http {


    interface RouterInterface
    {

        /**
         * Add new route
         *
         * @param $pattern
         * @param $handler
         */
        public function add($pattern, $handler);

        /**
         * Return true if a request can be routed, false otherwise
         *
         * @param \Fw\Http\Request $request
         * @return boolean
         */
        public function isRoutable($request);

        /**
         * Route a request
         *
         * @param \Fw\Http\Request $request
         * @return boolean
         */
        public function route($request);

    }

}
