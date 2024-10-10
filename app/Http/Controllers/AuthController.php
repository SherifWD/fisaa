<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Traits\HelpersTrait;
use Twilio\Rest\Client;
class AuthController extends Controller
{
    use HelpersTrait;

    public function requestPhoneNumber(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|unique:users,phone',
            'country_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->returnValidationError('E001', $validator);
        }

        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH');
        $twilio = new Client($sid, $token);

        $verification = $twilio->verify->v2->services("VA84af6f06b5cfa0d64e9bfdf64a5ecd7e")
            ->verifications
            ->create($request->country_code . $request->phone, "sms");

        return $this->returnData('status', $verification->status, 'OTP sent successfully');
    }
    public function validateOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'country_code' => 'required|string',
            'otp' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->returnValidationError('E001', $validator);
        }

        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH');
        $twilio = new Client($sid, $token);

        $verification_check = $twilio->verify->v2->services("VA84af6f06b5cfa0d64e9bfdf64a5ecd7e")
            ->verificationChecks
            ->create([
                "to" => $request->country_code . $request->phone,
                "code" => $request->otp,
            ]);

        if ($verification_check->status === 'approved') {
            return $this->returnSuccessMessage('OTP validated successfully');
        } else {
            return $this->returnError('E002', 'Invalid OTP');
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:6',
            'country_code' => 'required',
            'email' => 'email',
            'is_driver' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->returnValidationError('E001', $validator);
        }

        // You can also add an additional check here to ensure the OTP is validated before registration.

        $user = User::create([
            'fname' => $request->fname,
            'lname' => $request->lname,
            'email' => $request->email ?? null,
            'phone' => $request->phone,
            'country_code' => $request->country_code,
            'password' => Hash::make($request->password),
            'is_driver' => $request->is_driver,
        ]);

        $token = JWTAuth::fromUser($user);

        return $this->returnData('token', compact('token'), 'User registered successfully');
    }


    public function login(Request $request)
    {
        $credentials = $request->only('phone', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return $this->returnError('E002', 'Invalid credentials');
            }
        } catch (JWTException $e) {
            return $this->returnError('E500', 'Could not create token');
        }

        return $this->returnData('token', compact('token'), 'Login successful');
    }

    public function getAuthenticatedUser()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return $this->returnError('E003', 'User not found');
            }
        } catch (JWTException $e) {
            return $this->returnError('E500', 'Token error');
        }

        return $this->returnData('user', $user, 'Authenticated user retrieved successfully');
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return $this->returnSuccessMessage('User successfully logged out');
    }
}