<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\AudioFile;

class AudioFileTest extends TestCase
{
    /** @var AudioFile $audiofile   Our audio file to run test on */
    public $audiofile = null;

    public static function setUpBeforeClass() : void
    {

    }

    public static function tearDownAfterClass() : void
    {

    }

    public function setup() : void
    {
        $this->audiofile = new AudioFile();
    }

    public function tearDown() : void
    {
        $this->audiofile = null;
    }

    /**
     * @return void
     */
    public function test_audiofile_has_beed_created_ready_for_testing()
    {
        $this->assertIsObject($this->audiofile);
    }
}
