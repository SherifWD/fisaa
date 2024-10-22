<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Traits\HelpersTrait;
use App\Traits\backendTraits;
use Twilio\Rest\Client;
class AuthController extends Controller
{
    use HelpersTrait;
    use backendTraits;

    public function loginValidateOtp(Request $request)
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

        try {
            // $verification_check = $twilio->verify->v2->services("VA84af6f06b5cfa0d64e9bfdf64a5ecd7e")
            //     ->verificationChecks
            //     ->create([
            //         "to" => $request->country_code . $request->phone,
            //         "code" => $request->otp,
            //     ]);
                if($request->otp == User::where('phone',$request->phone)->where('country_code',$request->country_code)->first()->otp){
                    $verification_check['status'] = 'approved';
                }
// dd($verification_check);
            if ($verification_check['status'] === 'approved') {
                $user = User::where('phone', $request->phone)
                    ->where('country_code', $request->country_code)
                    ->first();

                if (!$user) {
                    // Redirect to registration if user does not exist
                    return $this->returnError('E003', 'User not found. Please register.');
                }

                $token = JWTAuth::fromUser($user);
                // Check if user is missing a name and needs to update profile
                if (empty($user->name)) {
                    return $this->returnData('update_profile', [
                        'message' => 'Please update your profile with your name.',
                        'required_fields' => ['name'],
                        'token' => $token
                    ], 'Incomplete profile');
                }

                // Login success, generate token
                return $this->returnData('token', compact('token'), 'Login successful');
            } else {
                return $this->returnError('E002', 'Invalid OTP');
            }
        } catch (\Exception $e) {
            return $this->returnError('E500', 'OTP validation failed');
        }
    }

    public function updateProfile(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email'
        ]);

        if ($validator->fails()) {
            return $this->returnValidationError('E001', $validator);
        }

        // Update profile
        $user->update([
            'name' => $request->name,
            'email' => $request->email ?? $user->email,
            'phone' => $request->phone ?? $user->phone,
        ]);
        if(isset($request->image)){
        $user->image = $this->upploadImage($request->file('image'), 'profile_image') ?? $user->image;
        $user->save();
        }
        $data['usr'] = $user;
        return $this->returnData('data',$data,'Profile updated successfully');
    }

    public function register(Request $request,$flag)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            // 'phone' => 'required|string|unique:users,phone',
            // 'password' => 'required|string|min:6',
            // 'country_code' => 'required',
            'email' => 'nullable|email',
            // 'is_driver' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->returnValidationError('E001', $validator);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email ?? null,
            // 'phone' => $request->phone,
            // 'country_code' => $request->country_code,
            // 'password' => Hash::make($request->password),
            // 'is_driver' => $request->is_driver,
        ]);

        $token = JWTAuth::fromUser($user);

        return $this->returnData('token', compact('token'), 'User registered successfully');
    }



    public function requestPhoneNumber(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|unique:users,phone',
            'country_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->returnValidationError('E001', $validator);
        }

        // $sid = env('TWILIO_SID');
        // $token = env('TWILIO_AUTH');
        // $twilio = new Client($sid, $token);

        // $verification = $twilio->verify->v2->services("VA84af6f06b5cfa0d64e9bfdf64a5ecd7e")
        //     ->verifications
        //     ->create($request->country_code . $request->phone, "sms");
        $verification['otp'] = rand([00000,99999]);
        $verification['status'] = 'Success';
        $usr = User::where('phone',$request->phone)->where('country_code',$request->country_code)->first();
        $usr->otp = $verification['otp'];
        $usr->save();
        $verification['usr'] = $usr;
        return $this->returnData('status', $verification, 'OTP sent successfully');
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

        // $sid = env('TWILIO_SID');
        // $token = env('TWILIO_AUTH');
        // $twilio = new Client($sid, $token);

        // $verification_check = $twilio->verify->v2->services("VA84af6f06b5cfa0d64e9bfdf64a5ecd7e")
        //     ->verificationChecks
        //     ->create([
        //         "to" => $request->country_code . $request->phone,
        //         "code" => $request->otp,
        //     ]);
        if($request->otp == User::where('phone',$request->phone)->where('country_code',$request->country_code)->first()->otp)
        // if ($verification_check->status === 'approved') {
            return $this->returnSuccessMessage('OTP validated successfully');
        // }
        else {
            return $this->returnError('E002', 'Invalid OTP');
        }
    }


    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'country_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->returnValidationError('E001', $validator);
        }

        // $sid = env('TWILIO_SID');
        // $token = env('TWILIO_AUTH');
        // $twilio = new Client($sid, $token);
        try {
    // Validate request data
    $request->validate([
        'phone' => 'required|numeric|digits_between:7,15',
        'country_code' => 'required',
    ]);

    $verification['otp'] = rand(10000, 99999); // Ensure OTP has 5 digits
    $verification['status'] = 'Success';

    // Check if user exists
    $user = User::where('phone', $request->phone)
                ->where('country_code', $request->country_code)
                ->first();

    if (!$user) {
        // Create new user if not found
        $user = new User();
        $user->phone = $request->phone;
        $user->country_code = $request->country_code;
        $user->otp = $verification['otp'];
        $user->save();
    } else {
        // Update OTP if user exists
        $user->otp = $verification['otp'];
        $user->save();
    }

    $verification['usr'] = $user;

    // Uncomment the following block to integrate with Twilio
    /*
    $twilio->verify->v2->services("VA84af6f06b5cfa0d64e9bfdf64a5ecd7e")
        ->verifications
        ->create($request->country_code . $request->phone, "sms");
    */

    return $this->returnData('status', $verification, 'OTP sent successfully');
} catch (\Illuminate\Validation\ValidationException $e) {
    // Handle validation errors
    return $this->returnError('E400', $e->errors());
} catch (\Exception $e) {
    // Handle other errors
    return $this->returnError('E500', 'Failed to send OTP');
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