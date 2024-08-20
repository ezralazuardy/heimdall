<?php namespace Heimdall\Http;

use CodeIgniter\HTTP\IncomingRequest;

/**
 * Class HeimdallRequest
 * @package Heimdall
 */
class HeimdallRequest extends Request
{
    /**
     * HeimdallRequest constructor.
     * @param IncomingRequest $request
     */
    function __construct(IncomingRequest $request)
    {
        $username = null;
        $password = null;
        $uri = $request->getUri()->showPassword(true);
        if (strpos($uri->getUserInfo(), ':')) {
            $userInfo = explode(':', $uri->getUserInfo());
            $username = $userInfo[0];
            $password = $userInfo[1];
        } else if (!empty($uri->getUserInfo())) {
            $username = $uri->getUserInfo();
        }
        $headers = [];
        foreach ($request->getHeaders() as $key => $value) {
            $key = str_replace('-', '_', strtoupper($key));
            if (!strpos($key, 'HTTP')) $key = "HTTP_$key";
            $headers[$key] = array($value->getValue());
        }
        parent::__construct(
            $request->getMethod(),
            new Uri(
                $request->getUri()->getScheme(),
                $request->getUri()->getHost(),
                $request->getUri()->getPort(),
                $request->getUri()->getPath(),
                $request->getUri()->getQuery(),
                $request->getUri()->getFragment(),
                $username,
                $password
            ),
            new Headers($headers),
            $request->getCookie(),
            $request->getServer(),
            new Body(fopen('php://temp', 'r+')),
            $request->getFiles()
        );
    }
}
