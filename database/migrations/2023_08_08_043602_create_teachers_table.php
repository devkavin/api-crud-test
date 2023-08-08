<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeachersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teachers', function (Blueprint $table) {
            // NOT MIGRATED YET, THIS IS TO CREATE A LOGIN SYSTEM WITH ROLES
            $table->id();
            $table->string('name');
            $table->string('email', 100);
            $table->char('phone', 10);
            $table->integer('age');
            $table->string('department');
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
        Schema::dropIfExists('teachers');
    }
}