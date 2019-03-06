<?php
/**
 * Created by PhpStorm.
 * User: OargaTamas
 * Date: 2019. 03. 04.
 * Time: 11:20
 */

namespace MedevAuth\Services\Auth\OAuth\Entity\Persistables;


use DateTime;
use MedevAuth\Services\Auth\OAuth\Entity\Token\JWT\Signed\OAuthJWS;
use Medoo\Medoo;

class RefreshToken implements MedooPersistable
{

    /**
     * @param $storedData
     * @return OAuthJWS
     * @throws \Exception
     */
    public static function fromAssocArray($storedData)
    {
        $token = new OAuthJWS();

        $token->setIdentifier($storedData["RefreshTokenId"]);
        $token->setCreatedAt(new DateTime($storedData["RefreshTokenCreated"]));
        $token->setExpiresAt(new DateTime($storedData["RefreshTokenExpires"]));
        $token->setIsRevoked($storedData["RefreshTokenIsRevoked"]);
        $token->setClient(Client::fromAssocArray($storedData));
        $token->setUser(User::fromAssocArray($storedData));

        return $token;
    }

    /**
     * @return string
     */
    public static function getTableName()
    {
        return "OAuth_RefreshTokens";
    }

    /**
     * @return string[]
     */
    public static function getColumnNames()
    {
        return [
            "RefreshTokenId" => Medoo::raw("<rt.Id>"),
            "RefreshTokenUserId" => Medoo::raw("<rt.UserId>"),
            "RefreshTokenClientId" => Medoo::raw("<rt.ClientId>"),
            "RefreshTokenIsRevoked" => Medoo::raw("<rt.IsRevoked>"),
            "RefreshTokenExpires" => Medoo::raw("<rt.ExpiresAt>"),
            "RefreshTokenCreated" => Medoo::raw("<rt.CreatedAt>")
        ];
    }
}