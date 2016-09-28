<?php

namespace Fw\Http {


    use Fw\Http;

    class Request implements RequestInterface
    {

        /**
         * @var array
         */
        protected $headers;

        /**
         * @var string
         */
        protected $contentType;

        /**
         * @var string
         */
        protected $rawBody;

        /**
         * Request constructor
         */
        public function __construct()
        {
            $this->headers = getallheaders();
            $this->rawBody = file_get_contents('php://input');
        }

        /**
         * Gets a variable from the $_REQUEST superglobal applying filters if needed.
         * If no parameters are given the $_REQUEST superglobal is returned
         *
         * @param string $name
         * @param string|array $filters
         * @param mixed $defaultValue
         * @return mixed
         */
        public function get($name = null, $filters = null, $defaultValue = null)
        {
            if ($name == null) {
                return $_REQUEST;
            }
            if (isset($_GET[$name])) {
                return $_GET[$name];
            } elseif ($defaultValue != null) {
                return $defaultValue;
            }
            return null;
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
            if ($name == null) {
                return $_POST;
            }
            if (isset($_POST[$name])) {
                return $_POST[$name];
            } elseif ($defaultValue != null) {
                return $defaultValue;
            }
            return null;
        }

        /**
         * Gets a variable from put request
         *
         * @param string $name
         * @param string|array $filters
         * @param mixed $defaultValue
         * @return mixed
         */
        /*
        public function getPut($name = null, $filters = null, $defaultValue = null)
        {
            if ($name == null) {
                return $_PUT;
            }
            if (isset($_PUT[$name])) {
                return $_PUT[$name];
            } elseif ($defaultValue != null) {
                return $defaultValue;
            }
            return null;
        }
        */

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
            if ($name == null) {
                return $_GET;
            }
            if (isset($_REQUEST[$name])) {
                return $_REQUEST[$name];
            } elseif ($defaultValue != null) {
                return $defaultValue;
            }
            return null;
        }

        /**
         * Gets variable from $_SERVER superglobal
         *
         * @param string $name
         * @return mixed
         */
        public function getServer($name)
        {
            if (isset($_SERVER[$name])) {
                return $_SERVER[$name];
            }
            return null;
        }

        /**
         * Checks whether $_REQUEST superglobal has certain index
         *
         * @param string $name
         * @return boolean
         */
        public function has($name)
        {
            return isset($_REQUEST[$name]);
        }

        /**
         * Checks whether $_POST superglobal has certain index
         *
         * @param string $name
         * @return boolean
         */
        public function hasPost($name)
        {
            return isset($_POST[$name]);
        }

        /**
         * Checks whether put has certain index
         *
         * @param string $name
         * @return boolean
         */
        public function hasPut($name)
        {
            return isset($_PUT[$name]);
        }

        /**
         * Checks whether $_GET superglobal has certain index
         *
         * @param string $name
         * @return boolean
         */
        public function hasQuery($name)
        {
            return isset($_GET[$name]);
        }

        /**
         * Checks whether $_SERVER superglobal has certain index
         *
         * @param string $name
         * @return mixed
         */
        public function hasServer($name)
        {
            return isset($_SERVER[$name]);
        }

        /**
         * Return request content type
         *
         * @return string
         */
        public function getContentType()
        {
            if ($this->contentType == null) {
                $contentTypeHeader = $this->getServer('CONTENT_TYPE');
                if (strpos($contentTypeHeader, ';') === false) {
                    $contentType = $contentTypeHeader;
                } else {
                    list($contentType) = explode(';', $contentTypeHeader);
                }
                $this->contentType = $contentType;
            }
            return $this->contentType;
        }

        /**
         * Gets HTTP header from request data
         *
         * @param string $header
         * @return string
         */
        public function getHeader($header)
        {
            if (isset($this->headers[$header])) {
                return $this->headers[$header];
            }
            return null;
        }

