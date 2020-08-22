<?php namespace Heimdall\Config;

use League\OAuth2\Server\Grant\AbstractGrant;

/**
 * Class HeimdallGrantType
 * @package Heimdall
 */
class HeimdallAuthorizationGrantType
{
    /**
     * Supported HeimdallAuthorizationGrantType
     */
    const ClientCredentials = 0;
    const PasswordCredentials = 1;
    const AuthorizationCode = 2;
    const Implicit = 3;
    const RefreshToken = 4;

    /**
     * @var int $grantType,
     * @var AbstractGrant $grantType
     * @var string $accessTokenTTL
     */
    private $grantTypeCode, $grantType, $accessTokenTTL;

    /**
     * HeimdallAuthorizationGrantType constructor.
     * @param int $grantTypeCode
     * @param AbstractGrant $grantType
     * @param string $accessTokenTTL
     */
    function __construct(int $grantTypeCode, AbstractGrant $grantType, string $accessTokenTTL) {
        $this->grantTypeCode = $grantTypeCode;
        $this->grantType = $grantType;
        $this->accessTokenTTL = $accessTokenTTL;
    }

    /**
     * @return int
     */
    function getCode(): int
    {
        return $this->grantTypeCode;
    }

    /**
     * @return AbstractGrant
     */
    function getGrantType(): AbstractGrant
    {
        return $this->grantType;
    }

    /**
     * @return string
     */
    function getAccessTokenTTL(): string
    {
        return $this->accessTokenTTL;
    }
}