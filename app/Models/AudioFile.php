<?php

namespace App\Models;

use App\Models\File;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AudioFile extends File
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'file_name',
        'mime',
        'rate_hertz',
        'transcript',
        'confidence',
        'no_of_alternatives',
        'file_size',
        'request_sent_at',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'request_sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    ###########################
    ##     Model Methods     ##
    ###########################

    /**
     * @see \App\Models\File
     * 
     * @return Illuminate\Http\JsonResponse
     **/
    public function jsonSuccess(string $message, bool $returnData = false, int $httpResponseCode = 200) : JsonResponse
    {
        if ($returnData) {
            // If true will sent the data in response
            return response()->json([
                'status' => 'success',
                'data' => [
                    'message' => $message,
                    'id' => $this->id,
                    'file_name' => $this->file_name,
                    'request_sent_at' => $this->request_sent_at,
                    'transcript' => $this->transcript,
                    'confidence' => $this->confidence,
                    'rate hertz' => $this->rate_hertz,
                    'no_of_alternatives' => $this->no_of_alternatives,
                    'file_size' => $this->file_size,
                ],
                'errors' => []
            ], $httpResponseCode);

        } else {
            // If false will send message of your choice  
            return response()->json([
                'status' => 'success',
                'data' => [
                    'message' => $message,
                ],
                'errors' => []
                ], $httpResponseCode);
        }
    }
}
