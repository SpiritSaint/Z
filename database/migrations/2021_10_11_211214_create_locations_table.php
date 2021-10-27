<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->string('uuid')->primary();
            $table->string('device_uuid')->index()->nullable();
            $table->string('geofence_uuid')->index()->nullable();
            $table->string('event')->nullable();
            $table->boolean('is_moving');
            $table->point('coords');
            $table->double('altitude');
            $table->double('accuracy');
            $table->double('heading');
            $table->double('speed');
            $table->string('activity_type');
            $table->double('activity_confidence');
            $table->double('battery_level');
            $table->boolean('battery_is_charging');
            $table->double('odometer');
            $table->timestamp('timestamp');
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
        Schema::dropIfExists('locations');
    }
}
