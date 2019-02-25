<?php
/**
 * Created by PhpStorm.
 * User: OargaTamas
 * Date: 2019. 02. 20.
 * Time: 14:42
 */

namespace MedevAuth\Services\Auth\OAuth\Actions\GrantType\RefreshToken;


use MedevAuth\Services\Auth\OAuth\Actions\GrantType\GrantAccess;
use MedevAuth\Services\Auth\OAuth\Actions\Token\JWT\JWS\AccessToken\GenerateAccessToken;
use MedevAuth\Services\Auth\OAuth\Actions\Token\JWT\JWS\RefreshToken\ParseRefreshToken;
use MedevAuth\Services\Auth\OAuth\Actions\Token\JWT\JWS\RefreshToken\RevokeRefreshToken;
use MedevAuth\Services\Auth\OAuth\Actions\Token\JWT\JWS\ValidateToken;
use MedevAuth\Services\Auth\OAuth\Entity\Token\OAuthToken;
use MedevSlim\Core\Service\Exceptions\UnauthorizedException;
use Slim\Http\Request;

/**
 * Class RequestAccessToken
 * @package MedevAuth\Services\Auth\OAuth\Actions\GrantType\RefreshToken
 */
class RequestAccessToken extends GrantAccess
{

    /**
     * @var OAuthToken
     */
    private $refreshToken;

    /**
     * @param Request $request
     * @param array $args
     * @return void
     * @throws UnauthorizedException
     * @throws \Exception
     */
    public function validateAccessRequest(Request $request, $args = [])
    {
        $parseAction = new ParseRefreshToken($this->service);
        $parsedRefreshToken = $parseAction->handleRequest(["token" => $request->getParam("refresh_token")]);

        $this->refreshToken = $parsedRefreshToken;

        $validation = new ValidateToken($this->service);
        $validation->handleRequest(["token" => $parsedRefreshToken]);
    }

    /**
     * @throws \Exception
     * @return OAuthToken
     */
    public function generateAccessToken()
    {
        $tokenInfo = [
            OAuthToken::USER => $this->user,
            OAuthToken::CLIENT => $this->client,
            OAuthToken::SCOPES => $this->scopes
        ];

        $action = new GenerateAccessToken($this->service);
        $accessToken = $action->handleRequest($tokenInfo);

        return $accessToken;
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function invalidateAuthData()
    {
        $this->info("Revoking refresh token to avoid multiple usage.");
        $action = new RevokeRefreshToken($this->service);
        $action->handleRequest(["token_id" => $this->refreshToken->getIdentifier()]);
    }


    /**
     * @return array|string[]
     */
    static function getParams()
    {
      return array_merge(parent::getParams(),[
              "refresh_token"
          ]);
    }


}