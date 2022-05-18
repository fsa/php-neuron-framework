<?php

namespace FSA\Neuron;

class Response
{

    const HTTP_STATUS_CODES = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Moved Temporarily',
        303 => 'See Other',
        304 => 'Not Modified',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        429 => 'Too Many Requests',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported'
    ];

    public function redirection($location, int $code = 302)
    {
        header("Location: $location", true, $code);
        printf('Location: <a href="%s">%s</a>', $location, $location);
        exit;
    }
    
    public function return($response)
    {
        echo (string)$response;
        exit;
    }

    public function returnEmpty(int $http_response_code)
    {
        http_response_code($http_response_code);
        exit;
    }

    public function returnError(int $http_response_code, $message = null)
    {
        http_response_code($http_response_code);
        die('<center><h1>' . $http_response_code . ' ' . ($message ?? self::HTTP_STATUS_CODES[$http_response_code] ?? 'Unknown http status code') . '</h1></center>');
    }
}
