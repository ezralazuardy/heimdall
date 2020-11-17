<?php namespace Heimdall;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use DateInterval;
use Exception;
use Heimdall\Config\HeimdallAuthorizationConfig;
use Heimdall\Config\HeimdallAuthorizationGrant;
use Heimdall\Config\HeimdallResourceConfig;
use Heimdall\Exception\HeimdallConfigException;
use Heimdall\Exception\HeimdallServerException;
use Heimdall\Extension\HeimdallOIDC;
use Heimdall\Http\HeimdallRequest;
use Heimdall\Http\HeimdallResponse;
use Heimdall\interfaces\IdentityRepositoryInterface;
use Heimdall\Server\HeimdallAuthorizationServer;
use Heimdall\Server\HeimdallResourceServer;
use League\OAuth2\Server\AuthorizationValidators\AuthorizationValidatorInterface;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\ImplicitGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Heimdall
 * @package Heimdall
 */
abstract class Heimdall
{
    /**
     * @param HeimdallAuthorizationConfig $config
     * @param HeimdallAuthorizationGrant $grant
     * @param HeimdallOIDC|null $oidc
     * @return HeimdallAuthorizationServer
     */
    static function initializeAuthorizationServer(
        HeimdallAuthorizationConfig $config,
        HeimdallAuthorizationGrant $grant,
        HeimdallOIDC $oidc = null
    ): HeimdallAuthorizationServer
    {
        switch ($grant->getCode()) {
            case HeimdallAuthorizationGrant::AuthorizationCode:
                return new HeimdallAuthorizationServer($config, $grant, $oidc);
            default:
                throw new HeimdallConfigException(
                    'Unknown Heimdall grant type, please recheck your parameter.'
                );
        }
    }

    /**
     * @param HeimdallResourceConfig $config
     * @return HeimdallResourceServer
     */
    static function initializeResourceServer(HeimdallResourceConfig $config): HeimdallResourceServer
    {
        return new HeimdallResourceServer($config);
    }

    /**
     * @param IncomingRequest $request
     * @return HeimdallRequest
     */
    static function handleRequest(IncomingRequest $request): HeimdallRequest
    {
        return (new HeimdallRequest($request))->withParsedBody($request->getPost());
    }

    /**
     * @param Response $response
     * @return HeimdallResponse
     */
    static function handleResponse(Response $response): HeimdallResponse
    {
        return new HeimdallResponse($response);
    }

    /**
     * @param ResponseInterface $generatedResponse
     * @param Response $response
     * @return Response
     */
    static function return(ResponseInterface $generatedResponse, Response $response): Response
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
     */
    static function handleException(Exception $exception)
    {
        header('Content-Type: application/json');
        if ($exception instanceof HeimdallServerException) {
            header($_SERVER['SERVER_PROTOCOL'] . ' ' . $exception->getHttpStatusCode() . ' ' . $exception->getMessage());
            $error = [
                'code' => $exception->getCode(),
                'messages' => $exception->getMessage()
            ];
            if (!empty($exception->getHint())) $error['hint'] = $exception->getHint();
        } else {
            header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal HeimdallServer Error');
            $error = [
                'code' => $exception->getCode(),
                'messages' => $exception->getMessage()
            ];
        }
        echo json_encode($error);
        exit;
    }

    /**
     * @param $something
     * @param bool $prettify
     * @param bool $asJSON
     * @return void
     */
    static function debug($something, $prettify = true, $asJSON = false)
    {
        echo ($prettify === true) ? '<pre>' : '';
        ($asJSON === true) ? print_r(json_encode($something)) : print_r($something);
        echo ($prettify === true) ? '</pre>' : '';
        exit;
    }

    /**
     * @param ClientRepositoryInterface $clientRepository
     * @param AccessTokenRepositoryInterface $accessTokenRepository
     * @param ScopeRepositoryInterface $scopeRepository
     * @param $privateKey
     * @param ResponseTypeInterface|null $responseType
     * @return HeimdallAuthorizationConfig
     */
    static function withAuthorizationConfig(
        ClientRepositoryInterface $clientRepository,
        AccessTokenRepositoryInterface $accessTokenRepository,
        ScopeRepositoryInterface $scopeRepository,
        $privateKey,
        ResponseTypeInterface $responseType = null
    ): HeimdallAuthorizationConfig
    {
        if (is_string($privateKey)) $privateKey = ['path' => $privateKey];
        return new HeimdallAuthorizationConfig(
            $clientRepository, $accessTokenRepository, $scopeRepository, $privateKey, $responseType
        );
    }

    /**
     * @param AccessTokenRepositoryInterface $accessTokenRepository
     * @param $publicKey
     * @param AuthorizationValidatorInterface|null $authorizationValidator
     * @return HeimdallResourceConfig
     */
    static function withResourceConfig(
        AccessTokenRepositoryInterface $accessTokenRepository,
        $publicKey,
        AuthorizationValidatorInterface $authorizationValidator = null
    ): HeimdallResourceConfig
    {
        if (is_string($publicKey)) $publicKey = ['path' => $publicKey];
        return new HeimdallResourceConfig($accessTokenRepository, $publicKey, $authorizationValidator);
    }

