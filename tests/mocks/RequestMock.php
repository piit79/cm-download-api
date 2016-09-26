<?php

namespace Tests\Mocks {


    use Fw\Http\RequestInterface;

    class RequestMock implements RequestInterface
    {

        /**
         * @var string
         */
        private $method;

        /**
         * @var string
         */
        private $uri;

        /**
         * @var string[]
         */
        private $_request;

        /**
         * RequestMock constructor
         *
         * @param string $method
         * @param string $uri
         */
        public function __construct($method, $uri)
        {
            $this->method = $method;
            $this->uri = $uri;
            $this->_request = array();
        }

        /**
         * Gets a variable from the $_request array applying filters if needed.
         * If no parameters are given the $_REQUEST superglobal is returned
         *
         * @param string $name
         * @param string|array $filters not implemented yet
         * @param mixed $defaultValue
         * @return mixed
         */
        public function get($name = null, $filters = null, $defaultValue = null)
        {
            if ($name == null) {
                return $this->_request;
            }
            if ($this->has($name)) {
                return $this->_request[$name];
            }
            return $defaultValue;
        }

        /**
         * Set a value in the $_request array
         *
         * @param string $name
         * @param mixed $value
         */
        public function set($name, $value)
        {
            $this->_request[$name] = $value;
        }

        /**
         * Gets a variable from the $_POST superglobal applying filters if needed
         * If no parameters are given the $_POST superglobal is returned
         *
         * @param string $name
         * @param string|array $filters
         * @param mixed $defaultValue
         * @return mixed
         */
        public function getPost($name = null, $filters = null, $defaultValue = null)
        {
            // TODO: Implement getPost() method.
        }

        /**
         * Gets variable from $_GET superglobal applying filters if needed
         * If no parameters are given the $_GET superglobal is returned
         *
         * @param string $name
         * @param string|array $filters
         * @param mixed $defaultValue
         * @return mixed
         */
        public function getQuery($name = null, $filters = null, $defaultValue = null)
        {
            // TODO: Implement getQuery() method.
        }

        /**
         * Gets variable from $_SERVER superglobal
         *
         * @param string $name
         * @return mixed
         */
        public function getServer($name)
        {
            // TODO: Implement getServer() method.
        }

        /**
         * Checks whether $_request array has certain index
         *
         * @param string $name
         * @return boolean
         */
        public function has($name)
        {
            return isset($this->_request[$name]);
        }

        /**
         * Checks whether $_POST superglobal has certain index
         *
         * @param string $name
         * @return boolean
         */
        public function hasPost($name)
        {
            // TODO: Implement hasPost() method.
        }

        /**
         * Checks whether put has certain index
         *
         * @param string $name
         * @return boolean
         */
        public function hasPut($name)
        {
            // TODO: Implement hasPut() method.
        }

        /**
         * Checks whether $_GET superglobal has certain index
         *
         * @param string $name
         * @return boolean
         */
        public function hasQuery($name)
        {
            // TODO: Implement hasQuery() method.
        }

        /**
         * Checks whether $_SERVER superglobal has certain index
         *
         * @param string $name
         * @return mixed
         */
        public function hasServer($name)
        {
            // TODO: Implement hasServer() method.
        }

        /**
         * Return request content type
         *
         * @return string
         */
        public function getContentType()
        {
            // TODO: Implement getContentType() method.
        }

        /**
         * Gets HTTP header from request data
         *
         * @param string $header
         * @return string
         */
        public function getHeader($header)
        {
            // TODO: Implement getHeader() method.
        }

        /**
         * Gets HTTP scheme (http/https)
         *
         * @return string
         */
        public function getScheme()
        {
            // TODO: Implement getScheme() method.
        }

        /**
         * Checks whether request has been made using ajax. Checks if $_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest'
         *
         * @return boolean
         */
        public function isAjax()
        {
            // TODO: Implement isAjax() method.
        }

        /**
         * Gets HTTP raw request body
         *
         * @return string
         */
        public function getRawBody()
        {
            // TODO: Implement getRawBody() method.
        }

        /**
         * Gets decoded JSON HTTP raw request body
         *
         * @param boolean $assoc
         * @return string
         */
        public function getJsonRawBody($assoc = true)
        {
            // TODO: Implement getJsonRawBody() method.
        }

        /**
         * Gets HTTP method which request has been made
         *
         * @return string
         */
        public function getMethod()
        {
            return $this->method;
        }

        /**
         * Set request method
         *
         * @param string $method
         */
        public function setMethod($method)
        {
            $this->method = $method;
        }

        /**
         * Gets HTTP URI which request has been made
         *
         * @return string
         */
        public function getURI()
        {
            return $this->uri;
        }

        /**
         * Set request URI
         *
         * @param string $uri
         */
        public function setURI($uri)
        {
            $this->uri = $uri;
        }

        /**
         * Gets HTTP user agent used to made the request
         *
         * @return string
         */
        public function getUserAgent()
        {
            // TODO: Implement getUserAgent() method.
        }

        /**
         * Checks whether HTTP method is POST. if $_SERVER['REQUEST_METHOD']=='POST'
         *
         * @return boolean
         */
        public function isPost()
        {
            // TODO: Implement isPost() method.
        }

        /**
         * Checks whether HTTP method is GET. if $_SERVER['REQUEST_METHOD']=='GET'
         *
         * @return boolean
         */
        public function isGet()
        {
            // TODO: Implement isGet() method.
        }

        /**
         * Checks whether HTTP method is PUT. if $_SERVER['REQUEST_METHOD']=='PUT'
         *
         * @return boolean
         */
        public function isPut()
        {
            // TODO: Implement isPut() method.
        }

        /**
         * Checks whether HTTP method is PATCH. if $_SERVER['REQUEST_METHOD']=='PATCH'
         *
         * @return boolean
         */
        public function isPatch()
        {
            // TODO: Implement isPatch() method.
        }

        /**
         * Checks whether HTTP method is HEAD. if $_SERVER['REQUEST_METHOD']=='HEAD'
         *
         * @return boolean
         */
        public function isHead()
        {
            // TODO: Implement isHead() method.
        }

        /**
         * Checks whether HTTP method is DELETE. if $_SERVER['REQUEST_METHOD']=='DELETE'
         *
         * @return boolean
         */
        public function isDelete()
        {
            // TODO: Implement isDelete() method.
        }

        /**
         * Checks whether HTTP method is OPTIONS. if $_SERVER['REQUEST_METHOD']=='OPTIONS'
         *
         * @return boolean
         */
        public function isOptions()
        {
            // TODO: Implement isOptions() method.
        }

        /**
         * Returns the available headers in the request
         *
         * @return array
         */
        public function getHeaders()
        {
            // TODO: Implement getHeaders() method.
        }
    }
}
