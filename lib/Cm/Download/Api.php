<?php

namespace Cm\Download {


    use Cm\Download\Api\BuildListInterface;
    use Fw\DI\InjectionAware;
    use Fw\Http;
    use Fw\Http\RequestInterface;
    use Fw\Http\ResponseInterface;

    class Api extends InjectionAware
    {

        const CHANNEL_NIGHTLY = 'nightly';
        const URI_API = '/api';
        const URI_API_V1 = '/api/v1/';

        /**
         * @var RequestInterface
         */
        private $request;

        /**
         * @var ResponseInterface
         */
        private $response;

        /**
         * Request URI parts split by /
         *
         * @var array
         */
        private $uriParts;

        /**
         * Array containing request data decoded from json
         *
         * @var array
         */
        private $requestData;

        /**
         * @var BuildListInterface
         */
        private $buildList;

        /**
         * Api constructor
         */
        function __construct()
        {
            parent::__construct();
            $this->request = $this->di->get('request');
            $this->response = $this->di->get('response');
            $this->buildList = $this->di->get('buildList');
        }

        /**
         * Get request info
         */
        protected function getRequest()
        {
            $uriParts = explode('/', $this->request->getURI());
            $this->uriParts = array();
            foreach ($uriParts as $part) {
                if ($part != '') {
                    $this->uriParts[] = $part;
                }
            }

            switch ($this->request->getMethod()) {
                case Http::METHOD_POST:
                    $this->requestData = self::decodeRequestData($this->request->getContentType(), $this->request->getRawBody());
                    break;

                default:
                    $this->response->setup(405, Http::CONTENT_TYPE_TEXT,
                        "405 Method Not Allowed\n\nThe method GET is not allowed for this resource.")->send();
                    exit();
            }
        }

        /**
         * Decode raw input data according to content type
         *
         * @param string $contentType data content type
         * @param string $rawData raw data
         * @return array|false
         */
        protected static function decodeRequestData($contentType, $rawData)
        {
            switch ($contentType) {
                case Http::CONTENT_TYPE_JSON:
                    $json = json_decode($rawData, true);
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
            if ($this->request->getURI() == self::URI_API) {
                if (isset($this->requestData['method'])
                    && $this->requestData['method']
                    && method_exists($this, $this->requestData['method'])
                ) {
                    return $this->requestData['method'];
                }
            } elseif (strpos($this->request->getURI(), self::URI_API_V1) === 0) {
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
                    'error' => 'Error decoding JSON',
                );
                $this->response->setup(200, Http::CONTENT_TYPE_JSON, $output)->send();
                exit();
            }
            return $this->$apiCall();
        }

        /**
         * get_all_builds API call
         */
        protected function get_all_builds()
        {
            if (!isset($this->requestData['params']) || !is_array($this->requestData['params'])
                || !isset($this->requestData['params']['device']) || !$this->requestData['params']['device']) {
                // invalid request
            }
            $device = $this->requestData['params']['device'];
            $channel = self::CHANNEL_NIGHTLY;
            $builds = $this->buildList->getBuilds($device, $channel);
            $result = array();
            foreach ($builds as $build) {
                $result[] = $build->toArray();
            }
            $output['result'] = $result;
            $this->response->setup(200, Http::CONTENT_TYPE_JSON, $output)->send();
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
                        'reason' => 'internal server error',
                        'message' => 'Internal Server Error',
                    ),
                ),
            );
            $responseDeltaNotFound = array(
                'errors' => array(
                    array(
                        'message' => 'Unable to find delta',
                    ),
                ),
            );
            $this->response->setup(200, Http::CONTENT_TYPE_JSON, $responseDeltaNotFound)->send();
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
