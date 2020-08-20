<?php namespace Heimdall;

use Heimdall\Exception\HeimdallConfigException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use DateInterval;
use Exception;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Heimdall
 * @package Heimdall
 */
abstract class Heimdall
{
    /**
     * @param HeimdallConfig $config
     * @param HeimdallGrantType $grantType
     * @param HeimdallOIDC|null $oidc
     * @return HeimdallServer
     * @throws Exception
     */
    static function initialize(HeimdallConfig $config, HeimdallGrantType $grantType, HeimdallOIDC $oidc = null)
    {
        switch ($grantType->getCode()) {
            case HeimdallGrantType::AuthorizationCode:
                return new HeimdallServer($config, $grantType, $oidc);
            default:
                throw new HeimdallConfigException(
                    'Unknown Heimdall grant type, please recheck your parameter.'
                );
        }
    }

    /**
     * @param IncomingRequest $request
     * @return HeimdallRequest
     */
    static function handleRequest(IncomingRequest $request)
    {
        return (new HeimdallRequest($request))->withParsedBody($request->getPost());
    }

    /**
     * @param Response $response
     * @return HeimdallResponse
     */
    static function handleResponse(Response $response)
    {
        return new HeimdallResponse($response);
    }

    /**
     * @param ResponseInterface $generatedResponse
     * @param Response $response
     * @return Response
     */
    static function return(ResponseInterface $generatedResponse, Response $response)
    {
        $formattedResponse = $response
            ->setContentType('application/json')
            ->setStatusCode($generatedResponse->getStatusCode(), $generatedResponse->getReasonPhrase())
            ->setHeader('Location', $generatedResponse->getHeader('Location'))
            ->setBody($generatedResponse->getBody());
        echo $formattedResponse->getBody();
        return $formattedResponse;
    }

    /**
     * @param Exception $exception
     * @param Response $response
     * @return Response
     */
    static function handleException(Exception $exception, Response $response)
    {
        if($exception instanceof OAuthServerException) {
            $error = [
                'error' => $exception->getCode(),
                'messages' => $exception->getMessage(),
                'hint' => $exception->getHint()
            ];
            if($response !== null) {
                $errorResponse = $response
                    ->setContentType('application/json')
                    ->setStatusCode($exception->getHttpStatusCode(), $exception->getMessage())
                    ->setBody($error);
            }
        } else {
            $error = [
                'error'    => $exception->getCode(),
                'messages' => $exception->getMessage()
            ];
            if($response !== null) {
                $errorResponse = $response
                    ->setContentType('application/json')
                    ->setStatusCode(500, 'Internal HeimdallServer Error')
                    ->setBody($error);
            }
        }
        echo json_encode($error);
        if($response === null) exit;
        return $errorResponse;
    }

    /**
     * @param $something
     * @param bool $prettify
     * @param bool $asJSON
     */
    static function debug($something, $prettify = true, $asJSON = false)
    {
        echo ($prettify === true) ? '<pre>' : '';
        ($asJSON === true) ? print_r(json_encode($something)) : print_r($something);
        echo ($prettify === true) ? '</pre>' : '';
        exit;
    }

    /**
     * @param $clientRepository
     * @param $accessTokenRepository
     * @param $scopeRepository
     * @param $privateKey
     * @param null $responseType
     * @return HeimdallConfig
     * @throws Exception
     */
    static function withConfig(
        $clientRepository, $accessTokenRepository, $scopeRepository, $privateKey, $responseType = null
    ) {
        if(is_string($privateKey)) $privateKey = ['path' => $privateKey];
        return new HeimdallConfig(
            $clientRepository, $accessTokenRepository, $scopeRepository, $privateKey, $responseType
        );
    }

    /**
     * @param $identityRepository
     * @param array $claimSet
     * @return HeimdallOIDC
     * @throws Exception
     */
    static function withOIDC($identityRepository, array $claimSet = [])
    {
        return new HeimdallOIDC($identityRepository, $claimSet);
    }

    /**
     * @param $authCodeRepository
     * @param $refreshTokenRepository
     * @param string $accessTokenTTL
     * @return HeimdallGrantType
     */
    static function withAuthorizationGrantType($authCodeRepository, $refreshTokenRepository, $accessTokenTTL = 'PT1H')
    {
        try {
            $authCodeRepository = new $authCodeRepository;
            if(!($authCodeRepository instanceof AuthCodeRepositoryInterface)) $authCodeRepository = null;
            $refreshTokenRepository = new $refreshTokenRepository;
            if(!($refreshTokenRepository instanceof RefreshTokenRepositoryInterface)) $refreshTokenRepository = null;
            return new HeimdallGrantType(
                HeimdallGrantType::AuthorizationCode,
                new AuthCodeGrant($authCodeRepository, $refreshTokenRepository, new DateInterval('PT10M')),
                $accessTokenTTL
            );
        } catch (Exception $e) {
            throw new HeimdallConfigException(
                'Error happened initializing Heimdall grant type, please recheck your parameter.'
            );
        }
    }
}