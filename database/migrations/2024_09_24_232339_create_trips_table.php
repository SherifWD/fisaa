<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();


            $table->unsignedBigInteger('driver_id')->nullable();
            $table->foreign('driver_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('passenger_id')->nullable();
            $table->foreign('passenger_id')->references('id')->on('users')->onDelete('cascade');


            $table->unsignedBigInteger('type_id')->nullable();
            $table->foreign('type_id')->references('id')->on('trip_types')->onDelete('cascade');
            $table->string('weight')->nullable();
            $table->string('count_of_workers')->nullable();

            $table->string('from')->nullable();
            $table->string('from_lat')->nullable();
            $table->string('from_lng')->nullable();

            $table->string('to')->nullable();
            $table->string('to_lat')->nullable();
            $table->string('to_lng')->nullable();

            $table->double('price')->nullable();
            $table->boolean('is_cash')->default(1);
            $table->double('estimated_distance')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};