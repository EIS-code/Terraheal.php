<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TherapistCalendarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('therapist_calendar', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->time('time_from');
            $table->time('time_to');
            $table->bigInteger('therapist_id')->unsigned();
            $table->foreign('therapist_id')->references('id')->on('therapists')->onDelete('cascade');
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
        Schema::dropIfExists('therapist_calendar');
    }
}
