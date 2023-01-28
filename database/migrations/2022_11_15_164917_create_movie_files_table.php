<?php

use App\Models\Movie;
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
        Schema::create('movie_files', function (Blueprint $table) {
            $table->id()->from(time());
            $table->foreignIdFor(Movie::class);
            $table->string('name');
            $table->string('description');
            $table->string('thumbnail')->nullable();
            $table->string('video')->nullable();
            $table->string('preview')->nullable();
            $table->integer('size')->nullable();
            $table->integer('duration')->nullable();
            $table->json('meta')->nullable();
            $table->datetime('processed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('movie_files');
    }
};
