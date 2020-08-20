<?php namespace Heimdall;

/**
 * Class HeimdallGrantType
 * @package Heimdall
 */
class HeimdallGrantType
{
    /**
     * HeimdallGrantType Support Type
     */
    const ClientCredentials = 0;
    const PasswordCredentials = 1;
    const AuthorizationCode = 2;
    const Implicit = 3;
    const RefreshToken = 4;

    /**
     * @var int $grantType,
     * @var mixed $grantType
     */
    private $grantTypeCode, $grantType, $accessTokenTTL;

    /**
     * HeimdallGrantType constructor.
     * @param int $grantTypeCode
     * @param $grantType
     * @param string $accessTokenTTL
     */
    function __construct(int $grantTypeCode, $grantType, string $accessTokenTTL) {
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
     * @return mixed
     */
    function getGrantType()
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