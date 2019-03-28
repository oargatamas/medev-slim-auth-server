<?php
/**
 * Created by PhpStorm.
 * User: OargaTamas
 * Date: 2019. 02. 13.
 * Time: 16:03
 */

namespace MedevAuth\Services\Auth\OAuth\Actions\Token\JWT\JWS;


use DateTime;
use JOSE_JWE;
use JOSE_JWT;
use MedevAuth\Services\Auth\OAuth\Actions\Client\GetClientData;
use MedevAuth\Services\Auth\OAuth\Actions\User\GetUserData;
use MedevAuth\Services\Auth\OAuth\Entity\Token\JWT\Signed\OAuthJWS;
use MedevSlim\Core\Action\Repository\APIRepositoryAction;

/**
 * Class ParseToken
 * @package MedevAuth\Services\Auth\OAuth\Actions\Token\JWT\JWS
 */
abstract class ParseToken extends APIRepositoryAction
{

    /**
     * @param $args
     * @return OAuthJWS
     * @throws \Exception
     */
    public function handleRequest($args = [])
    {
        $jwt = JOSE_JWT::decode($args["token"]);

        if($jwt instanceof JOSE_JWE){
            $tokenDecryptionKey = file_get_contents($this->config["authorization"]["token"]["private_key"]);
            $jwt = $jwt->decrypt($tokenDecryptionKey);
        }

        $client = (new GetClientData($this->service))->handleRequest(["client_id" => $jwt->claims["cli"]]);
        $user = (new GetUserData($this->service))->handleRequest(["user_id" => $jwt->claims["usr"]]);;
        $privateKey = file_get_contents($this->config["authorization"]["token"]["private_key"]);

        $token = new OAuthJWS();

        $token->setIdentifier($jwt->claims["jti"]);
        $token->setCreatedAt((new DateTime())->setTimestamp($jwt->claims["iat"]));
        $token->setExpiresAt((new DateTime())->setTimestamp($jwt->claims["exp"]));
        $token->setScopes($jwt->claims["scopes"]);
        $token->setClient($client);
        $token->setUser($user);
        $token->setPrivateKey($privateKey);

        return $this->withServerState($token);
    }

    /**
     * @param OAuthJWS $token
     * @return OAuthJWS
     */
    protected abstract function withServerState(OAuthJWS $token);
}