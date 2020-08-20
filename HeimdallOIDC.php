<?php namespace Heimdall;

use Heimdall\Exception\HeimdallConfigException;
use Exception;
use OpenIDConnectServer\ClaimExtractor;
use OpenIDConnectServer\IdTokenResponse;

/**
 * Class HeimdallOIDC
 * @package Heimdall
 */
class HeimdallOIDC
{
    /**
     * @var IdTokenResponse
     */
    private $responseType;

    /**
     * HeimdallOIDC constructor.
     * @param $identityRepository
     * @param array $claimSet
     * @throws Exception
     */
    function __construct($identityRepository, array $claimSet)
    {
        try {
            $identityRepository = new $identityRepository();
            $this->responseType = new IdTokenResponse($identityRepository, new ClaimExtractor($claimSet));
        } catch (Exception $exception) {
            throw new HeimdallConfigException(
                'Error happened when enabling Heimdall OIDC, please recheck your parameter.'
            );
        }
    }

    /**
     * @return IdTokenResponse
     */
    function getResponseType()
    {
        return $this->responseType;
    }
}