<?php

namespace App\Lib;

use App\Constants\Status;
use App\Models\AdminNotification;
use App\Models\User;
use App\Models\UserLogin;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Socialite;

class SocialLogin
{
    private $provider;
    private $fromApi;

    public function __construct($provider, $fromApi = false)
    {
        $this->provider = $provider;
        $this->fromApi = $fromApi;
        $this->configuration();
    }

    public function redirectDriver()
    {
        return Socialite::driver($this->provider)->redirect();
    }

    private function configuration()
    {
        $provider = $this->provider;
        $configuration = gs('socialite_credentials')->$provider;
        $provider = $this->fromApi && $provider == 'linkedin' ? 'linkedin-openid' : $provider;

        Config::set('services.' . $provider, [
            'client_id'     => "453814658743-c1qlb59c9vv5daailvr7dm2vb4eodccg.apps.googleusercontent.com",
            'client_secret' => "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",
            'redirect'      => route('user.social.login.callback', $provider),
        ]);
    }

    public function login()
    {
        $provider = $this->provider;
        $provider = $this->fromApi && $provider == 'linkedin' ? 'linkedin-openid' : $provider;
        $driver = Socialite::driver($provider);
        
        if ($this->fromApi) {
            try {
                $user = (object)$driver->userFromToken(request()->token)->user;
            } catch (\Throwable $th) {
                throw new Exception('Something went wrong');
            }
        } else {
            $user = $driver->user();
        }

        if ($provider == 'linkedin-openid') {
            $user->id = $user->sub;
        }

        $userData = User::where('provider_id', $user->id)->first();

        if (!$userData) {
            if (!gs('registration')) {
                throw new Exception('New account registration is currently disabled');
            }
            
            $emailExists = User::where('email', @$user->email)->first();
            if ($emailExists) {
                // If email exists but not with social login, merge carts before throwing exception
                $this->mergeGuestCartWithUserCart($emailExists);
                throw new Exception('Email already exists');
            }

            $userData = $this->createUser($user, $this->provider);
        }

        // Merge guest cart before authentication
        $this->mergeGuestCartWithUserCart($userData);

        if ($this->fromApi) {
            $tokenResult = $userData->createToken('auth_token')->plainTextToken;
            $this->loginLog($userData);
            return [
                'user'         => $userData,
                'access_token' => $tokenResult,
                'token_type'   => 'Bearer',
            ];
        }

        Auth::login($userData);
        $this->loginLog($userData);

        return redirect()->intended(route('user.home'));
    }

    private function createUser($user, $provider)
    {
        $password = getTrx(8);

        $firstName = null;
        $lastName = null;

        if (@$user->first_name) {
            $firstName = $user->first_name;
        }
        if (@$user->last_name) {
            $lastName = $user->last_name;
        }

        if ((!$firstName || !$lastName) && @$user->name) {
            $firstName = preg_replace('/\W\w+\s*(\W*)$/', '$1', $user->name);
            $pieces    = explode(' ', $user->name);
            $lastName  = array_pop($pieces);
        }

        $newUser = new User();
        $newUser->provider_id = $user->id;
        $newUser->email = $user->email;
        $newUser->password = Hash::make($password);
        $newUser->firstname = $firstName;
        $newUser->lastname = $lastName;
        $newUser->status = Status::VERIFIED;
        $newUser->ev = Status::VERIFIED;
        $newUser->sv = gs('sv') ? Status::UNVERIFIED : Status::VERIFIED;
        $newUser->provider = $provider;
        $newUser->save();

        $adminNotification = new AdminNotification();
        $adminNotification->user_id = $newUser->id;
        $adminNotification->title = 'New member registered';
        $adminNotification->click_url = urlPath('admin.users.detail', $newUser->id);
        $adminNotification->save();

        return User::find($newUser->id);
    }

    private function loginLog($user)
    {
        $ip = getRealIP();
        $exist = UserLogin::where('user_ip', $ip)->first();
        $userLogin = new UserLogin();

        if ($exist) {
            $userLogin->longitude = $exist->longitude;
            $userLogin->latitude = $exist->latitude;
            $userLogin->city = $exist->city;
            $userLogin->country_code = $exist->country_code;
            $userLogin->country = $exist->country;
        } else {
            $info = json_decode(json_encode(getIpInfo()), true);
            $userLogin->longitude = @implode(',', $info['long']);
            $userLogin->latitude = @implode(',', $info['lat']);
            $userLogin->city = @implode(',', $info['city']);
            $userLogin->country_code = @implode(',', $info['code']);
            $userLogin->country = @implode(',', $info['country']);
        }

        $userAgent = osBrowser();
        $userLogin->user_id = $user->id;
        $userLogin->user_ip = $ip;
        $userLogin->browser = @$userAgent['browser'];
        $userLogin->os = @$userAgent['os_platform'];
        $userLogin->save();
    }

    /**
     * Merge guest cart items with user's session cart
     */
    protected function mergeGuestCartWithUserCart($user)
    {
        $cartManager = new \App\Lib\CartManager();
        
        // For logged out users, cart is stored in 'user_cart' session key
        $guestKey = 'user_cart';
        $userKey = 'user_cart_'.$user->id;
        
        $guestCart = session()->get($guestKey, []);
        $userCart = session()->get($userKey, []);
        
        if (!empty($guestCart)) {
            foreach ($guestCart as $productId => $item) {
                if (isset($userCart[$productId])) {
                    // If product exists in both carts, sum the quantities
                    $userCart[$productId]['quantity'] += $item['quantity'];
                } else {
                    // If product doesn't exist in user cart, add it
                    $userCart[$productId] = $item;
                }
            }
            
            // Save merged cart to user's session
            session()->put($userKey, $userCart);
            
            // Clear guest cart
            session()->forget($guestKey);
        }
        
        // If user was logged in but had items in guest cart (unlikely but possible)
        if (auth()->check() && session()->has($guestKey)) {
            $cartManager->mergeGuestCart();
        }
    }
}