        /**
         * Gets HTTP scheme (http/https)
         *
         * @return string
         */
        public function getScheme()
        {
            if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
                return Http::SCHEME_HTTP;
            }
            return Http::SCHEME_HTTPS;
        }

        /**
         * Checks whether request has been made using ajax. Checks if $_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest'
         *
         * @return boolean
         */
        public function isAjax()
        {
            return $this->getServer('HTTP_X_REQUESTED_WITH') == 'XMLHttpRequest';
        }

        /**
         * Checks whether request has been made using SOAP
         *
         * @return boolean
         */
        //public abstract function isSoapRequested();

        /**
         * Checks whether request has been made using any secure layer
         *
         * @return boolean
         */
        //public abstract function isSecureRequest();

        /**
         * Gets HTTP raw request body
         *
         * @return string
         */
        public function getRawBody()
        {
            return $this->rawBody;
        }

        /**
         * Gets decoded JSON HTTP raw request body
         *
         * @param boolean $assoc
         * @return string
         */
        public function getJsonRawBody($assoc = true)
        {
            return json_decode($this->rawBody, $assoc);
        }

        /**
         * Gets active server address IP
         *
         * @return string
         */
        //public abstract function getServerAddress();

        /**
         * Gets active server name
         *
         * @return string
         */
        //public abstract function getServerName();

        /**
         * Gets information about schema, host and port used by the request
         *
         * @return string
         */
        //public abstract function getHttpHost();

        /**
         * Gets most possible client IPv4 Address. This method search in $_SERVER['REMOTE_ADDR'] and optionally in $_SERVER['HTTP_X_FORWARDED_FOR']
         *
         * @param boolean $trustForwardedHeader
         * @return string
         */
        //public abstract function getClientAddress($trustForwardedHeader = null);

        /**
         * Gets HTTP method which request has been made
         *
         * @return string
         */
        public function getMethod()
        {
            return $this->getServer('REQUEST_METHOD');
        }

        /**
         * Gets HTTP URI which request has been made
         *
         * @return string
         */
        public function getURI()
        {
            return $this->getServer('REQUEST_URI');
        }

        /**
         * Gets HTTP user agent used to made the request
         *
         * @return string
         */
        public function getUserAgent()
        {
            return $this->getServer('HTTP_USER_AGENT');
        }

        /**
         * Check if HTTP method match any of the passed methods
         *
         * @param string|array $methods
         * @return boolean
         */
        //public abstract function isMethod($methods);

        /**
         * Checks whether HTTP method is POST. if $_SERVER['REQUEST_METHOD']=='POST'
         *
         * @return boolean
         */
        public function isPost()
        {
            return $this->getMethod() == Http::METHOD_POST;
        }

        /**
         * Checks whether HTTP method is GET. if $_SERVER['REQUEST_METHOD']=='GET'
         *
         * @return boolean
         */
        public function isGet()
        {
            return $this->getMethod() == Http::METHOD_GET;
        }

        /**
         * Checks whether HTTP method is PUT. if $_SERVER['REQUEST_METHOD']=='PUT'
         *
         * @return boolean
         */
        public function isPut()
        {
            return $this->getMethod() == Http::METHOD_PUT;
        }

        /**
         * Checks whether HTTP method is PATCH. if $_SERVER['REQUEST_METHOD']=='PATCH'
         *
         * @return boolean
         */
        public function isPatch()
        {
            return $this->getMethod() == Http::METHOD_PATCH;
        }

        /**
         * Checks whether HTTP method is HEAD. if $_SERVER['REQUEST_METHOD']=='HEAD'
         *
         * @return boolean
         */
        public function isHead()
        {
            return $this->getMethod() == Http::METHOD_PATCH;
        }

        /**
         * Checks whether HTTP method is DELETE. if $_SERVER['REQUEST_METHOD']=='DELETE'
         *
         * @return boolean
         */
        public function isDelete()
        {
            return $this->getMethod() == Http::METHOD_DELETE;
        }

        /**
         * Checks whether HTTP method is OPTIONS. if $_SERVER['REQUEST_METHOD']=='OPTIONS'
         *
         * @return boolean
         */
        public function isOptions()
        {
            return $this->getMethod() == Http::METHOD_DELETE;
        }

        /**
         * Checks whether request includes attached files
         *
         * @return boolean
         */
        //public abstract function hasFiles();

        /**
         * Gets attached files as \Phalcon\Http\Request\File instances
         *
         * @param boolean $notErrored
         * @return mixed //\Phalcon\Http\Request\File[]
         */
        //public abstract function getUploadedFiles($notErrored = null);

        /**
         * Returns the available headers in the request
         *
         * @return array
         */
        public function getHeaders()
        {
            return $this->headers;
        }

        /**
         * Gets web page that refers active request. ie: http://www.google.com
         *
         * @return string
         */
        //public abstract function getHTTPReferer();

        /**
         * Gets array with mime/types and their quality accepted by the browser/client from $_SERVER['HTTP_ACCEPT']
         *
         * @return array
         */
        //public abstract function getAcceptableContent();

        /**
         * Gets best mime/type accepted by the browser/client from $_SERVER['HTTP_ACCEPT']
         *
         * @return array
         */
        //public abstract function getBestAccept();

        /**
         * Gets charsets array and their quality accepted by the browser/client from $_SERVER['HTTP_ACCEPT_CHARSET']
         *
         * @return array
         */
        //public abstract function getClientCharsets();

        /**
         * Gets best charset accepted by the browser/client from $_SERVER['HTTP_ACCEPT_CHARSET']
         *
         * @return string
         */
        //public abstract function getBestCharset();

        /**
         * Gets languages array and their quality accepted by the browser/client from $_SERVER['HTTP_ACCEPT_LANGUAGE']
         *
         * @return array
         */
        //public abstract function getLanguages();

        /**
         * Gets best language accepted by the browser/client from $_SERVER['HTTP_ACCEPT_LANGUAGE']
         *
         * @return string
         */
        //public abstract function getBestLanguage();

        /**
         * Gets auth info accepted by the browser/client from $_SERVER['PHP_AUTH_USER']
         *
         * @return array
         */
        //public abstract function getBasicAuth();

        /**
         * Gets auth info accepted by the browser/client from $_SERVER['PHP_AUTH_DIGEST']
         *
         * @return array
         */
        //public abstract function getDigestAuth();

    }

}
