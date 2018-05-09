<?php namespace App\Services;

use Log;
use Carbon\Carbon;

use App\Models\Borrow;
use App\Repositories\Eloquent\SmsRepository as Sms;
use App\Models\FormId;

class CronService
{
    protected $sms;

    /*
     * 构造函数
     */
    public function __construct(Sms $sms)
    {
        $this->sms = $sms;
        Carbon::setLocale('zh');
    }

    public function returnTip()
    {
        $borrowings = Borrow::with('user', 'book')->where('status', 'BORROW')->get();
        foreach($borrowings as $item){
            //skip...
            if(!$item->user || !$item->user->mobile){
                continue;
            }

            $time = Carbon::parse($item->return_time)->diffForHumans();
            if(strtotime($item->return_time) < time()){
                $message = $item->user->name.'您好， 您借阅的《'.$item->book->title.'》已超期都没有还书哦，'.$time.'就应该还书了!';
            }else{
                $message = $item->user->name.'您好， 您借阅的《'.$item->book->title.'》看得怎么样，可以去友福图书馆写点感想，'.$time.'内读完并归还哦!';
            }
            $this->sentMessage( $item->user->mobile, $message);
        }
        //return $message;
        return $borrowings;
    }

    //通用发短信任务
    public function sentMessage($mobile, $message)
    {
        $this->sms->sentMessage( $mobile, $message);
    }

    //检测form_id是否失效
    public function checkFormId()
    {
        $form_id_arr = FormId::where('status', 0)->get();
        $now_time = time();
        $end_time = $now_time - 7 * 24 * 60 * 60;
        foreach ($form_id_arr as $form_id) {
            $time = strtotime($form_id->created_at->toDateString());
            if ($time <= $end_time) {
                FormId::where('id', $form_id->id)->update(['status'=>1]);
            }
        }
    }
}
