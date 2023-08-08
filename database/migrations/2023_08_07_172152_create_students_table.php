<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            // id is the primary key by default according to documentation
            // id is bigint by default, biginteger is 64 bit integer(exceeds int limit)
            $table->id();
            $table->string('name');
            $table->string('email', 100);
            //char to store phone number with 0 in front
            $table->char('phone', 10);
            $table->integer('age');
            // TODO: timestamp format is YYYY-MM-DD HH:MM:SS
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('students');
    }
}
