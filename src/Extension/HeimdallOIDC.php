<?php namespace Heimdall\Extension;

use Exception;
use Heimdall\Exception\HeimdallConfigException;
use Heimdall\Interfaces\IdentityRepositoryInterface;
use OpenIDConnectServer\ClaimExtractor;
use OpenIDConnectServer\IdTokenResponse;

/**
 * Class HeimdallOIDC
 * @package Heimdall\Extension
 */
class HeimdallOIDC
{
    /**
     * @var IdTokenResponse
     */
    private $responseType;

    /**
     * HeimdallOIDC constructor.
     * @param IdentityRepositoryInterface $identityRepository
     * @param array $claimSet
     */
    function __construct(IdentityRepositoryInterface $identityRepository, array $claimSet)
    {
        try {
            $identityRepository = new $identityRepository();
            $this->responseType = new IdTokenResponse($identityRepository, new ClaimExtractor($claimSet));
        } catch (Exception $exception) {
            throw new HeimdallConfigException(
                'Error happened when enabling Heimdall Authorization Server OIDC, please recheck your parameter.'
            );
        }
    }

    /**
     * @return IdTokenResponse
     */
    function getResponseType(): IdTokenResponse
    {
        return $this->responseType;
    }
}