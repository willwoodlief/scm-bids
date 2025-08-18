<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('scm_plugin_bid_files', function (Blueprint $table) {
            $table->string('original_file_name',200)->nullable()->default(null)
                ->comment("holds the unprocessed file")
                ->after('bid_file_name');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scm_plugin_bid_files', function (Blueprint $table) {
            $table->dropColumn('original_file_name');
        });
    }
};
