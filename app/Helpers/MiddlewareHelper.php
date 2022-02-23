<?php


namespace App\Helpers;


use Illuminate\Http\Request;

class MiddlewareHelper
{
    /**
     * Routes that should skip handle.
     *
     * @var array
     */
    public static $except = [
        'v1/docs',
    ];

    /**
     * Determine if the request has a URI that should pass through.
     *
     * @param Request $request
     * @return bool
     */
    public static function inExceptArray(Request $request)
    {
        foreach (self::$except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->is($except)) {
                return true;
            }
        }

        return false;
    }
}