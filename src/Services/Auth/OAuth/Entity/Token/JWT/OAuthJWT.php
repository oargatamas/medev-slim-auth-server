<?php
/**
 * Created by PhpStorm.
 * User: Oarga-Tamas
 * Date: 2018. 09. 14.
 * Time: 17:04
 */

namespace MedevAuth\Services\Auth\OAuth\Entity\Token\JWT;


use JOSE_JWT;
use MedevAuth\Services\Auth\OAuth\Entity\Client;
use MedevAuth\Services\Auth\OAuth\Entity\Token\OAuthToken;
use MedevAuth\Services\Auth\OAuth\Entity\User;

class OAuthJWT extends OAuthToken
{

    /**
     * @var JOSE_JWT
     */
    protected $jwt;


    /**
     * OAuthJWT constructor.
     * @param JOSE_JWT $jwt
     */
    public function __construct(JOSE_JWT $jwt)
    {
        $this->jwt = $jwt;
    }

    public function setIdentifier($identifier)
    {
        $this->jwt->claims["jti"] = $identifier;
        parent::setIdentifier($identifier);
    }

    public function setScopes($scopes)
    {
        $this->jwt->claims["scopes"] = array_values($scopes);
        parent::setScopes($scopes);
    }

    public function addScope($scope)
    {
        $this->jwt->claims["scopes"][] = $scope;
        parent::addScope($scope);
    }


    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->jwt->claims["iat"] = $createdAt->getTimestamp();
        parent::setCreatedAt($createdAt);
    }

    public function setExpiresAt(\DateTime $expiresAt)
    {
        $this->jwt->claims["exp"] = $expiresAt->getTimestamp();
        parent::setExpiresAt($expiresAt);
    }

    public function setClient(Client $client)
    {
        $this->jwt->claims["cli"] = $client->getIdentifier();
        parent::setClient($client);
    }

    public function setUser(User $user)
    {
        $this->jwt->claims["usr"] = $user->getIdentifier();
        parent::setUser($user);
    }

    /**
     * @return string
     */
    public function finalizeToken()
    {
        return $this->jwt->toString();
    }


}