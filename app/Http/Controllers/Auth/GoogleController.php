<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function handleCallBack(Request $request)
    {
        try {
            $state = $request->get('state');

            $userType = "";

            if (!empty($state)) {
                $userType = json_decode($state, true);
                $userType = $userType['user_type'];
            }

            if (empty($userType)) {
                return redirect()->route('login')
                    ->withErrors(['email' => 'Something went wrong. Please try again']);
            }

            $user = Socialite::driver('google')->stateless()->user();

            $findUser = User::where('user_type', $userType)
                ->where(function ($q) use ($user) {
                    return $q->where('uid', $user->id)->orWhere('email', $user->email);
                })
                ->first();

            if ($findUser) {
                if ($findUser->uid != $user->id) {
                    $findUser->uid = $user->id;
                    $findUser->save();
                }

                Auth::login($findUser);
                return redirect('/home');

            } else {
                 // Authentication failed
                 session()->put('email', $user->email);
                return redirect()->route('register')
                    ->withErrors(['email' => 'The provided credentials do not match our records.']);

            }

        } catch (\Exception $e) {
            echo $e->getMessage() . " " . $e->getFile() . " " . $e->getLine();
            die;
            return redirect('auth/google');

        }
    }

    public function login(Request $request)
    {
        $userType = getUserType($request->user_type);

        $parameters = json_encode([
            'user_type' => $userType,
        ]);

        return Socialite::driver('google')->with(['state' => $parameters])->redirect();
    }
}
