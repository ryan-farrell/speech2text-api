<?php

namespace App\Models;

use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class File extends Model
{
    use HasFactory;

    ###########################
    ##     Model Methods     ##
    ###########################

    /**
     * Wrapper method to return a JsonResponse with error code and message.
     *
     * @param   string   $message            Message to return to in the response
     * @param   int      $errorCode          Error code to help with debugging
     * @param   int      $httpResponseCode   Http error code to return in response 
     *
     * @return Illuminate\Http\JsonResponse
     **/
    public function jsonError(string $message, int $errorCode, int $httpResponseCode) : JsonResponse
    {
        // No resource located respond with appropriate error code
        return response()->json([
            'status' => 'failure',
            'data' => [],
            'errors' => [
                'message' => $message,
                'error_code' => $errorCode,
            ]
        ], $httpResponseCode);
    }

    /**
     * Wrapper method to return this file details in a JsonResponse with the complete file
     * details or just a standard connection response so the user knows they've
     * connected correctly.
     *
     * @param   string   $message            Message to return to in the response
     * @param   bool     $returnData         Whether to return this file details
     * @param   int      $httpResponseCode   Http error code to return in response
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
