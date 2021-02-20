<?php

namespace Tests\Controllers;

use Illuminate\Http\Response;
use Tests\TestCase;

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
        $this->json('GET', '/api/v1/audiofiles/1')
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'message',
                ],
                'errors'
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
        $this->json('POST', '/api/v1/audiofiles/')
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'status' => 'success',
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
            ]);
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
