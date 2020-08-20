<?php namespace Heimdall;

use Heimdall\Exception\HeimdallConfigException;
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
class HeimdallConfig
{
    /**
     * @var ClientRepositoryInterface $clientRepository
     * @var AccessTokenRepositoryInterface $accessTokenRepository
     * @var ScopeRepositoryInterface $scopeRepository
     * @var CryptKey $privateKey
     * @var ResponseTypeInterface $responseType
     */
    private $clientRepository, $accessTokenRepository, $scopeRepository, $privateKey, $responseType = null;

    /**
     * HeimdallConfig constructor.
     * @param $clientRepository
     * @param $scopeRepository
     * @param $accessTokenRepository
     * @param array $privateKey
     * @param $responseType
     * @throws Exception
     */
    function __construct($clientRepository, $accessTokenRepository, $scopeRepository, array $privateKey, $responseType) {
        try {
            $clientRepository = new $clientRepository;
            if($clientRepository instanceof ClientRepositoryInterface) $this->clientRepository = $clientRepository;

            $accessTokenRepository = new $accessTokenRepository;
            if($accessTokenRepository instanceof AccessTokenRepositoryInterface) $this->accessTokenRepository = $accessTokenRepository;

            $scopeRepository = new $scopeRepository;
            if($scopeRepository instanceof ScopeRepositoryInterface) $this->scopeRepository = $scopeRepository;

            if($responseType !== null) $responseType = new $responseType;
            if($responseType instanceof ResponseTypeInterface) $this->responseType = $responseType;

            $this->privateKey = new CryptKey(
                empty($privateKey['path']) ? null : $privateKey['path'],
                empty($privateKey['password']) ? null : $privateKey['password'],
                empty($privateKey['permissionCheck']) ? false : $privateKey['permissionCheck']
            );
        } catch(Exception $exception) {
            throw new HeimdallConfigException(
                'Error happened when initializing Heimdall configuration, please recheck your parameter.'
            );
        }
    }

    /**
     * @return ClientRepositoryInterface
     */
    function getClientRepository()
    {
        return $this->clientRepository;
    }

    /**
     * @return AccessTokenRepositoryInterface
     */
    function getAccessTokenRepository()
    {
        return $this->accessTokenRepository;
    }

    /**
     * @return ScopeRepositoryInterface
     */
    function getScopeRepository()
    {
        return $this->scopeRepository;
    }

    /**
     * @return CryptKey
     */
    function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * @return ResponseTypeInterface|null
     */
    function getResponseType()
    {
        return $this->responseType;
    }
}