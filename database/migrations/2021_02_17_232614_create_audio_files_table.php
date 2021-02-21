<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAudioFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audio_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('file_name')->comment('File Name');
            $table->text('mime')->comment('Mime Type');
            $table->integer('rate_hertz')->comment('Rate Hertz');
            $table->text('transcript')->nullable()->comment('Transcript');
            $table->float('confidence')->nullable()->comment('Confidence');
            $table->integer('no_of_alternatives')->nullable()->comment('No. of Alternatives');
            $table->integer('file_size')->comment('File Size');
            $table->dateTime('request_sent_at')->comment('Request Sent At');
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
        Schema::dropIfExists('audio_files');
    }
}
