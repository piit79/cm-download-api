<?php

class CmDownloadApi
{

    const URI_API = '/api';
    const URI_API_V1 = '/api/v1/';

    const TYPE_TEXT = "text/plain";
    const TYPE_JSON = "application/json";

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
    private $base_url;

    /**
     * Request content type
     *
     * @var string
     */
    private $content_type;

    /**
     * Request method (GET/POST...)
     *
     * @var string
     */
    private $request_method;

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
    private $uri_parts;

    /**
     * Array containing request decoded from json
     *
     * @var array
     */
    private $request;

    /**
     * CmDownloadApi constructor.
     *
     * @param string $root
     * @param string $base_url
     */
    function __construct($root, $base_url)
    {
        $this->root = $root;
        $this->base_url = $base_url;
    }

    /**
     *
     */
    protected function getRequest()
    {
        $this->request_method = $_SERVER['REQUEST_METHOD'];
        $this->content_type = $this->getContentType();
        $this->uri = $_SERVER['REQUEST_URI'];
        $uri_parts = explode('/', $this->uri);
        $this->uri_parts = array();
        foreach ($uri_parts as $part) {
            if ($part != "") {
                $this->uri_parts[] = $part;
            }
        }

        switch ($this->request_method) {
            case 'POST':
                $this->request = $this->decodeRequest();
                break;

            default:
                $this->response(405, self::TYPE_TEXT, "405 Method Not Allowed\n\nThe method GET is not allowed for this resource.");
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
        $content_type_value = $_SERVER['CONTENT_TYPE'];
        if (strpos($content_type_value, ';') === FALSE) {
            $content_type = $content_type_value;
        } else {
            list($content_type) = explode(';', $content_type_value);
        }

        return $content_type;
    }

    /**
     * Decode the input data
     *
     * @return array
     */
    protected function decodeRequest()
    {
        $request_data = file_get_contents("php://input");

        switch ($this->content_type) {
            case self::TYPE_JSON:
                $json = json_decode($request_data, TRUE);
                return $json;
                break;
        }

        return FALSE;
    }

    /**
     * Get API call name
     *
     * @return bool
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
            reset($this->uri_parts);
            $method = end($this->uri_parts);
            if (method_exists($this, $method)) {
                return $method;
            }
        }
        return FALSE;
    }

    /**
     * Handle the API request
     *
     * @return bool
     */
    public function handle()
    {
        $this->getRequest();
        $apiCall = $this->getApiCall();
        if (!$apiCall) {
            $output = array(
                'id' => NULL,
                'result' => NULL,
                'error' => "Error decoding JSON",
            );
            $this->response(200, self::TYPE_JSON, $output);
            exit();
        }
        return $this->$apiCall();
    }

    /**
     * Output a HTTP code along with result data
     *
     * @param $code int
     * @param $content_type string
     * @param string $message string|array
     */
    protected function response($code, $content_type, $message = "")
    {
        http_response_code($code);
        header('Content-Type: ' . $content_type);
        switch ($content_type) {
            case self::TYPE_TEXT:
                echo $message;
                break;

            case self::TYPE_JSON:
                echo json_encode($message, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
                break;

            default:
                echo $message;
        }
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
        $api_level = 23;
        $device_dir = $this->root . DIRECTORY_SEPARATOR . $device;
        if (is_dir($device_dir)) {
            $files = scandir($device_dir);
        } else {
            $files = array();
        }
        $output = array();
        $output['id'] = NULL;
        $output['error'] = NULL;
        $result = array();
        foreach ($files as $filename) {
            $file_path_rel = $device . DIRECTORY_SEPARATOR . $filename;
            $file_path_full = $this->root . DIRECTORY_SEPARATOR . $file_path_rel;
            if (!is_file($file_path_full) || substr($filename, -4) != ".zip") {
                continue;
            }
            $build_date = $this->getBuildDate($filename);
            $file_url = $this->base_url . '/' . $file_path_rel;
            $stat = stat($file_path_full);
            $timestamp = $stat[9];
            $md5sum_file = $file_path_full . ".md5sum";
            $md5sum = NULL;
            if (is_file($md5sum_file)) {
                $md5sum_contents = file_get_contents($md5sum_file);
                list($md5sum_line) = explode("\n", $md5sum_contents);
                if (preg_match('/^([0-9a-f]{32}) /', $md5sum_line, $m)) {
                    $md5sum = $m[1];
                }
            }
            $incremental = "4a97bfd9e2";
            $changes = $this->base_url . '/' . $device . '/' . str_replace(".zip", ".changes", $filename);
            $build = array(
                'url' => $file_url,
                'filename' => $filename,
                'timestamp' => $timestamp,
                'md5sum' => $md5sum,
                'incremental' => $incremental,
                'changes' => $changes,
                'channel' => $channel,
                'api_level' => $api_level,
            );
            $result[] = $build;
        }
        $output['result'] = $result;
        $this->response(200, self::TYPE_JSON, $output);
    }

    /**
     * get_delta API call - stub, always returns 500
     */
    protected function get_delta()
    {
        $response_invalid_input = array(
            'errors' => array(
                array(
                    'reason' => "internal server error",
                    'message' => "Internal Server Error",
                ),
            ),
        );
        $response_delta_not_found = array(
            'errors' => array(
                array(
                    'message' => "Unable to find delta",
                ),
            ),
        );
        $this->response(200, self::TYPE_JSON, $response_delta_not_found);
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
