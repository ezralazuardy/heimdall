<?php namespace Heimdall;

use Heimdall\Exception\HeimdallConfigException;
use Heimdall\Exception\HeimdallServerException;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use DateInterval;
use Exception;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Psr\Http\Message\ResponseInterface;

/**
 * Class HeimdallServer
 * @package Heimdall\Src
 */
class HeimdallServer
{
    use ResponseTrait;

    /**
     * @var AuthorizationServer $server
     */
    private $server;

    /**
     * @param HeimdallConfig $config
     * @param HeimdallOIDC|null $oidc
     * @return $this
     */
    private function initialize(HeimdallConfig $config, $oidc)
    {
        try {
            if(empty(getenv('encryption.key'))) throw new HeimdallConfigException(
                'Cant\'t get encryption key from .env'
            );
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
            throw new HeimdallConfigException(
                'Error when initializing Heimdall, please check your configuration.',
                $exception->getCode()
            );
        }
    }

    /**
     * @param HeimdallGrantType $grantType
     * @return $this
     */
    private function setGrantType(HeimdallGrantType $grantType)
    {
        try {
            $this->server->enableGrantType(
                $grantType->getGrantType(),
                new DateInterval($grantType->getAccessTokenTTL())
            );
            return $this;
        } catch (Exception $exception) {
            throw new HeimdallConfigException(
                'Error when applying Heimdall grant type, please check your configuration.',
                $exception->getCode()
            );
        }
    }

    /**
     * HeimdallServer constructor.
     * @param HeimdallConfig $config
     * @param HeimdallGrantType $grantType
     * @param HeimdallOIDC|null $oidc
     * @throws Exception
     */
    function __construct(HeimdallConfig $config, HeimdallGrantType $grantType, HeimdallOIDC $oidc = null)
    {
        $this->initialize($config, $oidc)->setGrantType($grantType);
    }

    /**
     * @param $request
     * @param $response
     */
    function bootstrap(&$request, &$response)
    {
        $this->request = &$request;
        $this->response = &$response;
    }

    /**
     * @throws Exception
     */
    function validateRequestAndResponse()
    {
        if(empty($this->request))
            throw new Exception('Server Request is undefined, please apply it via bootstrap().');
        else if(empty($this->response))
            throw new Exception('Server Response is undefined, please apply it via bootstrap().');
    }

    /**
     * @param IncomingRequest $request
     * @return HeimdallServer
     */
    function withRequest(IncomingRequest $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @param Response $response
     * @return HeimdallServer
     */
    function withResponse(Response $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @param ResponseInterface $generatedResponse
     * @throws Exception
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
        Heimdall::handleException($exception, $this->response);
    }

    /**
     * @return AuthorizationRequest
     * @throws Exception
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
                $exception->getErrorType()
            );
        }
    }

    /**
     * @param AuthorizationRequest $authorizationRequest
     * @throws Exception
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
                'unknown'
            );
        }
    }

    /**
     * @throws Exception
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
                $exception->getErrorType()
            );
        }
    }
}
