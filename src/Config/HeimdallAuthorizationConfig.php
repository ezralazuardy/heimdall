<?php namespace Heimdall\Config;

use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;

/**
 * Class HeimdallConfig
 * @package Heimdall
 */
class HeimdallAuthorizationConfig
{
    /**
     * @var ClientRepositoryInterface $clientRepository
     * @var AccessTokenRepositoryInterface $accessTokenRepository
     * @var ScopeRepositoryInterface $scopeRepository
     * @var CryptKey $privateKey
     * @var ResponseTypeInterface|null $responseType
     */
    private $clientRepository, $accessTokenRepository, $scopeRepository, $privateKey, $responseType;

    /**
     * HeimdallAuthorizationConfig constructor.
     * @param ClientRepositoryInterface $clientRepository
     * @param AccessTokenRepositoryInterface $accessTokenRepository
     * @param ScopeRepositoryInterface $scopeRepository
     * @param $privateKey
     * @param ResponseTypeInterface|null $responseType
     */
    function __construct(
        ClientRepositoryInterface $clientRepository,
        AccessTokenRepositoryInterface $accessTokenRepository,
        ScopeRepositoryInterface $scopeRepository,
        $privateKey,
        $responseType
    ) {
        $this->clientRepository = $clientRepository;
        $this->accessTokenRepository = $accessTokenRepository;
        $this->scopeRepository = $scopeRepository;
        $this->responseType = $responseType;
        $this->privateKey = new CryptKey(
            empty($privateKey['path']) ? null : $privateKey['path'],
            empty($privateKey['password']) ? null : $privateKey['password'],
            empty($privateKey['permissionCheck']) ? false : $privateKey['permissionCheck']
        );
    }

    /**
     * @return ClientRepositoryInterface
     */
    function getClientRepository(): ClientRepositoryInterface
    {
        return $this->clientRepository;
    }

    /**
     * @return AccessTokenRepositoryInterface
     */
    function getAccessTokenRepository(): AccessTokenRepositoryInterface
    {
        return $this->accessTokenRepository;
    }

    /**
     * @return ScopeRepositoryInterface
     */
    function getScopeRepository(): ScopeRepositoryInterface
    {
        return $this->scopeRepository;
    }

    /**
     * @return CryptKey
     */
    function getPrivateKey(): CryptKey
    {
        return $this->privateKey;
    }

    /**
     * @return ResponseTypeInterface|null
     */
    function getResponseType(): ?ResponseTypeInterface
    {
        return $this->responseType;
    }
}