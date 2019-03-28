<?php
/**
 * Created by PhpStorm.
 * User: OargaTamas
 * Date: 2019. 03. 28.
 * Time: 18:09
 */

namespace MedevAuth\Services\Auth\OAuth\Entity\Token\JWT\Encrypted;


use JOSE_JWT;
use MedevAuth\Services\Auth\OAuth\Entity\Token\JWT\OAuthJWT;

class OAuthJWE extends OAuthJWT
{
    /**
     * @var string
     */
    private $publicKey;

    /**
     * @param string $publicKey
     */
    public function setPublicKey($publicKey)
    {
        $this->publicKey = $publicKey;
    }

    /**
     * @return string
     */
    public function finalizeToken()
    {
        $token = new JOSE_JWT($this->mapPropsToClaims());

        return $token->encrypt($this->publicKey,"RS256")->toString();
    }
}