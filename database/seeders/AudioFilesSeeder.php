<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AudioFilesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('audio_files')->insert([
            'id' => 1,
            'file_name' => 'test_file1613999999',
            'mime' => 'audio/flac',
            'rate_hertz' => 44100,
            'transcript' => 'text to seed the database not from an audio file',
            'confidence' => 0.99,
            'no_of_alternatives' => 3,
            'file_size' => 366000,
            'request_sent_at' => '2021-02-21 16:50:28',
            'created_at' => '2021-02-21 16:50:31',
            'updated_at' => '2021-02-21 16:50:31',
        ]);
    }
}

