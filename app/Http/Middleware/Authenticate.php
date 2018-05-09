<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Factory as Auth;
use App\Models\FormId;
use App\Models\Wechat;
use App\Models\ShareInfor;

class Authenticate
{
    /**
     * The authentication factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards)
    {   
        $this->authenticate($guards);
        //记录formid
        $user_id = $this->auth->id();
        $openid = Wechat::where('user_id', $user_id)->value('openid');
        if ($request->has('formId')) {
            $form_ids = $request->formId;
            if (is_array($form_ids)) {
                foreach ($form_ids as $form_id) {
                    if ($form_id == 'the formId is a mock one') {
                        continue;
                    }
                    FormId::create([
                        'user_id'=>$user_id,
                        'openid'=>$openid,
                        'form_id'=>$form_id,
                    ]);
                }
            }
        }
        //记录分享
        if ($request->has('from_openid') && $request->from_openid) {
            $count = ShareInfor::where('openid', $openid)->where('from_openid', $request->from_openid)->count();
            if (empty($count)) {
                ShareInfor::create([
                    'openid'=>$openid,
                    'from_openid'=>$request->from_openid,
                ]);
            }
        }
        return $next($request);
    }

    /**
     * Determine if the user is logged in to any of the given guards.
     *
     * @param  array  $guards
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function authenticate(array $guards)
    {
        if (empty($guards)) {
            return $this->auth->authenticate();
        }

        foreach ($guards as $guard) {
            if ($this->auth->guard($guard)->check()) {
                return $this->auth->shouldUse($guard);
            }
        }

        throw new AuthenticationException('Unauthenticated.', $guards);
    }
}
