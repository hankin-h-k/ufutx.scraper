<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CronController extends Controller
{

    public function cronTransfer($name){
        $data = \CronService::$name();
        return $this->success($name, $data);
    }
}

