<?php
/**
 * Created by PhpStorm.
 * User: OargaTamas
 * Date: 2019. 02. 13.
 * Time: 16:03
 */

namespace MedevAuth\Services\Auth\OAuth\Actions\Token;


use DateTime;
use JOSE_JWE;
use JOSE_JWT;
use MedevAuth\Services\Auth\OAuth\Entity\Client;
use MedevAuth\Services\Auth\OAuth\Entity\Token\JWT\Signed\OAuthJWS;
use MedevAuth\Services\Auth\OAuth\Entity\User;
use MedevAuth\Utils\CryptUtils;
use MedevSlim\Core\Action\Repository\APIRepositoryAction;

/**
 * Class ParseToken
 * @package MedevAuth\Services\Auth\OAuth\Actions\Token\
 */
abstract class ParseToken extends APIRepositoryAction
{

    /**
     * @param $args
     * @return OAuthJWS
     * @throws \JOSE_Exception
     * @throws \Exception
     */
    public function handleRequest($args = [])
    {
        $jwt = JOSE_JWT::decode($args["token"]);

        $privateKey = CryptUtils::getRSAKeyFromConfig($this->config["authorization"]["token"]["private_key"]);
        $publicKey = CryptUtils::getRSAKeyFromConfig($this->config["authorization"]["token"]["public_key"]);

        if($jwt instanceof JOSE_JWE){
            $decryptedJwt = $jwt->decrypt($privateKey)->plain_text;
            $jwt = JOSE_JWT::decode($decryptedJwt);
        }

        $jwtClient = $jwt->claims["client"];
        $jwtUser = $jwt->claims["user"];

        $client = new Client();
        $client->setIdentifier($jwtClient->id);
        $client->setName($jwtClient->name);
        $client->setRedirectUri($jwtClient->redirectUri);

        $user = new User();
        $user->setIdentifier($jwtUser->id);
        $user->setUsername($jwtUser->username);
        $user->setEmail($jwtUser->email);

        $token = new OAuthJWS($jwt,$publicKey,$privateKey);

        $token->setIdentifier($jwt->claims["jti"]);
        $token->setCreatedAt((new DateTime())->setTimestamp($jwt->claims["iat"]));
        $token->setExpiresAt((new DateTime())->setTimestamp($jwt->claims["exp"]));
        $token->setScopes($jwt->claims["scopes"]);
        $token->setClient($client);
        $token->setUser($user);

        return $this->withServerState($token);
    }

    /**
     * @param OAuthJWS $token
     * @return OAuthJWS
     */
    protected abstract function withServerState(OAuthJWS $token);
}