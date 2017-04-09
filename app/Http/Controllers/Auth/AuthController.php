<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Validator;
use Auth;
use Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public $redirectTo = '/dashboard';
    public $maxLoginAttempts = 5;
    public $loginPath = '/login';
    
    // following construtor is commented as we have define guest middleware
    // public function __construct()
    // {
    //     // $this->middleware('guest', ['except' => 'getLogout']);
    // }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'username' => 'required|max:20|unique:users',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'username' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }
    public function login(){
        // return 'Auth Login Panel';
        return view('auth.login')
                    ->with('title', 'Login');
    }

    public function doLogin(Request $request)
    {
        $rules = array
        (
                    'email'    => 'required',
                    'password' => 'required'
        );
        $allInput = $request->all();
        $validation = Validator::make($allInput, $rules);

        // dd($allInput);

        // user lock after multiple wrong attempts // this throttle lock ---Added By Masiur
        $throttles = $this->isUsingThrottlesLoginsTrait();

        if ($throttles && $this->hasTooManyLoginAttempts($request)) {
            return $this->sendLockoutResponse($request);
        }


        if ($validation->fails())
        {

            return redirect()->route('login')
                        ->withInput()
                        ->withErrors($validation);
        } else
        {

            $credentials = array
            (
                        'email'    => $allInput['email'],
                        'password' => $allInput['password']
            );

            if (Auth::attempt($credentials))
            {   
                if ($throttles) {
                    $this->clearLoginAttempts($request);
                }
                return redirect()->intended('admin/dashboard');
            } else if(Auth::attempt([ 'username' => $allInput['email'], 'password' => $allInput['password'] ])) {
                if ($throttles) {
                    $this->clearLoginAttempts($request);
                }
                return redirect()->intended('admin/dashboard');
            } else
            {   
                if ($throttles) {
                    $this->incrementLoginAttempts($request);
                }
                return redirect()->route('login')
                            ->withInput()
                            ->withErrors('Error in Email Address or Password.');
            }
        }
        return 'Do Login Executes';
    }

    public function logout(){
        Auth::logout();
        return redirect()->route('login')
                    ->with('success',"You are successfully logged out.");
        // return 'Logout Panel';
    }

    public function dashboard(){
        return view('dashboard')
                    ->with('title','Dashboard')->with('user', Auth::user());
        // return 'Dashboard';
    }

    public function changePassword(){
        return view('auth.changePassword')
                    ->with('title',"Change Password")->with('user', Auth::user());
        // return 'Change Password';
    }

    public function doChangePassword(Request $request){
        $rules =[
            'password'              => 'required|confirmed',
            'password_confirmation' => 'required'
        ];
        $data = $request->all();

        $validation = Validator::make($data,$rules);

        if($validation->fails()){
            return redirect()->back()->withErrors($validation)->withInput();
        }else{
            $user = Auth::user();
            $user->password = Hash::make($data['password']);

            if($user->save()){
                Auth::logout();
                return redirect()->route('login')
                            ->with('success','Your password changed successfully.');
            }else{
                return redirect()->route('dashboard')
                            ->with('error',"Something went wrong.Please Try again.");
            }
        }
         // return 'Do Change Password';

    }
}
