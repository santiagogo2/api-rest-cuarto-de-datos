<?php

namespace App\Http\Middleware;

use Closure;

class ApiAuthMiddleware
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
        // Comporbar si el usuario está identificado
        $token = $request->header('Authorization');

        if( $token ){
            $jwtAuth = new \JwtAuth();
            $checkToken = $jwtAuth->checkToken($token);
            
            if($checkToken){
                return $next($request);
            } else{
                $data = array(
                    'status' => 'error',
                    'code' => 401,
                    'message' => 'El usuario no está identificado.'
                );
            }
        }
        else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'No ha ingresado la cabecera de autorización.'
            );
        }
        return response()->json($data, $data['code']);
    }
}
