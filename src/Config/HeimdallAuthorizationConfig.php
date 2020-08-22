<?php namespace Heimdall\Config;

use Exception;
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
     * @var ResponseTypeInterface $responseType
     */
    private $clientRepository, $accessTokenRepository, $scopeRepository, $privateKey, $responseType;

    /**
     * HeimdallConfig constructor.
     * @param $clientRepository
     * @param $scopeRepository
     * @param $accessTokenRepository
     * @param array $privateKey
     * @param $responseType
     * @throws Exception
     */
    function __construct(
        ClientRepositoryInterface $clientRepository,
        AccessTokenRepositoryInterface $accessTokenRepository,
        ScopeRepositoryInterface $scopeRepository,
        $privateKey,
        ResponseTypeInterface $responseType
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