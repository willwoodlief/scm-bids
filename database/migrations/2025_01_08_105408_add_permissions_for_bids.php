<?php

use Illuminate\Database\Migrations\Migration;

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
