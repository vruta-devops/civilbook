<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Category;
use App\Models\SubCategory;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $emailsession = session()->get('email');
        session()->remove('email');
        $providerdata = new User;
        $categorydata = Category::where('status',1)->get();
        $subcategorydata = SubCategory::where('status', 1)->get();

        $preDiff = config('constant.PRE_DISTANCE');
        return view('auth.register', compact('providerdata' ,'categorydata', 'subcategorydata', 'preDiff', 'emailsession'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'username'  => 'required|string|max:255|unique:users',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:8',
        ]);

        Auth::login($user = User::create([
            'username' => $request->username,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone_number' => $request->phone_number,
            'user_type' => 'user',
            'display_name' => $request->first_name." ".$request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]));

        event(new Registered($user));
        $user->assignRole('user');
        return redirect(route('home'));
    }
}
