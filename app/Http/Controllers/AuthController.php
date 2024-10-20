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

    public function registerUser(Request $request)
{
    $sid = env('TWILIO_SID');
    $token = env('TWILIO_AUTH');
    $twilio = new Client($sid, $token);

    $action = $request->input('action'); // Determine the action type (request_otp, validate_otp, register)
    
    switch ($action) {
        case 'request_otp':
            $validator = Validator::make($request->all(), [
                'phone' => 'required|string|unique:users,phone',
                'country_code' => 'required|string',
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError('E001', $validator);
            }

            try {
                $verification = $twilio->verify->v2->services(env('TWILIO_VERIFY_SID'))
                    ->verifications
                    ->create($request->country_code . $request->phone, "sms");

                return $this->returnData('status', $verification->status, 'OTP sent successfully');
            } catch (\Exception $e) {
                return $this->returnError('E500', 'Failed to send OTP');
            }

        case 'validate_otp':
            $validator = Validator::make($request->all(), [
                'phone' => 'required|string',
                'country_code' => 'required|string',
                'otp' => 'required|string',
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError('E001', $validator);
            }

            try {
                $verification_check = $twilio->verify->v2->services(env('TWILIO_VERIFY_SID'))
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
            } catch (\Exception $e) {
                return $this->returnError('E500', 'OTP validation failed');
            }

        case 'register':
            $validator = Validator::make($request->all(), [
                'fname' => 'required|string|max:255',
                'lname' => 'required|string|max:255',
                'phone' => 'required|string|unique:users,phone',
                'password' => 'required|string|min:6',
                'country_code' => 'required|string',
                'email' => 'nullable|email',
                'is_driver' => 'required|boolean',
                'otp' => 'required|string',
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError('E001', $validator);
            }

            // Validate OTP before proceeding with registration
            try {
                $verification_check = $twilio->verify->v2->services(env('TWILIO_VERIFY_SID'))
                    ->verificationChecks
                    ->create([
                        "to" => $request->country_code . $request->phone,
                        "code" => $request->otp,
                    ]);

                if ($verification_check->status !== 'approved') {
                    return $this->returnError('E002', 'Invalid OTP');
                }
            } catch (\Exception $e) {
                return $this->returnError('E500', 'OTP validation failed');
            }

            // Register the user
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

        default:
            return $this->returnError('E400', 'Invalid action');
    }
}



    public function loginOrValidateOtp(Request $request)
{
    $validator = Validator::make($request->all(), [
        'phone' => 'required|string|exists:users,phone',
        'country_code' => 'required|string',
        'otp' => 'nullable|string',  // OTP is optional for sending OTP flow
    ]);

    if ($validator->fails()) {
        return $this->returnValidationError('E001', $validator);
    }

    $sid = env('TWILIO_SID');
    $token = env('TWILIO_AUTH');
    $twilio = new Client($sid, $token);

    if (!$request->has('otp')) {
        // OTP sending logic
        try {
            $verification = $twilio->verify->v2->services(env('TWILIO_VERIFY_SID'))
                ->verifications
                ->create($request->country_code . $request->phone, "sms");

            return $this->returnSuccessMessage('OTP sent successfully');
        } catch (\Exception $e) {
            return $this->returnError('E500', 'Failed to send OTP');
        }
    } else {
        // OTP validation logic
        try {
            $verification_check = $twilio->verify->v2->services(env('TWILIO_VERIFY_SID'))
                ->verificationChecks
                ->create([
                    "to" => $request->country_code . $request->phone,
                    "code" => $request->otp,
                ]);

            if ($verification_check->status === 'approved') {
                $user = User::where('phone', $request->phone)->first();

                if (!$user) {
                    return $this->returnError('E003', 'User not found');
                }

                $token = JWTAuth::fromUser($user);
                return $this->returnData('token', compact('token'), 'Login successful');
            } else {
                return $this->returnError('E002', 'Invalid OTP');
            }
        } catch (\Exception $e) {
            return $this->returnError('E500', 'OTP validation failed');
        }
    }
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