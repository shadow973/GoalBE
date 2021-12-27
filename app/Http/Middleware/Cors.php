<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $origin = isset($_SERVER['HTTP_ORIGIN'])? $_SERVER['HTTP_ORIGIN'] : '';

        $allowedOrigins = [
            'http://localhost',
            'http://localhost:4200',
            'http://localhost:4300',
            'http://localhost:4000',
            'http://localhost:4201',
            'http://localhost:8080',
            'http://goal.loc',
            'http://new.goal.ge',
            'https://new.goal.ge',
            'http://react.goal.ge',
            'https://react.goal.ge',
            'http://v2.goal.ge',
            'https://v2 .goal.ge',
            'http://192.168.1.2:4200',
            'http://192.168.43.46:4200',
            'https://new.goal.ge',
            'http://10.20.0.234:4200',
            'http://192.140.2.61:4200',
            'http://192.140.2.60:4200',
            'http://192.140.1.218:4200',
            'http://192.168.0.117:4200',
            'http://10.10.24.76:4200',
            'http://admin.goal.ge',
            'https://test.goal.ge',
            'https://admin.goal.ge',
            'http://admin.goal.loc',
            'https://dev.goal.ge',
            'http://dev.goal.ge',
            'https://testnew.goal.ge',
            'http://testnew.goal.ge',
            'http://football45.com',
            'http://admin.football45.com',
            'https://football45.com',
            'https://admin.football45.com',
            'http://192.168.88.38:4200',
            'http://192.168.1.64:4200',
            'http://192.168.88.43:4200',
        ];

       // if(in_array($origin, $allowedOrigins)){
            return $next($request)
                ->header('Access-Control-Allow-Origin', $origin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers',
                    'Content-Type, Authorization, X-XSRF-TOKEN, Cache-Control, Pragma, Expires, Sec-Fetch-Mode');
       // }

        return $next($request);
    }

}
