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
        Schema::create('scm_plugin_bid_files', function (Blueprint $table) {
            $table->id();

            $table->foreignId('owning_bid_id')
                ->nullable(true)
                ->default(null)
                ->index()
                ->comment("The owning bid. When bid is deleted, we know to cleanup these files if they have a null bid and a null project.")
                ->constrained('scm_plugin_bid_singles')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('owning_project_id')
                ->nullable(true)
                ->default(null)
                ->index()
                ->comment("When bid is converted to project, the files may want to be kept, or at least a zipped version of them")
                ->constrained('projects')
                ->cascadeOnUpdate()
                ->nullOnDelete();




            $table->foreignId('uploaded_by_user_id')
                ->nullable()
                ->default(null)
                ->comment("the user who uploaded this file for the bid")
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();


            $table->unsignedInteger('bid_file_size_bytes')
                ->nullable()
                ->default(null)
                ->comment("The size of the file in 8 bit bytes")
                ->index();

            $table->string('bid_file_category',15)
                ->index()
                ->nullable(false)
                ->default('unknown')
                ->comment("the type of file that is verified here");

            $table->timestamps();

            $table->string('bid_file_extension',5)
                ->nullable()
                ->default(null)
                ->comment("the file extension without the name")
            ;



            $table->string('bid_file_name')
                ->nullable()
                ->default(null)
                ->comment("The file name includes extension and path from the bid plugin storage");

            $table->string('bid_file_human_name')
                ->nullable()
                ->default(null)
                ->comment("includes file extension");

            $table->string('bid_file_mime_type')
                ->nullable()
                ->default(null)
                ->comment("The mime type of the file");
        });


        DB::statement("ALTER TABLE scm_plugin_bid_files
                              MODIFY COLUMN created_at
                              TIMESTAMP
                              NULL
                              DEFAULT current_timestamp()
                              ;
                    ");

        DB::statement("ALTER TABLE scm_plugin_bid_files
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
        Schema::dropIfExists('scm_plugin_bid_files');
    }
};
