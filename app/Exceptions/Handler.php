<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use App\Utils\Messenger;
use WechatService;
use App\Models\FormId;
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
        if(method_exists($exception, 'getStatusCode')){
            $status = $exception->getStatusCode(); 
        }elseif($exception instanceof AuthenticationException){
            $status = 403;
            //403不报警
            return;
        }else{
            $status = get_class($exception);
        }

        $error = $exception->getMessage();

        if($status == 404 && empty($error)){
            //404不报警
            return;
        }
        
        $route = Request()->getMethod().':'.Request()->getRequestUri();
        if(\Route::getFacadeRoot()->current()){
            $action = $status.':'.\Route::getCurrentRoute()->getActionName();
        }else{
            $action = $status.':非法路由访问错误';
        }

        if (method_exists($exception, 'getPrevious')) {
            $err = $exception->getPrevious();
            if (!empty($err)) {
                $error = $err->getMessage();
            }
        }
        $files = explode('/', $exception->getFile());
        $file = $files[count($files)-1];
        $message = '服务器异常，位置：'.$route.'，控制器：'.$file.' Line:'.$exception->getLine().'，报错内容：'.$error;
        Messenger::sendSMS('18682191714', $message);
        Messenger::sendSMS('15872844805', $message);
        $openids = ['oyBj70MRExrrzYH7K8F_VE75XeoE', 'oyBj70CVe76vOsKP3zhq71a4Ukp0'];
        foreach ($openids as $openid) {
            $now_time = time();
            $end_time = $now_time - 7 * 24 * 60 * 60;
            $time = date('Y-m-d H:i:s', $end_time);
            $form_id = FormId::where('openid', $openid)->where('status', 0)->where('form_id', '<>','the formId is a mock one')->where('created_at', '>', $time)->orderBy('id', 'asc')->first();
            if (!empty($form_id)) {
                $form_id->status = 1;
                $form_id->save();
                $param = [
                    'message'=>$message,
                    'error_time'=>date('y-m-d H:i:s', time()),
                    'openid'=>$openid,
                    'form_id'=>$form_id->form_id,
                ];
                WechatService::errorMessage($param);
            }

        }
        
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        return parent::render($request, $exception);
    }

    public function prepareJsonResponse($request, Exception $e)
    {
        $data['status'] = $this->isHttpException($e) ? $e->getStatusCode() : 500;
        if(config('app.debug')){
            $data['file'] = $e->getFile();
            $data['line'] = $e->getLine();
            $data['traces'] = $e->getTrace();
        }

        $headers = $this->isHttpException($e) ? $e->getHeaders() : [];

        return new JsonResponse(
            [ 'code'=>1,
                'data'=>$data,
                'message'=>$data['status'].':'.$e->getMessage()
            ], 200, $headers,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );
        
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['code'=>2, 'message' => '请登录后访问.'], 200);
        }

        return redirect()->guest(route('login'));
    }


}
