<?php

namespace Fw {


    abstract class Http
    {
        const SCHEME_HTTP = 'http';
        const SCHEME_HTTPS = 'https';

        const METHOD_GET = 'GET';
        const METHOD_HEAD = 'HEAD';
        const METHOD_POST = 'POST';
        const METHOD_PUT = 'PUT';
        const METHOD_PATCH = 'PATCH';
        const METHOD_DELETE = 'DELETE';
        const METHOD_OPTIONS = 'OPTIONS';

        const CONTENT_TYPE_TEXT = 'text/plain';
        const CONTENT_TYPE_JSON = 'application/json';
    }
}
