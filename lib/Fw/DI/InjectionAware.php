<?php

namespace Fw\DI {


    use Fw\DI;

    /**
     * Class User
     *
     * This class is the ancestor for all classes wanting to use dependency injection
     * @package Fw\DI
     */
    abstract class InjectionAware
    {

        /**
         * @var \Fw\DI
         */
        protected $di;

        /**
         * User constructor
         */
        public function __construct()
        {
            $this->di = DI::getInstance();
        }

    }

}
