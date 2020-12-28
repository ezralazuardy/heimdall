<?php namespace Heimdall\Server;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use Exception;
use Heimdall\Config\HeimdallResourceConfig;
use Heimdall\Exception\HeimdallConfigException;
use Heimdall\Exception\HeimdallServerException;
use Heimdall\Heimdall;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;

/**
 * Class HeimdallResourceServer
 * @package Heimdall\Server
 */
class HeimdallResourceServer
{
    /**
     * @var ResourceServer
     */
    private $server;

    /**
     * HeimdallResourceServer constructor.
     * @param HeimdallResourceConfig $config
     */
    function __construct(HeimdallResourceConfig $config)
    {
        $this->initialize($config);
    }

    /**
     * @param HeimdallResourceConfig $config
     * @return $this|void
     */
    private function initialize(HeimdallResourceConfig $config): HeimdallResourceServer
    {
        try {
            $this->server = new ResourceServer(
                $config->getAccessTokenRepository(),
                $config->getPublicKey(),
                $config->getAuthorizationValidator()
            );
            return $this;
        } catch (Exception $exception) {
            $this->handleException(new HeimdallConfigException(
                'Error when initializing Heimdall Resource Server, please check your configuration.',
                $exception->getCode()
            ));
        }
    }

    /**
     * @param Exception $exception
     */
    function handleException(Exception $exception)
    {
        Heimdall::handleException($exception);
    }

    /**
     * @param RequestInterface $request
     * @throws HeimdallServerException
     */
    function validate(RequestInterface $request)
    {
        try {
            return $this->server->validateAuthenticatedRequest(
                Heimdall::handleRequest(
                    new IncomingRequest(config('app'),
                        $request->uri, $request->getBody(),
                        $request->getUserAgent()
                    )
                )
            );
        } catch (OAuthServerException $exception) {
            throw new HeimdallServerException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception->getErrorType(),
                $exception->getHttpStatusCode(),
                $exception->getHint()
            );
        }
    }
}