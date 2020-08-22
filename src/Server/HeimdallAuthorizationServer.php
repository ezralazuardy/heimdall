<?php namespace Heimdall\Server;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use DateInterval;
use Exception;
use Heimdall\Config\HeimdallAuthorizationConfig;
use Heimdall\Config\HeimdallAuthorizationGrantType;
use Heimdall\Exception\HeimdallConfigException;
use Heimdall\Heimdall;
use Heimdall\Plugin\HeimdallAuthorizationOIDC;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Psr\Http\Message\ResponseInterface;

/**
 * Class HeimdallServer
 * @package Heimdall\Src
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
     * @return HeimdallAuthorizationServer
     */
    private function initialize(HeimdallAuthorizationConfig $config, $oidc): HeimdallAuthorizationServer
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
                'Error when initializing Heimdall Authorization Server, please check your configuration.',
                $exception->getCode()
            );
        }
    }

    /**
     * @param HeimdallAuthorizationGrantType $grantType
     * @return $this
     */
    private function setGrantType(HeimdallAuthorizationGrantType $grantType): HeimdallAuthorizationServer
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
     * HeimdallAuthorizationServer constructor.
     * @param HeimdallAuthorizationConfig $config
     * @param HeimdallAuthorizationGrantType $grantType
     * @param HeimdallAuthorizationOIDC|null $oidc
     */
    function __construct(
        HeimdallAuthorizationConfig $config,
        HeimdallAuthorizationGrantType $grantType,
        HeimdallAuthorizationOIDC $oidc = null
    ) {
        $this->initialize($config, $oidc)->setGrantType($grantType);
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
     * @return AuthorizationRequest|Response|void
     */
    function validateAuth()
    {
        try {
            $authRequest = $this->server->validateAuthorizationRequest(Heimdall::handleRequest($this->request));
            $authRequest->setAuthorizationApproved(true);
            return $authRequest;
        } catch (Exception $exception) {
            Heimdall::handleException($exception, $this->response);
        }
    }

    /**
     * @param AuthorizationRequest $authorizationRequest
     * @return ResponseInterface|Response|void
     */
    function completeAuth(AuthorizationRequest $authorizationRequest)
    {
        try {
            $this->return($this->server->completeAuthorizationRequest(
                $authorizationRequest,
                Heimdall::handleResponse($this->response)
            ));
        } catch (Exception $exception) {
            Heimdall::handleException($exception, $this->response);
        }
    }

    /**
     * @return ResponseInterface|Response|void
     */
    function createToken()
    {
        try {
            $this->return($this->server->respondToAccessTokenRequest(
                Heimdall::handleRequest($this->request),
                Heimdall::handleResponse($this->response)
            ));
        } catch (Exception $exception) {
            Heimdall::handleException($exception, $this->response);
        }
    }
}
