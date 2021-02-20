<?php

namespace Tests\Controllers;

use Tests\TestCase;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;

/**
 * Class to test the methods in the AudioFile Controller
 */
class AudioFileControllerTest extends TestCase {

    #####################
    ### GET REQUESTS  ###
    #####################

    /**
     * @return void
     */
    public function test_a_get_request_with_no_filter_returns_the_correct_structure()
    {
        $this->json('GET', '/api/v1/audiofiles/')
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'message',
                ],
                'errors'
            ])
            ->assertJsonFragment([
                'status' => 'success',
            ]);
    }

    /**
     * @return void
     */
    public function test_get_request_with_a_filter_returns_the_correct_structure_if_resource_is_available()
    {
        $this->json('GET', '/api/v1/audiofiles/1')
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'message',
                ],
                'errors'
            ])
            ->assertJsonFragment([
                'status' => 'success',
            ]);
    }

    /**
     * @return void
     */
    public function test_get_request_with_a_filter_returns_the_correct_structure_if_resource_is_not_available()
    {
        $this->json('GET', '/api/v1/audiofiles/99999999999999999')
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJsonStructure([
                'status',
                'data',
                'errors' => [
                    'message',
                    'error_code',
                ],
            ])
            ->assertJsonFragment([
                'status' => 'failure',
            ]);
    }

    ######################
    ### POST REQUESTS  ###
    ######################

    /**
     * @return void
     */
    public function test_a_post_request_with_a_file_attached_returns_the_correct_structure()
    {
        // The path to the test file
        $test_file_path = base_path('tests/files/audio/base64encodedflacfiles/test_file');

        // Create an uploadFile we can pass to the request
        $test_file = new UploadedFile($test_file_path,'test_file', 'multipart/form-data', null, true);

        // Set the file as data to pass to the POST request in this test
        $this->json('POST', '/api/v1/audiofiles/', ['file' => $test_file])
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'message',
                    'id',
                    'file_name',
                    'request_sent_at',
                    'transcript',
                    'confidence',
                    'rate hertz',
                    'no_of_alternatives',
                    'file_size',
                ],
                'errors' => []
            ])
            ->assertJsonFragment([
                'status' => 'success',
                'no_of_alternatives' => 1,
                'transcript' => 'ok this is a testing track to see if you can hear me',
                'file_size' => 364068,
            ]);

        /** @todo clean up the filesystem and db table. */
    }

    /**
     * @return void
     */
    public function test_a_post_request_with_no_file_attached_returns_the_correct_structure()
    {
        $this->json('POST', '/api/v1/audiofiles/')
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonStructure([
                'status',
                'data',
                'errors' => [
                    'message',
                    'error_code',
                ]
            ])
            ->assertJsonFragment([
                'status' => 'failure',
            ]);
    }
}
