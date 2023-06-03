<?php
namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Exception;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ServerRequestInterface;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Spatie\Permission\Models\Role;

class AuthController extends AccessTokenController
{
    public function login(ServerRequestInterface $request)
    {
        try {
            $tokenRequest = $request->withParsedBody(array_merge($request->getParsedBody(), [
                'grant_type' => 'password',
            ]));
            $response = $this->server->respondToAccessTokenRequest($tokenRequest, new Response());
            $responseData = json_decode($response->getBody(), true);
            return self::createSuccessResponse($responseData);
        } catch (OAuthServerException $e) {
            return self::createErrorResponse($e);
        } catch (Exception $e) {
            return response()->json(['message' => $e -> getMessage()], 401);
        }
    }

    public function refresh(ServerRequestInterface $request)
    {
        try {
            $refreshToken = $request->getCookieParams()['refresh_token'];
            $tokenRequest = $request->withParsedBody(array_merge($request->getParsedBody(), [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
            ]));
            $response = $this->server->respondToAccessTokenRequest($tokenRequest, new Response());
            $responseData = json_decode($response->getBody(), true);
            return self::createSuccessResponse($responseData);
        } catch (OAuthServerException $e) {
            return self::createErrorResponse($e);
        } catch (Exception $e) {
            return response()->json(['message' => 'No existe token en la petición'], 401);
        }
    }

    static function createSuccessResponse($responseData)
    {
        $cookieExpiration = 60 * 24;
        $accessToken = cookie('access_token', $responseData['access_token'], $cookieExpiration);
        $refreshToken = cookie('refresh_token', $responseData['refresh_token'], $cookieExpiration);
        $expiresAt = cookie('expires_at', now()->addSecond($responseData['expires_in']), $cookieExpiration);

        return response()->json(['status' => 'success'])
            ->withCookie($accessToken)
            ->withCookie($refreshToken)
            ->withCookie($expiresAt);
    }

    static function createErrorResponse(OAuthServerException $e)
    {
        $errorCode = $e->getErrorType();
        $errorMessage = $e->getMessage();
        $hint = $e->getHint();
        $errorResponse = [
            'error' => $errorCode,
            'error_description' => $errorMessage,
            'message' => $errorMessage
        ];
        if ($hint) {
            $errorResponse['hint'] = $hint;
        }
        if ($e->hasRedirect()) {
            $errorResponse['redirect_uri'] = $e->getRedirectUri();
        }

        return response()->json($errorResponse, $e->getHttpStatusCode());
    }

    public function logout()
    {
        $accessToken = Cookie::forget('access_token');
        $refreshToken = Cookie::forget('refresh_token');
        $expiresAt = Cookie::forget('expires_at');
        return response()->json(['status' => 'success'])
            ->withCookie($expiresAt)
            ->withCookie($accessToken)
            ->withCookie($refreshToken);
    }

    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        $role = Role::findOrFail($request->input('role_id'));
        $user->assignRole($role);
        return response($user, 200);
    }
}
