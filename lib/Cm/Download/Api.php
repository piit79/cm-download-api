<?php

namespace Cm\Download {


    use Fw\Http;
    use Fw\Http\Response;

    class Api
    {

        const URI_API = '/api';
        const URI_API_V1 = '/api/v1/';

        /**
         * Build filesystem root
         *
         * @var string
         */
        private $root;

        /**
         * Build filesystem base URL
         *
         * @var string
         */
        private $baseUrl;

        /**
         * Request content type
         *
         * @var string
         */
        private $contentType;

        /**
         * Request method (GET/POST...)
         *
         * @var string
         */
        private $requestMethod;

        /**
         * Request URI
         *
         * @var string
         */
        private $uri;

        /**
         * Request URI parts split by /
         *
         * @var array
         */
        private $uriParts;

        /**
         * Array containing request decoded from json
         *
         * @var array
         */
        private $request;

        /**
         * CmDownloadApi constructor
         *
         * @param string $root
         * @param string $baseUrl
         */
        function __construct($root, $baseUrl)
        {
            $this->root = $root;
            $this->baseUrl = $baseUrl;
        }

        /**
         *
         */
        protected function getRequest()
        {
            $this->requestMethod = $_SERVER['REQUEST_METHOD'];
            $this->contentType = $this->getContentType();
            $this->uri = $_SERVER['REQUEST_URI'];
            $uriParts = explode('/', $this->uri);
            $this->uriParts = array();
            foreach ($uriParts as $part) {
                if ($part != "") {
                    $this->uriParts[] = $part;
                }
            }

            switch ($this->requestMethod) {
                case 'POST':
                    $this->request = $this->decodeRequest();
                    break;

                default:
                    Response::create(405, Http::CONTENT_TYPE_TEXT,
                        "405 Method Not Allowed\n\nThe method GET is not allowed for this resource.")->send();
                    exit();
            }
        }

        /**
         * Get request content type
         *
         * @return string
         */
        protected function getContentType()
        {
            $contentTypeHeader = $_SERVER['CONTENT_TYPE'];
            if (strpos($contentTypeHeader, ';') === false) {
                $contentType = $contentTypeHeader;
            } else {
                list($contentType) = explode(';', $contentTypeHeader);
            }

            return $contentType;
        }

        /**
         * Decode the input data
         *
         * @return array
         */
        protected function decodeRequest()
        {
            $requestData = file_get_contents("php://input");

            switch ($this->contentType) {
                case Http::CONTENT_TYPE_JSON:
                    $json = json_decode($requestData, true);
                    return $json;
                    break;
            }

            return false;
        }

        /**
         * Get API call name
         *
         * @return boolean
         */
        protected function getApiCall()
        {
            if ($this->uri == self::URI_API) {
                if (isset($this->request['method'])
                    && $this->request['method']
                    && method_exists($this, $this->request['method'])
                ) {
                    return $this->request['method'];
                }
            } elseif (strpos($this->uri, self::URI_API_V1) === 0) {
                reset($this->uriParts);
                $method = end($this->uriParts);
                if (method_exists($this, $method)) {
                    return $method;
                }
            }
            return false;
        }

        /**
         * Handle the API request
         *
         * @return boolean
         */
        public function handle()
        {
            $this->getRequest();
            $apiCall = $this->getApiCall();
            if (!$apiCall) {
                $output = array(
                    'id' => null,
                    'result' => null,
                    'error' => "Error decoding JSON",
                );
                Response::create(200, Http::CONTENT_TYPE_JSON, $output)->send();
                exit();
            }
            return $this->$apiCall();
        }

        /**
         * Return the date part of the build filename
         *
         * @param string $filename
         * @return string The date of the build in YYYYMMDD format
         */
        protected function getBuildDate($filename) {
            $parts = explode('-', $filename);
            return $parts[2];
        }

