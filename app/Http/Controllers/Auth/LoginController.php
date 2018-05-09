<?php

namespace App\Http\Controllers\Auth;

use Socialite;
use WechatService;
use App\Models\User;
use App\Models\Wechat;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

use App\Repositories\Eloquent\SmsRepository as Sms;
use Log;
class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    protected $sms;
    protected $app;
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * login account.
     *
     * @var string
     */
    protected $account= 'mobile';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Sms $sms)
    {
        $this->sms = $sms;
        $this->middleware('guest')->except('logout', 'wechatUpdate');
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return $this->account;
    }


    /**
     * 微信登录接口
     */
    public function loginWechat(Request $request)
    {
        $failure = null;
        $user = null;
        $token = null; 

        $session =  $this->getWechatSession($request->code);
        if($session){
            if (!isset($session['session_key'])) {
                Log::debug($session);
                return $this->failure('wechat login failed');
            }
            session(['session_key'=>$session['session_key']]);
            if($id = Wechat::where('openid', $session['openid'])->value('id')){
                $wechat = Wechat::find($id);
            }else{
                $wechat = new Wechat;
                $wechat->openid = $session['openid'];
                // if (array_key_exists('unionid', $session)) {
                //     $wechat->unionid = $session['unionid'];
                // }
                $wechat->unionid = isset($session['unionid'])?:null;
                $wechat->save();
            }

            if($wechat->user_id){
                $user = User::findOrFail($wechat->user_id);
                $registered = true;
                $user = $this->guard()->loginUsingId($wechat->user_id, true);
                $token = $user->createToken($user->mobile)->accessToken; 
            }

            return $this->success('login_success', compact('user', 'token'));
        }else{
            return $this->failure('wechat login failed');
        }
    }

    public function getWechatSession($code)
    {
        $session = null;

        try{
            if(config('app.debug') && $code == 'the code is a mock one'){
                //simulate
                $session = [
                    'openid' => 'oyBj70MRExrrzYH7K8F_VE75XeoE',
                    'session_key' => 'oyBj70MRExrrzYH7K8F_VE75XeoE',
                    'unionid' => 'oVMWoswKQA2ToVHyLzcc6t19N4zE',
                ];
            }else{
                // $session = $this->app->auth->session($code);
                $session = WechatService::app()->auth->session($code);
            }
        }catch(\Exeception $e){
            $failure = $e->getMessage;
        }

        return $session;
    
    }

    /*
     * 微信资料更新
     */
    public function wechatUpdate($request,$session_key, $id, $user_info)
    {
        $mp = WechatService::app();
        if(config('app.debug') && !$request->iv && !$request->encryptedData){
            $user_info = (object)$request->info;
        }else{
            $user_info = $mp->encryptor->decryptData($session_key, $request->iv, $request->encryptedData);
        }

        $user = User::with('wechat')->find($id);
        if($user->wechat){
            $user->wechat->nickname = $user_info['nickName'];
            $user->wechat->gender = $user_info['gender'];
            $user->wechat->city = $user_info['city'];
            $user->wechat->province = $user_info['province'];
            $user->wechat->country = isset($user_info['country'])?$user_info['country']:'中国';
            $user->wechat->avatar = $user_info['avatarUrl'];
            $user->wechat->unionid = $user_info['unionId'];
            $user->wechat->save();
        }

        return;
    }

    /*
     * 微信资料更新
     */
    public function wechatRegister(Request $request)
    {

        if($res = $this->sms->check($request->mobile, $request->code)){
            return $this->failure($res);
        }

        if($id = User::where('mobile', $request->mobile)->value('id')){
            $user = User::findorFail($id);
        }else{
            $user =  new User;
            $user->password = '';
        }

        if($request->mobile != $user->mobile && $request->input('mobile')){
            $user->mobile = $request->input('mobile');
            if(!$user->email){
                $user->email = $request->input('mobile').'@ufutx.com';
            }
        }

        if($request->name != $user->name && $request->input('name')){
            $user->name = $request->input('name');
        }

        $user->save();

        $session =  $this->getWechatSession($request->wechat_code);
        $session_key = '';
        if($session){
            Wechat::where('openid', $session['openid'])->update(['user_id'=>$user->id]);
            $session_key = $session['session_key'];
        }

        $user_info = $request->info;
        if($user_info){
            $this->wechatUpdate($request, $session_key, $user->id, $user_info);
        }

        $user = $this->guard()->loginUsingId($user->id, true);
        $token = $user->createToken($user->mobile)->accessToken; 

        return $this->success('register user info', compact('user', 'token'));
    }


    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        //本地登录
        if ($this->attemptLogin($request)) {


            //需要返回access_token
            if($request->expectsJson()){
                $user = $this->guard()->user();
                $user->access_token = $user->createToken($user->mobile)->accessToken; 
                return $this->success('login_success', $user);
            }else{
                return $this->sendLoginResponse($request);
            }
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    public function logout(Request $request)
    {
        //logout api token
        if ($request->expectsJson()) {
            $token= $this->guard()->user()->token();
            if(!$token){ //mobile api
                // 获取 refresh_token
                $refresh_token = \DB::table('oauth_refresh_tokens')
                    ->where('access_token_id', $token->id)
                    ->first();
             
                // 删除已经 revoke 的令牌
                event(new AccessTokenCreated($token->id, $token->user_id, $token->client_id));
                if($refresh_token){
                    event(new RefreshTokenCreated($refresh_token->id, $token->id));
                }
                // revoke 用户注销前的令牌
                if($token){
                    $token->revoked=true;
                    $token->save();
                }
             
                // revoke 用户注销前的refresh_token
                \DB::table('oauth_refresh_tokens')
                    ->where('access_token_id', $token->id)
                    ->update(['revoked' => true]);
            }
            return $this->success('logout_success');

        }else{ //logout web session
            $this->guard()->logout();
            $request->session()->invalidate();
            return redirect('/');
        }
    }
    


    /*
     * 社交登录:友福同享Ufutx
     */
    function snsLoginUfutx()
    {
        return Socialite::with('ufutx')->redirect();
    }

    /*
     * 社交登录:github
     */
    function snsLoginGithub()
    {
        return Socialite::with('github')->redirect();
    }


    /*
     * 社交登录回调:友福同享Ufutx
     */
    function snsUfutxCallback()
    {
        $user = Socialite::with('ufutx')->user();
        return $this->success('user', $user);
    }

    /*
     * 社交登录回调: Github
     */
    function snsGithubCallback()
    {
        $user = Socialite::with('github')/*->stateless()*/->user();
        return $this->success('user', $user);
    }



}
