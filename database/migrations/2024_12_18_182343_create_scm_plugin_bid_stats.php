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
        Schema::create('scm_plugin_bid_stats', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stats_bid_id')
                ->nullable(true)
                ->default(null)
                ->unique()
                ->comment("When bid is created a new row made, when bid deleted, the deleted timestamp or project created timestamp is made")
                ->constrained('scm_plugin_bid_singles')
                ->cascadeOnUpdate()
                ->nullOnDelete();


            $table->foreignId('stats_project_id')
                ->nullable(true)
                ->default(null)
                ->unique()
                ->comment("When bid is converted to project, new project id put here, bid_success_at set ,stats_bid_id nulled due to deletion")
                ->constrained('projects')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('stats_contractor_id')
                ->nullable(true)
                ->default(null)
                ->index()
                ->comment("the contractor for the bid. If a contractor is deleted then the stats remain without it")
                ->constrained('contractors')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('stats_user_id')
                ->nullable()
                ->index()
                ->default(null)
                ->comment("the user who created this bid")
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();


            $table->timestamp('bid_created_at')
                ->nullable(true)
                ->default(null)
                ->index()
                ->comment("When the bid was created at");

            $table->timestamp('bid_success_at')
                ->nullable(true)
                ->default(null)
                ->index()
                ->comment("When the bid was created at");

            $table->timestamp('bid_failed_at')
                ->nullable(true)
                ->default(null)
                ->index()
                ->comment("When the bid was failed");


            $table->decimal('budget',10,2)
                ->nullable(true)
                ->default(null)
                ->index()
                ->comment("Amount of the bid, saved because the bid itself is deleted on success or fail");

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scm_plugin_bid_stats');
    }
};
