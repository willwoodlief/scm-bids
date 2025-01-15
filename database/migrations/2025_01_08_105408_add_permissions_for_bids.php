<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    const TRIGGERS = [
        'after_single_bid_deleted'
    ];
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \Scm\PluginBid\Helpers\PluginPermissions::doInit();

        foreach (static::TRIGGERS as $trigger_name) {
            $trigger_file = $trigger_name.'.sql';
            $trigger = file_get_contents(base_path("plugins/scm-plugin-bids/database/triggers/bids/$trigger_file"));
            if (!$trigger) {
                throw new RuntimeException("Trigger $trigger_name not found for (bid)");
            }
            DB::statement($trigger);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Scm\PluginBid\Helpers\PluginPermissions::doRemove();

        foreach (static::TRIGGERS as $trigger_name) {
            $sql = "DROP TRIGGER IF EXISTS $trigger_name";
            DB::statement($sql);
        }
    }
};
