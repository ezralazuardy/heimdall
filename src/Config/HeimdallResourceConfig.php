<?php namespace Heimdall\Config;

use League\OAuth2\Server\AuthorizationValidators\AuthorizationValidatorInterface;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

class HeimdallResourceConfig
{
    /**
     * @var AccessTokenRepositoryInterface $accessTokenRepository
     * @var CryptKey $publicKey
     * @var AuthorizationValidatorInterface|null $authorizationValidator
     */
    private $accessTokenRepository, $publicKey, $authorizationValidator;

    /**
     * HeimdallResourceConfig constructor.
     * @param AccessTokenRepositoryInterface $accessTokenRepository
     * @param array $publicKey
     * @param AuthorizationValidatorInterface|null $authorizationValidator
     */
    function __construct(
        AccessTokenRepositoryInterface $accessTokenRepository,
        array $publicKey,
        $authorizationValidator
    ) {
        $this->accessTokenRepository = $accessTokenRepository;
        $this->publicKey = new CryptKey(
            empty($publicKey['path']) ? null : $publicKey['path'],
            empty($publicKey['password']) ? null : $publicKey['password'],
            empty($publicKey['permissionCheck']) ? false : $publicKey['permissionCheck']
        );
        $this->authorizationValidator = $authorizationValidator;
    }

    /**
     * @return AccessTokenRepositoryInterface
     */
    function getAccessTokenRepository(): AccessTokenRepositoryInterface
    {
        return $this->accessTokenRepository;
    }

    /**
     * @return CryptKey
     */
    function getPublicKey(): CryptKey
    {
        return $this->publicKey;
    }

    /**
     * @return AuthorizationValidatorInterface|null
     */
    function getAuthorizationValidator(): ?AuthorizationValidatorInterface
    {
        return $this->authorizationValidator;
    }
}