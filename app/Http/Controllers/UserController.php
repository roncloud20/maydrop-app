<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // Create new user
    public function create(Request $request)
    {

        // Validating user entries
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:30',
            'middlename' => 'nullable|string|max:30',
            'surname' => 'required|string|max:30',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|same:confirm|regex:/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/',
            'phone' => 'required|regex:/^0[789][01]\d{8}$/|unique:users,phone',
            'gender' => 'required|in:Male,Female,Others',
            'user_role' => 'required|in:admin,vendor,user',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:3072',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Registration Failed',
            ], 400);
        }

        try {
            // // Using Query Builder to populate our database
            // DB::table('users')->insert([
            //     'firstname' => $request->input('firstname'),
            //     'middlename' => $request->input('middlename'),
            //     'surname' => $request->input('surname'),
            //     'email' => $request->input('email'),
            //     'password' => Hash::make($request->input('password')),
            //     'phone' => $request->input('phone'),
            //     'gender' => $request->input('gender'),
            //     'user_role' => $request->input('user_role'),
            //     'profile_picture' => $request->file('profile_picture')->store('users_pictures', 'public'),
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ]);

            // Using Eloquent ORM
            $code = rand(100000, 999999);
            $user = new User;
            $user->firstname = $request->input('firstname');
            $user->middlename = $request->input('middlename');
            $user->surname = $request->input('surname');
            $user->email = $request->input('email');
            $user->password = Hash::make($request->input('password'));
            $user->phone = $request->input('phone');
            $user->gender = $request->input('gender');
            $user->user_role = $request->input('user_role');
            if ($request->hasFile('profile_picture')) {
                $user->profile_picture = $request->file('profile_picture')->store('users_pictures', 'public');
            }
            $user->verification_code = $code;

            Mail::send('emails.user-verification', [
                'fullname' => $user->firstname . " " . $user->surname,
                'code' => $code,
                'url_link' => env('FRONTEND_URL') . "/verify?email=$user->email&code=$code",
            ], function ($message) use ($user) {
                $message->to($user->email)->subject('Email Verification');
            });

            $user->save();

            return response()->json([
                'user' => $user,
                'message' => 'Registration was successful',
            ], 201);
        } catch (\Exception $error) {
            return response()->json([
                'message' => $error->getMessage(),
            ], 500);
        }
    }

    // Verification of new user
    public function verify(Request $request)
    {
        // Validate users entries
        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:users,email',
            'code' => 'required'
        ]);

        // Return an output base on user entries if errors occurs
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Validation Failed',
            ], 400);
        }

        try {
            $user = User::where('email', $request->input('email'))->first();
            if ($user->verification_code === $request->input('code')) {
                // $user->update([
                //     'verification_code' => null,
                //     'email_verified_at' => now(),
                // ]);
                DB::table('users')->where('email', $request->input('email'))->update([
                    'verification_code' => null,
                    'email_verified_at' => now(),
                    'updated_at' => now(),
                ]);

                Mail::send('emails.email-verified', [
                    'fullname' => $user->firstname . " " . $user->surname,
                    'url_link' => env('FRONTEND_URL') . '/login',
                    'role' => $user->user_role,
                ], function ($message) use ($user) {
                    $message->to($user->email)->subject('Email Verified Successfully');
                });
                return response()->json([
                    'user' => $user,
                    'message' => 'Verification Successful',
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Verification Failed',
                ], 406);
            }
        } catch (\Exception $errors) {
            return response()->json([
                'errors' => $errors,
            ], 500);
        }
    }

    // Login Method
    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid Credentials 1 ',
                'errors' => $validator->errors(),
            ], 401);
        }

        try {
            $user = User::where('email', $request->email)->first();
            if ($user && Hash::check($request->password, $user->password)) {
                $token = $user->createToken('maydrop-token')->plainTextToken;

                return response()->json([
                    'user' => $user,
                    'token' => $token,
                    'message' => 'Login Successfully'
                ], 200);
            }

            return response()->json([
                'message' => 'Either Your Email Or Password is invalid',
                'errors' => [
                    'email' => 'Invalid',
                    'password' => 'Invalid',
                ],
            ], 401);
        } catch (\Exception $errors) {
            return response()->json([
                'errors' => $errors,
                'message' => 'Server Error',
            ], 500);
        }

        // // return "Hello";
        // $credentials = $request->validate([
        //     'email' => 'required|email',
        //     'password' => 'required',
        // ]);

        // if(Auth::attempt($credentials)) {
        //     $user = Auth::user();
        //     $token = $user->createToken('maydrop-token')->plainTextToken;

        //     return response()->json([
        //         'user' => $user,
        //         'token' => $token,
        //     ], 200);
        // }

        // return response()->json([
        //     'message' => 'Invalid Credentials',
        // ], 401);
    }

    // Fetch all users records
    public function index() {
        try {

            $users = User::get();
            return $users;
        } catch(\Exception $error) {
            return response()->json([
                'errors' => $error,
            ],500);
        }
    }

    // Forget Password
    public function forgetPassword(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ],400);
        }

        $user = User::where('email', $request->email)->first();
        $code = rand(100000, 999999);
        if($user) {
            DB::table('users')->where('email', $request->email)->update([
                'verification_code' => $code, 
                'updated_at' => now(),
            ]);


        }
    }
}
