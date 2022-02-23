<?php


namespace App\Http\Middleware;

use App\Helpers\MiddlewareHelper;
use App\Models\User;
use Illuminate\Support\Str;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;


class JwtAuthentication extends BaseMiddleware
{
    /**
     * Authenticate request with a JWT.
     *
     * @param \Illuminate\Http\Request $request
     * @param mixed $next
     * @return mixed
     */
    public function handle($request, $next)
    {
        // check user authed or API Key
        if (!MiddlewareHelper::inExceptArray($request)) {
            $token = $this->getToken($request);

            try {
                if ($this->compareTokens($token) || $this->compareWithUsers($token)) {
                    return $next($request);
                } else {
                    throw new UnauthorizedHttpException('JWTAuth', 'Unable to authenticate with invalid token.');
                }
            } catch (\Exception $exception) {
                throw new UnauthorizedHttpException('JWTAuth', $exception->getMessage(), $exception);
            }
        }

        return $next($request);
    }

    /**
     * Get the JWT from the request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string
     * @throws \Exception
     *
     */
    protected function getToken(\Illuminate\Http\Request $request)
    {
        try {
            $this->validateAuthorizationHeader($request);

            $token = $this->parseAuthorizationHeader($request);
        } catch (\Exception $exception) {
            if (!$token = $request->query('token', false)) {
                throw new UnauthorizedHttpException('JWTAuth', 'Authentication token not present.');
            }
        }

        return $token;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    public function validateAuthorizationHeader(\Illuminate\Http\Request $request)
    {
        if (Str::startsWith(strtolower($request->headers->get('authorization')), $this->getAuthorizationMethod())) {
            return true;
        }

        throw new BadRequestHttpException;
    }


    /**
     * Parse JWT from the authorization header.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string
     */
    protected function parseAuthorizationHeader(\Illuminate\Http\Request $request)
    {
        return trim(str_ireplace($this->getAuthorizationMethod(), '', $request->header('authorization')));
    }

    /**
     * @param $token
     * @return boolean
     */
    protected function compareTokens($token)
    {
        $internalToken = env('JWT_SECRET', null);

        if (!$internalToken) {
            throw new InternalErrorException('Internal security measures not configured', 500);
        }

        return (mb_strtolower((string)$token) === mb_strtolower((string)$internalToken));
    }

    /**
     * @param $token
     * @return bool
     */
    protected function compareWithUsers($token)
    {
        return User::where('auth_token', $token)->exists();
    }

    /**
     * Get the providers authorization method.
     *
     * @return string
     */
    public function getAuthorizationMethod()
    {
        return 'bearer';
    }
}
