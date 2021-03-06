<?php
/**
 * Created by PhpStorm.
 * User: OargaTamas
 * Date: 2019. 02. 20.
 * Time: 11:22
 */

namespace MedevAuth\Services\Auth\OAuth\Actions\GrantType\AccessGrant;


use MedevAuth\Services\Auth\OAuth\Actions\Client\GetClientData;
use MedevAuth\Services\Auth\OAuth\Actions\Client\ValidateClient;
use MedevAuth\Services\Auth\OAuth\Actions\GrantType\OAuthRequest;
use MedevAuth\Services\Auth\OAuth\Entity\Client;
use MedevAuth\Services\Auth\OAuth\Entity\Token\OAuthToken;
use MedevAuth\Services\Auth\OAuth\OAuthService;
use MedevSlim\Core\Service\Exceptions\InternalServerException;
use MedevSlim\Core\Service\Exceptions\UnauthorizedException;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class GrantAccess
 * @inheritdoc
 * @package MedevAuth\Services\Auth\OAuth\Actions\GrantType
 */
abstract class GrantAccess extends OAuthRequest
{
    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     * @throws \Exception
     */
    public function handleRequest(Request $request, Response $response, $args)
    {
        $this->info("Validating token request");
        $this->validateAccessRequest($request, $args);

        $this->info("Access granted. Generating tokens based on the grant flow.");
        $accessToken = $this->generateAccessToken();
        $refreshToken = $this->generateRefreshToken();

        $data = [
            self::ACCESS_TOKEN => $accessToken->finalizeToken(),
            self::ACCESS_TOKEN_TYPE => "bearer",
            self::EXPIRES_IN => $accessToken->getExpiration(),
        ];

        if ($refreshToken != null) {
            $data[self::REFRESH_TOKEN] = $refreshToken->finalizeToken();
        }
        $data[OAuthService::CSRF_TOKEN] = ""; //Todo implement logic behind

        $this->invalidateAuthData();

        switch ($this->client->getTokenPlace()){
            case Client::TOKEN_AS_COOKIE : return $this->mapTokensToCookie($response,$data);
            case Client::TOKEN_AS_URL : return $this->mapTokensToUrl($response,$data);
            case Client::TOKEN_AS_BODY : return $this->mapTokensToRequestBody($response,$data);
            default : throw new InternalServerException("Token type '". $this->client->getTokenPlace() ."' not implemented.");
        }
    }


    /**
     * @param Request $request
     * @param array $args
     * @return void
     * @throws UnauthorizedException
     */
    public function validateAccessRequest(Request $request, $args = []){
        $getClientData = new GetClientData($this->service);
        $this->client = $getClientData->handleRequest(["client_id" => $request->getParam("client_id")]);

        $this->client->setSecret($request->getParam("client_secret"));

        $validateClient = new ValidateClient($this->service);
        $validateClient->handleRequest([
                "client" => $this->client,
                "grant_type" => $request->getParam("grant_type")
            ]);
    }


    /**
     * @return void
     * @throws \Exception
     */
    public function invalidateAuthData(){
        //By default we do not need to do anything after tokens generated
        //Override this method to do clean up methods like revoking authorization data
    }

    /**
     * @throws \Exception
     * @return OAuthToken
     */
    public abstract function generateAccessToken();

    /**
     * @return OAuthToken|null
     */
    public function generateRefreshToken()
    {
        //By default no refresh token is provided
        //Override and return valid token if needed in the specified grant type class
        $this->info("Skipping refresh token generation.");
        return null;
    }

    static function getParams()
    {
        return [
            "grant_type",
            "client_id"
        ];
    }


}