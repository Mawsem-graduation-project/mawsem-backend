<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use function Pest\Laravel\withCookie;

class AuthController extends Controller
{
    //
    public function register(Request $request){
        $validatedData = $request->validate([
            // User Fields
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'confirm_password' => 'required|same:password',

            // Shop Fields
            'shop_name' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'address' => 'nullable|string|max:100',
        ]);

        $shop = Shop::create([
            'name' => $validatedData['shop_name'],
            'city' => $validatedData['city'],
            'address' => $validatedData['address'] ?? null,
        ]);

        $validatedData['password'] = bcrypt($validatedData['password']);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => $validatedData['password'],
            'shop_id' => $shop->id,
        ]);
        $accessToken = $user->createToken('authToken')->plainTextToken;

        return response()->json(['user' => auth()->user()->load('shop')])
            ->withCookie(cookie('accessToken', $accessToken, 60 * 24, '/', '.eny.sa', true, true));
    }

    public function login(Request $request){
        $validatedData = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!auth()->attempt($validatedData)) {
            return response()->json('Unauthorized', 401);
        }

        $accessToken = auth()->user()->createToken('authToken')->plainTextToken;

        return response()->json(['user' => auth()->user()->load('shop')])
            ->withCookie(cookie('accessToken', $accessToken, 60 * 24, '/', '.eny.sa', true, true));
    }

    public function logout(){

        if(auth()->check()){
            auth()->user()->tokens()->delete();
        }

        $cookie = cookie()->forget('accessToken');

        return response()->json(['message' => 'Successfully logged out'])
            ->withCookie($cookie);
    }
}
