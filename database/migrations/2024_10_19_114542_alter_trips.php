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
        Schema::table('trips', function (Blueprint $table) {
            // Remove old string columns
            $table->dropColumn('weight');
            $table->dropColumn('count_of_workers');

            // Add new foreign key columns
            $table->unsignedBigInteger('weight_id')->nullable()->after('type_id');
            $table->foreign('weight_id')->references('id')->on('object_weights')->onDelete('set null');

            $table->unsignedBigInteger('worker_id')->nullable()->after('weight_id');
            $table->foreign('worker_id')->references('id')->on('workers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            // Drop foreign key constraints
            $table->dropForeign(['weight_id']);
            $table->dropForeign(['worker_id']);

            // Drop the foreign key columns
            $table->dropColumn('weight_id');
            $table->dropColumn('worker_id');

            // Restore the old columns
            $table->string('weight')->nullable();
            $table->string('count_of_workers')->nullable();
        });
    }
};