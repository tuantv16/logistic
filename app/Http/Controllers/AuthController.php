<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only(['name', 'password']);

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized - Login failed and Unable to get tokens'], 401);
        }

        //return response()->json(['token' => $token]);
        return $this->respondWithToken($token);
    }



    protected function respondWithToken($token)
    {

        // $customTTL = 60; // Thời gian sống mới của token, tính bằng phút
        // auth('api')->factory()->setTTL($customTTL);
        // $token = auth('api')->attempt($credentials); 
        //dd(auth('api')->factory()->getTTL());
        // ttl config in config/jwt.php
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL()
        ]);
    }

}
