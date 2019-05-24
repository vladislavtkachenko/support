<?php

namespace Omadonex\Support\Classes\Exceptions;

use Omadonex\Support\Classes\ConstantsExceptionsCodes;

class OmxCurlRequestException extends \Exception
{
    protected $curlCode;
    protected $curlError;

    public function __construct($curlCode, $curlError)
    {
        $this->curlCode = $curlCode;
        $this->curlError = $curlError;

        $message = "Response code: {$curlCode}\ncURL error: {$curlError}\n";

        parent::__construct($message, ConstantsExceptionsCodes::CURL_REQUEST_SEND);
    }
}