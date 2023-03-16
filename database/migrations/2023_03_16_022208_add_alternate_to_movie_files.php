<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('movie_files', function (Blueprint $table) {
            $table->string('alt_thumbnail')->nullable();
            $table->string('background_picture')->nullable();
            $table->string('title_image')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('movie_files', function (Blueprint $table) {
            $table->dropColumn('alt_thumbnail');
            $table->dropColumn('background_picture');
            $table->dropColumn('title_image');
        });
    }
};
