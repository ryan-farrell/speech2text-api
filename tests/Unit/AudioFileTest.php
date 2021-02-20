<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\AudioFile;

class AudioFileTest extends TestCase
{
    /** @var AudioFile $audioFile   Our audio file to run test on */
    public $audioFile = null;

    public function setup() : void
    {
        $this->audioFile = new AudioFile();
    }

    public function tearDown() : void
    {
        $this->audioFile = null;
    }

    /**
     * @return void
     */
    public function test_transcribe_file()
    {
        $this->assertEquals(null, $this->audioFile->transcript);
        
        // The path to the test file
        $test_file_path = base_path('tests/files/audio/flac/test_file1613846342');

        // The contents of our test file
        $test_contents = file_get_contents($test_file_path);

        // Run the method were testing
        $this->audioFile->transcribeFile($test_contents);

        // Check the outcome
        $this->assertEquals('ok this is a testing track to see if you can hear me', $this->audioFile->transcript);
    }
}