        /**
         * get_all_builds API call
         */
        protected function get_all_builds()
        {
            if (!isset($this->request['params']) || !is_array($this->request['params'])
                || !isset($this->request['params']['device']) || !$this->request['params']['device']) {
                // invalid request
            }
            $device = $this->request['params']['device'];
            $channel = "nightly";
            $apiLevel = 23;
            $device_dir = $this->root . DIRECTORY_SEPARATOR . $device;
            if (is_dir($device_dir)) {
                $files = scandir($device_dir);
            } else {
                $files = array();
            }
            $output = array();
            $output['id'] = null;
            $output['error'] = null;
            $result = array();
            foreach ($files as $filename) {
                $file_path_rel = $device . DIRECTORY_SEPARATOR . $filename;
                $file_path_full = $this->root . DIRECTORY_SEPARATOR . $file_path_rel;
                if (!is_file($file_path_full) || substr($filename, -4) != ".zip") {
                    continue;
                }
                $fileUrl = $this->baseUrl . '/' . $file_path_rel;
                $stat = stat($file_path_full);
                $timestamp = $stat[9];
                $md5sumFile = $file_path_full . ".md5sum";
                $md5sum = null;
                if (is_file($md5sumFile)) {
                    $md5sumContents = file_get_contents($md5sumFile);
                    list($md5sumLine) = explode("\n", $md5sumContents);
                    if (preg_match('/^([0-9a-f]{32}) /', $md5sumLine, $m)) {
                        $md5sum = $m[1];
                    }
                }
                $incremental = "4a97bfd9e2";
                $changes = $this->baseUrl . '/' . $device . '/' . str_replace(".zip", ".changes", $filename);
                $build = new Api\Build(
                    $fileUrl,
                    $filename,
                    $timestamp,
                    $md5sum,
                    $incremental,
                    $changes,
                    $channel,
                    $apiLevel
                );
                $result[] = $build->toArray();
            }
            $output['result'] = $result;
            Response::create(200, Http::CONTENT_TYPE_JSON, $output)->send();
        }

        /**
         * get_delta API call - stub, always returns 200 Unable to find delta
         */
        protected function get_delta()
        {
            // TODO: investigate incremental updates
            /** @noinspection PhpUnusedLocalVariableInspection */
            $responseInvalidInput = array(
                'errors' => array(
                    array(
                        'reason' => "internal server error",
                        'message' => "Internal Server Error",
                    ),
                ),
            );
            $responseDeltaNotFound = array(
                'errors' => array(
                    array(
                        'message' => "Unable to find delta",
                    ),
                ),
            );
            Response::create(200, Http::CONTENT_TYPE_JSON, $responseDeltaNotFound)->send();
        }
    }
}


/*
/api get_all_builds request
{
   "method":"get_all_builds",
   "params":{
      "device":"i9100",
      "channels":[
         "nightly"
      ],
      "source_incremental":"39e618492a"
   }
}

Response:
{
 "id": null,
 "result": [
  {
   "url": "http://get.cm/get/jenkins/133969/cm-12.1-20151109-NIGHTLY-i9100.zip",
   "timestamp": "1447056702",
   "md5sum": "ba74933aa68ed4a278ca82c02cfd6d25",
   "filename": "cm-12.1-20151109-NIGHTLY-i9100.zip",
   "incremental": "4a97bfd9e2",
   "channel": "nightly",
   "changes": "http://get.cm/get/jenkins/133969/CHANGES.txt",
   "api_level": 22
  },
  {
   "url": "http://get.cm/get/jenkins/133634/cm-12.1-20151107-NIGHTLY-i9100.zip",
   "timestamp": "1446888318",
   "md5sum": "b8cb8aea6aa8718352288a8868cb7508",
   "filename": "cm-12.1-20151107-NIGHTLY-i9100.zip",
   "incremental": "e7d32d460d",
   "channel": "nightly",
   "changes": "http://get.cm/get/jenkins/133634/CHANGES.txt",
   "api_level": 22
  },
  {
   "url": "http://get.cm/get/jenkins/133497/cm-12.1-20151106-NIGHTLY-i9100.zip",
   "timestamp": "1446799192",
   "md5sum": "352ee38bf52866268df33145cd203c3a",
   "filename": "cm-12.1-20151106-NIGHTLY-i9100.zip",
   "incremental": "19a0ea51f6",
   "channel": "nightly",
   "changes": "http://get.cm/get/jenkins/133497/CHANGES.txt",
   "api_level": 22
  }
 ],
 "error": null
}

/api/v1/build/get_delta request:
{
  "source_incremental":"39e618492a",
  "target_incremental":"6b7521196e"
}

Error response:
{
 "errors": [
   {
    "reason": "internal_server_error",
    "message": "Internal Server Error"
   }
 ]
}
Error response:
{
  "errors": [
   {
    "message": "Unable to find delta"
   }
 ]
}
 */
