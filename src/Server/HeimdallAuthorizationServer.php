<?php namespace Heimdall\Server;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use DateInterval;
use Exception;
use Heimdall\Config\HeimdallAuthorizationConfig;
use Heimdall\Config\HeimdallAuthorizationGrant;
use Heimdall\Exception\HeimdallConfigException;
use Heimdall\Exception\HeimdallServerException;
use Heimdall\Heimdall;
use Heimdall\Extension\HeimdallOIDC;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Psr\Http\Message\ResponseInterface;

/**
 * Class HeimdallAuthorizationServer
 * @package Heimdall\Server
 */
class HeimdallAuthorizationServer
{
    use ResponseTrait;

    /**
     * @var AuthorizationServer $server
     */
    private $server;

    /**
     * @param HeimdallAuthorizationConfig $config
     * @param $oidc
     * @return $this|void
     */
    private function initialize(HeimdallAuthorizationConfig $config, $oidc): HeimdallAuthorizationServer
    {
        try {
            if(empty(getenv('encryption.key'))) $this->handleException(new HeimdallConfigException(
                'Cant\'t get encryption key from .env.',
                2
            ));
            $this->server = new AuthorizationServer(
                $config->getClientRepository(),
                $config->getAccessTokenRepository(),
                $config->getScopeRepository(),
                $config->getPrivateKey(),
                getenv('encryption.key'),
                ($oidc === null) ? $config->getResponseType() : $oidc->getResponseType()
            );
            return $this;
        } catch (Exception $exception) {
            $this->handleException(new HeimdallConfigException(
                'Error when initializing Heimdall Authorization Server, please check your configuration.',
                $exception->getCode()
            ));
        }
    }

    /**
     * @param HeimdallAuthorizationGrant $grant
     * @return $this|void
     */
    private function setGrantType(HeimdallAuthorizationGrant $grant): HeimdallAuthorizationServer
    {
        try {
            $this->server->enableGrantType(
                $grant->getGrantType(),
                new DateInterval($grant->getAccessTokenTTL())
            );
            return $this;
        } catch (Exception $exception) {
            $this->handleException(new HeimdallConfigException(
                'Error when applying Heimdall grant type, please check your configuration.',
                $exception->getCode()
            ));
        }
    }

    /**
     * HeimdallAuthorizationServer constructor.
     * @param HeimdallAuthorizationConfig $config
     * @param HeimdallAuthorizationGrant $grant
     * @param HeimdallOIDC|null $oidc
     */
    function __construct(
        HeimdallAuthorizationConfig $config,
        HeimdallAuthorizationGrant $grant,
        HeimdallOIDC $oidc = null
    ) {
        $this->initialize($config, $oidc)->setGrantType($grant);
    }

    /**
     * @param $request
     * @param $response
     * @return $this
     */
    function bootstrap(&$request, &$response): HeimdallAuthorizationServer
    {
        $this->request = &$request;
        $this->response = &$response;
        return $this;
    }

    /**
     * @return void
     */
    function validateRequestAndResponse()
    {
        if(empty($this->request))
            $this->handleException(
                new HeimdallServerException(
                    'Server Request is undefined, please apply it via bootstrap().',
                    0,
                    'heimdall_bootstrap_request_error',
                    500
                )
            );
        else if(empty($this->response))
            $this->handleException(
                new HeimdallServerException(
                    'Server Response is undefined, please apply it via bootstrap().',
                    1,
                    'heimdall_bootstrap_response_error',
                    500
                )
            );
    }

    /**
     * @param IncomingRequest $request
     * @return HeimdallAuthorizationServer
     */
    function withRequest(IncomingRequest $request): HeimdallAuthorizationServer
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @param Response $response
     * @return HeimdallAuthorizationServer
     */
    function withResponse(Response $response): HeimdallAuthorizationServer
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @param ResponseInterface $generatedResponse
     * @return Response|void
     */
    function return(ResponseInterface $generatedResponse)
    {
        $this->validateRequestAndResponse();
        Heimdall::return($generatedResponse, $this->response);
    }

    /**
     * @param Exception $exception
     */
    function handleException(Exception $exception)
    {
        Heimdall::handleException($exception);
    }

    /**
     * @return AuthorizationRequest|Response|void
     * @throws HeimdallServerException
     */
    function validateAuth()
    {
        try {
            $authRequest = $this->server->validateAuthorizationRequest(Heimdall::handleRequest($this->request));
            $authRequest->setAuthorizationApproved(true);
            return $authRequest;
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

    /**
     * @param AuthorizationRequest $authorizationRequest
     * @return ResponseInterface|Response|void
     * @throws HeimdallServerException
     */
    function completeAuth(AuthorizationRequest $authorizationRequest)
    {
        try {
            $this->return($this->server->completeAuthorizationRequest(
                $authorizationRequest,
                Heimdall::handleResponse($this->response)
            ));
        } catch (Exception $exception) {
            throw new HeimdallServerException(
                $exception->getMessage(),
                $exception->getCode(),
                'complete_authorization_request_error',
                500
            );
        }
    }

    /**
     * @return ResponseInterface|Response|void
     * @throws HeimdallServerException
     */
    function createToken()
    {
        try {
            $this->return($this->server->respondToAccessTokenRequest(
                Heimdall::handleRequest($this->request),
                Heimdall::handleResponse($this->response)
            ));
        } catch (OAuthServerException $exception) {
            throw new HeimdallServerException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception->getErrorType(),
                $exception->getHttpStatusCode(),
                $exception->getHint()
            );
        } catch (Exception $exception) {
            throw new HeimdallServerException(
                $exception->getMessage(),
                $exception->getCode(),
                'respond_access_token_error',
                500
            );
        }
    }
}
