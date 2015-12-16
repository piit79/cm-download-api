<?php

namespace Fw\Http {


    interface ResponseInterface
    {

        /**
         * Set the main response fields
         *
         * @param integer $httpCode HTTP response code
         * @param string $contentType
         * @param array|string $data
         * @return Response
         */
        public function setup($httpCode, $contentType, $data);

        /**
         * @return int
         */
        public function getHttpCode();

        /**
         * @param int $httpCode
         * @return Response updated object
         */
        public function setHttpCode($httpCode);

        /**
         * @return string
         */
        public function getContentType();

        /**
         * @param string $contentType
         * @return Response updated object
         */
        public function setContentType($contentType);

        /**
         * Add a HTTP header
         * Supports adding multiple values for a header
         *
         * @param string $name header name
         * @param string|string[] $value header value
         * @param boolean $multiValues if false, overwrites current header with the same name; otherwise adds another value
         * @return Response $this Updated object
         */
        public function addHeader($name, $value, $multiValues = false);

        /**
         * @param string $name header name
         * @return string|string[]|null header value(s) or null if not exists
         */
        public function getHeader($name);

        /**
         * @return array|string
         */
        public function getData();

        /**
         * @param array|string $data
         * @return Response updated object
         */
        public function setData($data);

        /**
         * Send the HTTP response
         */
        public function send();

    }

}
