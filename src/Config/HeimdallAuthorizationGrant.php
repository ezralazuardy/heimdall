<?php namespace Heimdall\Config;

use League\OAuth2\Server\Grant\AbstractGrant;

/**
 * Class HeimdallAuthorizationGrant
 * @package Heimdall\Config
 */
class HeimdallAuthorizationGrant
{
    /**
     * Supported HeimdallAuthorizationGrant
     */
    const ClientCredentials = 0;
    const PasswordCredentials = 1;
    const AuthorizationCode = 2;
    const Implicit = 3;
    const RefreshToken = 4;

    /**
     * @var int $grant,
     * @var AbstractGrant $grant
     * @var string $accessTokenTTL
     */
    private $grantCode, $grant, $accessTokenTTL;

    /**
     * HeimdallAuthorizationGrant constructor.
     * @param int $grantCode
     * @param AbstractGrant $grant
     * @param string $accessTokenTTL
     */
    function __construct(int $grantCode, AbstractGrant $grant, string $accessTokenTTL) {
        $this->grantCode = $grantCode;
        $this->grant = $grant;
        $this->accessTokenTTL = $accessTokenTTL;
    }

    /**
     * @return int
     */
    function getCode(): int
    {
        return $this->grantCode;
    }

    /**
     * @return AbstractGrant
     */
    function getGrantType(): AbstractGrant
    {
        return $this->grant;
    }

    /**
     * @return string
     */
    function getAccessTokenTTL(): string
    {
        return $this->accessTokenTTL;
    }
}