<?php

namespace Modules\Auth\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Auth\Http\Requests\CheckUsernameRequest;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Http\Requests\RegisterRequest;
use Modules\Auth\Services\AuthService;

class AuthController extends Controller
{

    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function checkUsername(CheckUsernameRequest $request)
    {
        // Step 1: Validate incoming request
        $validated = $request->validated();

        // Step 2: Check if username exist
        $checkUsernameDetails = $this->authService->checkUsername($validated['username']);
        if(!$checkUsernameDetails[0]){
             return response(['status' => false, 'message' => $checkUsernameDetails[1], 'data' => false], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'Username is available',
        ]);
    }


    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request)
    {
         // Step 1: Validate incoming request
        $validated = $request->validated();

         // Step 2: Register
        $isRegisterCompliant = $this->authService->register($validated);
        if(!$isRegisterCompliant[0]){
             return response(['status' => false, 'message' => $isRegisterCompliant[1], 'data' => false], 422);
        }
        $user = $isRegisterCompliant[1];
        $token = $isRegisterCompliant[2];
    
         return response()->json([
            'status' => true,
            'message' => 'Registration successful',
            'user' => $user,
            'token' => $token,
        ], 201);
    }



    /**
     * Login user and issue token.
     */
    public function login(LoginRequest $request)
    {
        // Step 1: Validate incoming request
        $validated = $request->validated();

        // Step 2: Attempt Login
        $isLoginCompliant = $this->authService->attemptLogin($validated);
        if(!$isLoginCompliant[0]){
             return response(['status' => false, 'message' => $isLoginCompliant[1], 'data' => false], 422);
        }
        $user = $isLoginCompliant[1];
        $token = $isLoginCompliant[2];
    
        // Step 3: Return Response
        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token
        ]);
    }


    public function me(Request $request)
    {
        return response()->json([
            'status' => true,
            'data' => $request->user(),
        ]);
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['status' => true, 'message' => 'Successfully logged out']);
    }
}
