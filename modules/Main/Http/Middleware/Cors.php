<?php

namespace Modules\Main\Http\Middleware;
use Closure;

class Cors
{
    /**
     * Handle an incoming request.
     * 关闭跨域设置
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $headers = [
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Origin, Content-Type, Cookie, Accept, multipart/form-data, application/json, Authorization, X-CS-Api-Token',
            'Access-Control-Allow-Methods' => 'GET, POST, PATCH, PUT, OPTIONS',
        ];

        foreach ($headers as $k => $v) {
            if (method_exists($response, 'header')) {
                $response->header($k, $v);
                continue;
            }
            if (isset($response->headers) && method_exists($response->headers, 'set')) {
                $response->headers->set($k, $v);
            }
        }

        return $response;
    }
}
