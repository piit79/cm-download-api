<?php

namespace Fw\Http {


    use Fw\Http;

    class Response implements ResponseInterface
    {

        /**
         * HTTP response code
         * @var int
         */
        private $httpCode;

        /**
         * Response content type
         * @var string
         */
        private $contentType;

        /**
         * HTTP response headers
         * @var string[]
         */
        private $headers = array();

        /**
         * Response data - can be string or array
         * @var string|array
         */
        private $data;

        /**
         * Response constructor
         *
         * @param int $httpCode
         * @param string $contentType
         * @param array|string $data
         */
        public function __construct($httpCode = 200, $contentType = Http::CONTENT_TYPE_TEXT, $data = "OK")
        {
            $this->setHttpCode($httpCode);
            $this->setContentType($contentType);
            $this->setData($data);
        }

        /**
         * Set the main response fields
         *
         * @param int $httpCode
         * @param string $contentType
         * @param array|string $data
         * @return Response
         */
        public function setup($httpCode, $contentType, $data)
        {
            $this->setHttpCode($httpCode);
            $this->setContentType($contentType);
            $this->setData($data);
            return $this;
        }

        /**
         * @return int
         */
        public function getHttpCode()
        {
            return $this->httpCode;
        }

        /**
         * @param int $httpCode
         * @return Response updated object
         */
        public function setHttpCode($httpCode)
        {
            $this->httpCode = $httpCode;
            return $this;
        }

        /**
         * @return string
         */
        public function getContentType()
        {
            return $this->contentType;
        }

        /**
         * @param string $contentType
         * @return Response updated object
         */
        public function setContentType($contentType)
        {
            $this->contentType = $contentType;
            $this->addHeader("Content-Type", $this->contentType);
            return $this;
        }

        /**
         * Add a HTTP header
         * Supports adding multiple values for a header
         *
         * @param string $name header name
         * @param string|string[] $value header value
         * @param boolean $multiValues if false, overwrites current header with the same name; otherwise adds another value
         * @return Response $this Updated object
         */
        public function addHeader($name, $value, $multiValues = false)
        {
            // if header already exists
            if (isset($this->headers[$name]) && $multiValues) {
                if (!is_array($this->headers[$name])) {
                    $this->headers[$name] = array($this->headers[$name]);
                }
                // if value is array
                if (is_array($value)) {
                    $this->headers[$name] = array_merge($this->headers[$name], $value);
                } else {
                    $this->headers[$name][] = $value;
                }
            } else {
                $this->headers[$name] = $value;
            }
            return $this;
        }

        /**
         * @param string $name header name
         * @return string|string[]|null header value(s) or null if not exists
         */
        public function getHeader($name)
        {
            if (isset($this->headers[$name])) {
                return $this->headers[$name];
            }
            return null;
        }

        /**
         * @return array|string
         */
        public function getData()
        {
            return $this->data;
        }

        /**
         * @param array|string $data
         * @return Response updated object
         */
        public function setData($data)
        {
            $this->data = $data;
            return $this;
        }

        /**
         * Set the HTTP response code
         */
        protected function setHttpResponseCode()
        {
            http_response_code($this->httpCode);
        }

        /**
         * Set the HTTP response headers
         */
        protected function setHttpResponseHeaders()
        {
            foreach ($this->headers as $headerName => $headerValue) {
                if (is_array($headerValue)) {
                    foreach ($headerValue as $headerVal) {
                        header($headerName . ": " . $headerVal);
                    }
                } else {
                    header($headerName . ": " . $headerValue);
                }
            }
        }

        /**
         * Encode the response data according to the content type
         *
         * @return string encoded response data
         * @throws \HttpRuntimeException
         */
        protected function encodeData()
        {
            switch ($this->contentType) {
                case Http::CONTENT_TYPE_TEXT:
                    return $this->data;
                    break;

                case Http::CONTENT_TYPE_JSON:
                    return json_encode($this->data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
                    break;

                default:
                    throw new \HttpRuntimeException("Unknown content type " . $this->contentType);
            }
        }

        /**
         * Send the HTTP response
         */
        public function send()
        {
            $this->setHttpResponseCode();
            $this->setHttpResponseHeaders();
            echo $this->encodeData();
        }

    }

}
