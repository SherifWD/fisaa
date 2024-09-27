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
            $table->enum('object_type', ['Building materials', 'Furniture', 'Food', 'Electrical objects', 'Others'])->nullable()->after('weight');

            $table->string('sender_name')->nullable()->after('object_type');
            $table->string('sender_phone')->nullable()->after('sender_name');
            $table->string('receiver_name')->nullable()->after('sender_phone');
            $table->string('receiver_phone')->nullable()->after('receiver_name');

            $table->enum('workers_needed', ['0', '1', '2', '3+'])->nullable()->after('count_of_workers');

            $table->enum('payment_by', ['sender', 'receiver'])->nullable()->after('is_cash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn('object_type');
            $table->dropColumn('sender_name');
            $table->dropColumn('sender_phone');
            $table->dropColumn('receiver_name');
            $table->dropColumn('receiver_phone');
            $table->dropColumn('workers_needed');
            $table->dropColumn('payment_by');
        });
    }
};