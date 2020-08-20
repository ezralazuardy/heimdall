<?php namespace Heimdall;

use Heimdall\Http\Headers;
use Heimdall\Http\Response;

/**
 * Class HeimdallResponse
 * @package Heimdall
 */
class HeimdallResponse extends Response
{
    /**
     * HeimdallResponse constructor.
     * @param \CodeIgniter\HTTP\Response $response
     */
    function __construct(\CodeIgniter\HTTP\Response $response)
    {
        $headers = [];
        foreach ($response->getHeaders() as $key => $value) $headers[$key] = array($value->getValue());
        parent::__construct(
            $response->getStatusCode(),
            new Headers($headers)
        );
    }
}