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
        \Scm\PluginBid\Helpers\PluginPermissions::doInit();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Scm\PluginBid\Helpers\PluginPermissions::doRemove();
    }
};
