<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Validator, DB, Hash, Mail;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Foundation\Auth\RegistersUsers;
use App\User_verification;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    // protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('guest');
    }

    public function register(Request $request)
    {
        $credentials = $request->only('name', 'email', 'password');

        $rules = [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users'
        ];
        $validator = Validator::make($credentials, $rules);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->messages()]);
        }

        $name = $request->name;
        $email = $request->email;
        $password = $request->password;

        $user = User::create(['name' => $name, 'email' => $email, 'password' => Hash::make($password)]);

        // $token = JWTAuth::fromUser($user);
        // return response()->json(compact('user', 'token'), 201);

        $verification_code = str_random(30); //Generate verification code

        // $verification = new User_verification();
        // $verification->token = $verification_code;
        // $user->verification()->save($verification);

        // DB::table('user_verifications')->insert(['user_id' => $user->id, 'token' => $verification_code]);
        // dd($verification_code);
        DB::table('user_verifications')->insert(['user_id' => $user->id, 'token' => $verification_code]);

        $subject = "Please verify your email address.";
        // Mail::send(
        //     'email.verify',
        //     ['name' => $name, 'verification_code' => $verification_code],
        //     function ($mail) use ($email, $name, $subject) {
        //         $mail->from(getenv('FROM_EMAIL_ADDRESS'), "Errnd");
        //         $mail->to($email, $name);
        //         $mail->subject($subject);
        //     }
        // );

        Mail::send('email.verify', array('verification_code' => $verification_code, 'name' => $user->name), function ($message) use ($user) {
            $message->to($user->email, $user->name)->subject('Active your account !');
        });

        return response()->json(['success' => true, 'message' => 'Thanks for signing up! Please check your email to complete your registration.']);
        // return response()->json(['success' => true, 'user' => $user, 'verification_code' => $verification_code]);
    }


    public function verifyUser($verification_code)
    {
        $check = DB::table('user_verifications')->where('token', $verification_code)->first();
        if (!is_null($check)) {
            $user = User::find($check->user_id);
            if ($user->is_verified == 1) {
                return response()->json([
                    'success' => true,
                    'message' => 'Account already verified..'
                ]);
            }
            $user->update(['is_verified' => 1]);
            DB::table('user_verifications')->where('token', $verification_code)->delete();
            return response()->json([
                'success' => true,
                'message' => 'You have successfully verified your email address.'
            ]);
        }
        return response()->json(['success' => false, 'error' => "Verification code is invalid."]);
    }
}
