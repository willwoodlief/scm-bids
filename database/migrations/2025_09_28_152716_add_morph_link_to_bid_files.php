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
            $table->nullableMorphs('link');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scm_plugin_bid_files', function (Blueprint $table) {
            $table->dropMorphs('link');
        });
    }
};
