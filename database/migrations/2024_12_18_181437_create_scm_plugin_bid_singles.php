<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scm_plugin_bid_singles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bid_contractor_id')
                ->nullable(true)
                ->default(null)
                ->index()
                ->comment("the contractor associated with this bid")
                ->constrained('contractors')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('bid_created_by_user_id')
                ->nullable()
                ->default(null)
                ->comment("the user who created this bid")
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();


            $table->double('latitude')->nullable(false)->default(0);
            $table->double('longitude')->nullable(false)->default(0);


            $table->decimal('budget',10,2)
                ->nullable(true)
                ->default(null)
                ->comment("Amount of the bid");

            $table->date('start_date')->nullable(false);
            $table->date('end_date')->nullable(false);

            $table->timestamps();

            $table->string('bid_name',255)->nullable(false);
            $table->string('address',250)->nullable(false);
            $table->string('city',100)->nullable(false);
            $table->string('state',2)->nullable(false);
            $table->string('zip',5)->nullable(false);

        });

        DB::statement("ALTER TABLE scm_plugin_bid_singles
                              MODIFY COLUMN created_at
                              TIMESTAMP
                              NULL
                              DEFAULT current_timestamp()
                              ;
                    ");

        DB::statement("ALTER TABLE scm_plugin_bid_singles
                              MODIFY COLUMN updated_at
                              TIMESTAMP
                              NULL -- the opposite is NOT NULL, which is implicitly set on timestamp columns
                              DEFAULT NULL -- no default value for newly-inserted rows
                              ON UPDATE CURRENT_TIMESTAMP;
                    ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scm_plugin_bid_singles');
    }
};