    /**
     * @param IdentityRepositoryInterface $identityRepository
     * @param array $claimSet
     * @return HeimdallOIDC
     */
    static function withOIDC(
        IdentityRepositoryInterface $identityRepository, array $claimSet = []
    ): HeimdallOIDC
    {
        return new HeimdallOIDC($identityRepository, $claimSet);
    }

    /**
     * @param string $accessTokenTTL
     * @return HeimdallAuthorizationGrant
     */
    static function withClientCredentialsGrant(
        string $accessTokenTTL = 'PT1H'
    ): HeimdallAuthorizationGrant
    {
        try {
            return new HeimdallAuthorizationGrant(
                HeimdallAuthorizationGrant::ClientCredentials,
                new ClientCredentialsGrant(),
                $accessTokenTTL
            );
        } catch (Exception $exception) {
            throw new HeimdallConfigException(
                'Error happened initializing Heimdall grant type, please recheck your parameter.',
                $exception->getCode()
            );
        }
    }

    /**
     * @param UserRepositoryInterface $userRepository
     * @param RefreshTokenRepositoryInterface $refreshTokenRepository
     * @param string $refreshTokenTTL
     * @param string $accessTokenTTL
     * @return HeimdallAuthorizationGrant
     */
    static function withPasswordGrant(
        UserRepositoryInterface $userRepository,
        RefreshTokenRepositoryInterface $refreshTokenRepository,
        string $refreshTokenTTL = 'P1M',
        string $accessTokenTTL = 'PT1H'
    ): HeimdallAuthorizationGrant
    {
        try {
            $passwordGrant = new PasswordGrant($userRepository, $refreshTokenRepository);
            $passwordGrant->setRefreshTokenTTL(new DateInterval($refreshTokenTTL));
            return new HeimdallAuthorizationGrant(
                HeimdallAuthorizationGrant::PasswordCredentials,
                $passwordGrant,
                $accessTokenTTL
            );
        } catch (Exception $exception) {
            throw new HeimdallConfigException(
                'Error happened initializing Heimdall grant type, please recheck your parameter.',
                $exception->getCode()
            );
        }
    }

    /**
     * @param AuthCodeRepositoryInterface $authCodeRepository
     * @param RefreshTokenRepositoryInterface $refreshTokenRepository
     * @param string $authCodeTTL
     * @param string $refreshTokenTTL
     * @param string $accessTokenTTL
     * @return HeimdallAuthorizationGrant
     */
    static function withAuthorizationCodeGrant(
        AuthCodeRepositoryInterface $authCodeRepository,
        RefreshTokenRepositoryInterface $refreshTokenRepository,
        string $authCodeTTL = 'PT10M',
        string $refreshTokenTTL = 'P1M',
        string $accessTokenTTL = 'PT1H'
    ): HeimdallAuthorizationGrant
    {
        try {
            $authCodeGrant = new AuthCodeGrant($authCodeRepository, $refreshTokenRepository, new DateInterval($authCodeTTL));
            $authCodeGrant->setRefreshTokenTTL(new DateInterval($refreshTokenTTL));
            return new HeimdallAuthorizationGrant(
                HeimdallAuthorizationGrant::AuthorizationCode,
                $authCodeGrant,
                $accessTokenTTL
            );
        } catch (Exception $exception) {
            throw new HeimdallConfigException(
                'Error happened initializing Heimdall grant type, please recheck your parameter.',
                $exception->getCode()
            );
        }
    }

    /**
     * @param string $accessTokenTTL
     * @return HeimdallAuthorizationGrant
     */
    static function withImplicitGrant(
        string $accessTokenTTL = 'PT1H'
    ): HeimdallAuthorizationGrant
    {
        try {
            return new HeimdallAuthorizationGrant(
                HeimdallAuthorizationGrant::Implicit,
                new ImplicitGrant(new DateInterval($accessTokenTTL)),
                $accessTokenTTL
            );
        } catch (Exception $exception) {
            throw new HeimdallConfigException(
                'Error happened initializing Heimdall grant type, please recheck your parameter.',
                $exception->getCode()
            );
        }
    }

    /**
     * @param RefreshTokenRepositoryInterface $refreshTokenRepository
     * @param string $refreshTokenTTL
     * @param string $accessTokenTTL
     * @return HeimdallAuthorizationGrant
     */
    static function withRefreshTokenGrant(
        RefreshTokenRepositoryInterface $refreshTokenRepository,
        string $refreshTokenTTL = 'P1M',
        string $accessTokenTTL = 'PT1H'
    ): HeimdallAuthorizationGrant
    {
        try {
            $refreshTokenGrant = new RefreshTokenGrant($refreshTokenRepository);
            $refreshTokenGrant->setRefreshTokenTTL(new DateInterval($refreshTokenTTL));
            return new HeimdallAuthorizationGrant(
                HeimdallAuthorizationGrant::RefreshToken,
                $refreshTokenGrant,
                $accessTokenTTL
            );
        } catch (Exception $exception) {
            throw new HeimdallConfigException(
                'Error happened initializing Heimdall grant type, please recheck your parameter.',
                $exception->getCode()
            );
        }
    }
}