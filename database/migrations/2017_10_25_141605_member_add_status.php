<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MemberAddStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('members', function (Blueprint $table) {
			$table->enum('status', ['JOIN', 'MEMBER', 'ADMIN'])->default('JOIN')->after('group_id')->comment('状态'); 
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
        Schema::table('members', function (Blueprint $table) {
			$table->dropColumn('status');
	    }); 
    }
}
