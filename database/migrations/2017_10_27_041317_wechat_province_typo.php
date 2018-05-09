<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class WechatProvinceTypo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('wechats', function (Blueprint $table) {
            $table->renameColumn('privince', 'province');
	    }); 
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('wechats', function (Blueprint $table) {
            $table->renameColumn('province', 'privince');
	    }); 
    }
}
