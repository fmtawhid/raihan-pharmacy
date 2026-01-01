<?php

namespace App\Http\Controllers\User;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Constants\Status;
use Illuminate\Support\Facades\Http;

class AuthorizationController extends Controller
{
    protected function checkCodeValidity($user,$addMin = 2)
    {
        if (!$user->ver_code_send_at){
            return false;
        }
        if ($user->ver_code_send_at->addMinutes($addMin) < Carbon::now()) {
            return false;
        }
        return true;
    }

    
  
  public function authorizeForm()
{
    $user = auth()->user();
  
    $user->ver_code = verificationCode(6);
    $user->ver_code_send_at = Carbon::now();
    $user->save();

    if (!$user->status) {
        $pageTitle = 'Banned';
        $type = 'ban';
    } elseif (!$user->ev) {
        $type = 'email';
        $pageTitle = 'Verify Email';
        $notifyTemplate = 'EVER_CODE';
      
        if (!$this->checkCodeValidity($user) && ($type != 'ban')) {
            notify($user, $notifyTemplate, [
                'code' => $user->ver_code
            ], [$type]);
        }
    } elseif (!$user->sv) {
        $type = 'sms';
        $pageTitle = 'Verify Mobile Number';
        $notifyTemplate = 'SVER_CODE';
      
        $response = Http::get('https://api.sms.net.bd/sendsms', [
            'api_key' => "BH60yg3uPUAuvm5if3f5xlu3161b0VGkMfolN19D",
            'msg' => "Thanks for joining Westernchoices! Enter this code to verify your account: $user->ver_code. Expires in 2 Minutes.",
            'to' => $user->dial_code.$user->mobile
        ]);
    } else {
        return to_route('user.home');
    }

    return view('Template::user.auth.authorization.'.$type, compact('user', 'pageTitle'));
}
  

    public function sendVerifyCode($type)
    {
        $user = auth()->user();

        if ($this->checkCodeValidity($user)) {
            $targetTime = $user->ver_code_send_at->addMinutes(2)->timestamp;
            $delay = $targetTime - time();
            throw ValidationException::withMessages(['resend' => 'Please try after ' . $delay . ' seconds']);
        }

        $user->ver_code = verificationCode(6);
        $user->ver_code_send_at = Carbon::now();
        $user->save();

        if ($type == 'email') {
            $type = 'email';
            $notifyTemplate = 'EVER_CODE';
            notify($user, $notifyTemplate, [
            'code' => $user->ver_code
        	],[$type]);
        } else {
            $type = 'sms';
            $notifyTemplate = 'SVER_CODE';
          
         	$response = Http::get('https://api.sms.net.bd/sendsms', [
        	'api_key' => "BH60yg3uPUAuvm5if3f5xlu3161b0VGkMfolN19D",
        	'msg' => "Thanks for joining Westernchoices! Enter this code to verify your account: $user->ver_code. Expires in 2 Minutes.",
        	'to' => $user->dial_code.$user->mobile
    ]);
        }

      

        $notify[] = ['success', 'Verification code sent successfully'];
        return back()->withNotify($notify);
    }

    public function emailVerification(Request $request)
    {
        $request->validate([
            'code'=>'required'
        ]);

        $user = auth()->user();

        if ($user->ver_code == $request->code) {
            $user->ev = Status::VERIFIED;
            $user->ver_code = null;
            $user->ver_code_send_at = null;
            $user->save();

           return redirect()->intended(route('user.home'));
        }
        throw ValidationException::withMessages(['code' => 'Verification code didn\'t match!']);
    }

    public function mobileVerification(Request $request)
    {
        $request->validate([
            'code' => 'required',
        ]);


        $user = auth()->user();
        if ($user->ver_code == $request->code) {
            $user->sv = Status::VERIFIED;
            $user->ver_code = null;
            $user->ver_code_send_at = null;
            $user->save();
           return redirect()->intended(route('user.home'));
        }
        throw ValidationException::withMessages(['code' => 'Verification code didn\'t match!']);
    }
}
