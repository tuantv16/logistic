<?php

namespace App\ResponseCode;

final class ResponseCode
{
    /**
     * HTTP Status Code
     */
    const SUCCESS = 200;
    const CREATED = 201;
    const NO_CONTENT = 204;
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const INTERNAL_SERVER_ERROR = 500;
    const SERVICE_UNAVAILABLE = 503;

    /**
     * Response Code
     */
    const SUCCESS_CODE = 2000;
    const UNKNOWN_ERROR = 2001;
    const VALIDATION_ERROR = 2002;

    const MAXIMUM_IMAGES = 3001;
    const ACCOUNT_EXIST = 3002;

}
