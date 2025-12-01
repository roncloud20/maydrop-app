<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // Create new user
    public function create (Request $request) {
        
        // Validating user entries
        $validator = Validator::make($request->all(),[
            'firstname' => 'required|string|max:30',
            'middlename' => 'nullable|string|max:30',
            'surname' => 'required|string|max:30',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|same:confirm|regex:/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/',
            'phone' => 'required|regex:/^0[789][01]\d{8}$/|unique:users,phone',
            'gender' => 'required|in:Male,Female,Others',
            'user_role' =>'required|in:admin,vendor,user',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:3072',
        ]);

        if($validator->fails()) {
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
            $user = new User;
            $user->firstname = $request->input('firstname');
            $user->middlename = $request->input('middlename');
            $user->surname = $request->input('surname');
            $user->email = $request->input('email');
            $user->password = Hash::make($request->input('password'));
            $user->phone = $request->input('phone');
            $user->gender = $request->input('gender');
            $user->user_role = $request->input('user_role');
            if($request->hasFile('profile_picture')) {
                $user->profile_picture = $request->file('profile_picture')->store('users_pictures', 'public');
            }
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
}
