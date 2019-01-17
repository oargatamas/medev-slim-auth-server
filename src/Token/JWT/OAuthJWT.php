<?php
/**
 * Created by PhpStorm.
 * User: Oarga-Tamas
 * Date: 2018. 09. 14.
 * Time: 17:04
 */

namespace MedevAuth\Token\JWT;


use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Token;
use MedevAuth\Services\Auth\OAuth\Entity\OAuthToken;

class OAuthJWT extends OAuthToken
{

    /**
     * @var \Lcobucci\JWT\Token
     */
    protected $jwt;


    /**
     * @param \Lcobucci\JWT\Token $jwt
     */
    public function setJwt(Token $jwt)
    {
        $this->jwt = $jwt;
    }


    /**
     * @return string
     */
    public function finalizeToken()
    {
        $token = (new Builder())
            ->setId($this->identifier, true)
            ->setSubject($this->user->getIdentifier())
            ->setAudience($this->client->getIdentifier())
            ->setIssuedAt(time())
            ->setNotBefore(time())
            ->setExpiration($this->expiration)
            ->set("scopes", $this->scopes) //Todo Move key to static field
            ->getToken();

        return $token->__toString();
    }


}