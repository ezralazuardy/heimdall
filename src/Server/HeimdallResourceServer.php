<?php namespace Heimdall\Server;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use Exception;
use Heimdall\Config\HeimdallResourceConfig;
use Heimdall\Exception\HeimdallConfigException;
use Heimdall\Heimdall;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Psr\Http\Message\ServerRequestInterface;

class HeimdallResourceServer
{
    /**
     * @var ResourceServer
     */
    private $server;

    /**
     * @param HeimdallResourceConfig $config
     * @return $this
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
        } catch(Exception $exception) {
            throw new HeimdallConfigException(
                'Error when initializing Heimdall Resource Server, please check your configuration.',
                $exception->getCode()
            );
        }
    }

    /**
     * HeimdallResourceServer constructor.
     * @param HeimdallResourceConfig $config
     */
    function __construct(HeimdallResourceConfig $config)
    {
        $this->initialize($config);
    }

    /**
     * @param IncomingRequest $request
     * @param Response $response
     * @return ServerRequestInterface|Response|void
     */
    function validate(IncomingRequest $request, Response $response)
    {
        try {
            $this->server->validateAuthenticatedRequest(Heimdall::handleRequest($request));
        } catch (OAuthServerException $exception) {
            Heimdall::handleException($exception, $response);
        }
    }
}