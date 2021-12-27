<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTransferStatusToArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('articles', function($t) {
            $t->enum('transfer_status', ['ოფიციალური', 'ყუილი/ჩაშლა', 'მაღალი ალბათობა', 'საშუალო ალბათობა'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('articles', function($t) {
            $t->dropColumns(['transfer_status']);
        });
    }
}
