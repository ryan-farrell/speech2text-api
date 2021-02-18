<?php

namespace App\Http\Controllers;

use App\Models\AudioFile;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Google\Cloud\Speech\V1\SpeechClient;
use Google\Cloud\Speech\V1\RecognitionAudio;
use Google\Cloud\Speech\V1\RecognitionConfig;
use Google\Cloud\Speech\V1\RecognitionConfig\AudioEncoding;

class AudioFileController extends Controller
{
    /** 
     * @api {GET} api/v1/audiofiles/    Retrieve a transcribed audio file
     *
     * @apiVersion 1.0.0
     *
     * @apiDescription  Endpoint to retrieve a transcribed audio files details
     * and also allow a quick check that the API is responding for a client 
     *
     * @apiName Transcriptions
     * @apiGroup Audio
     *
     * @apiParam (None) Simple confirmation of connection response
     * @apiParam (Filter Parameters) {string} id The audio file ID
     *
     * @apiSuccess {audioFile} The audio file details
     * @apiSuccessExample {json} Details of the audio file
     * 
     * {
     *   "data": { 
     *              "status":"success"
     *              "message":"Your connecting to the API! Now supply an ID of the audio file you'd like to see details for."
     *           },
     *   "error":[]
     * }
     * 
     * @apiError (Error 400) BadRequest An invalid file id produced  // @todo Check with Laravel API filters 
     * @apiError (Error 404) NotFound File id not found  // @todo Check with Laravel API filters
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AudioFile  $audioFile
     * @return \Illuminate\Http\Response
     */
    public function transcriptions(Request $request, AudioFile $audioFile)
    {

        // If successfully connected respond with 
        return response()->json([
            'status' => 'success',
            'data' => [
                'message' => 'Your connecting to the API! Now supply an ID of the audio file you\'d like to see details for.'
            ],
            'errors' => []
        ]);
    }

    /** 
     * @api {POST} api/v1/audiofiles/   Send an audio file to be transcribed
     *
     * @apiVersion 1.0.0
     *
     * @apiDescription  Create an audio file in storage and save the file details
     * in the database along with the transcription from Google's speech-to-text API. 
     *
     * @apiName Transcode
     * @apiGroup Audio
     *
     * @apiParam (File) {string} file  The base64encoded flac audio file
     *
     * @apiSuccess {audioFile} The audio file details
     * @apiSuccessExample {json} Details of the audio file
     * 
     * {
     *   "status": "success",
     *   "data": {
     *             "message": "Your file has been transcribed.",
     *             "id": 7,
     *             "file_name": "base64encodedflacfile1613685008",
     *             "request_sent_at": "2021-02-18T21:50:08.000000Z",
     *             "transcript": "ok this is a testing track to see if you can hear me",
     *             "confidence": 0.9516302,
     *             "rate hertz": 44100,
     *             "no_of_alternatives": 1,
     *             "file_size": 364068
     *   },
     *   "errors": []
     * }
     * 
     *@apiError (Error 502) BadGateway The server received an invalid response from GoogleAPI
     *@apiError (Error 400) NotFound File not attached to request 
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AudioFile  $audioFile
     *
     * @return \Illuminate\Http\Response
     */
    public function transcode(Request $request, AudioFile $audioFile)
    {
        // The time  the request was received here will set as time it was sent
        // by the client
        $requestSentAt = now();

        // Check the request contained an uploaded file
        if ($request->hasFile('file')) {

            /** 
             *@todo If request has mime type in body in theory this could be set by the upload 
             * process to the file encode if we have audio/flac then continue. If not reject 
             * now with error response. Checked on $request->mime can get it from the request?
             * Or better to check the actual file below with FFMpeg?
             */

            // Get uploaded file from the request
            $uploadedFile = $request->file('file');

            // Get the uploaded file name
            $origFilename = $uploadedFile->getClientOriginalName();

            // Store the temporary Base 64encoded FLAC file in temp dir from the file upload
            $tempFile = Storage::putFile('files/temp', $uploadedFile);

            // Get the contents of the temp file
            $contents = Storage::get($tempFile);
            
            /** 
             *@todo Need to check if the file contents are base64 encoded if not can be
             * sent back to client with incorrect file type uploaded? Checking for base64 looks
             * iffy? Don't like how this is hitting the file system before checking!
             */

            // Decode the content of the file
            $contents = base64_decode($contents);

            // Get unique timestamp to add to file name to make the file name unique
            $origFilename = $origFilename.Carbon::now()->timestamp;

            // Store the decoded file 
            Storage::put('files/audio/'.$origFilename, $contents);
            
            /** 
             *@todo Check the file here for length(secs), size, codec, encode, hertz? Will require 3rd party FFMpeg??
             * Send back response file to large not good enough hertz wrong encoding? Then delete file if not transcribing
             * Storage::delete('files/audio/'.$origFilename)!!;
             */

            // Get the file size for later
            $audioFileSize = Storage::size('files/audio/'.$origFilename);

            // Now we our decoded file 
            Storage::deleteDirectory('files/temp');

            // Credentials needed to use API
            putenv('GOOGLE_APPLICATION_CREDENTIALS='.base_path('setup-files/setup.json'));

            /**
             *@todo This is going to have to be from the query on the file from the 3rd party package FFMpeg
             */ 
            $sampleRateHertz = 44100;
            $fileEncoding = AudioEncoding::FLAC;

            // Set content of the file on the audio object ready to be passed to the SpeechClient 
            $audio = (new RecognitionAudio())
                ->setContent($contents);

            // Set config
            $config = (new RecognitionConfig())
                ->setEncoding($fileEncoding)
                ->setSampleRateHertz($sampleRateHertz)
                ->setLanguageCode('en-GB');

            // Create the speech client
            $speechClient = new SpeechClient();

            try {
                // Get our response from Google API
                $response = $speechClient->recognize($config, $audio);

                foreach ($response->getResults() as $result) {

                    $alternatives = $result->getAlternatives();
                    $mostLikely = $alternatives[0];
                    $transcript = $mostLikely->getTranscript();
                    $confidence = $mostLikely->getConfidence();
                    $numOfAlternatives = count($alternatives);
                }
            } finally {
                // Close the speech client
                $speechClient->close();
            }

            // Set the values of the file to be saved
            $audioFile->file_name = $origFilename;
            $audioFile->mime = 'audio/flac';
            $audioFile->rate_hertz = $sampleRateHertz;
            $audioFile->transcript = $transcript;
            $audioFile->confidence = $confidence;
            $audioFile->no_of_alternatives = $numOfAlternatives;
            $audioFile->file_size = $audioFileSize;
            $audioFile->request_sent_at = $requestSentAt;
            $audioFile->created_at = now();
            $audioFile->updated_at = now();

            // Save the details of this file
            if ($audioFile->save()) {

                // If successful respond in kind
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'message' => 'Your file has been transcribed.',
                        'id' => $audioFile->id,
                        'file_name' => $audioFile->file_name,
                        'request_sent_at' => $audioFile->request_sent_at,
                        'transcript' => $audioFile->transcript,
                        'confidence' => $audioFile->confidence,
                        'rate hertz' => $audioFile->rate_hertz,
                        'no_of_alternatives' => $audioFile->no_of_alternatives,
                        'file_size' => $audioFile->file_size,
                    ],
                    'errors' => []
                ]);
            }

            /**
             *@todo Failed to save (check if error from a connection to google api we can attempt again with the saved file)
             */
            return response()->json([
                'status' => 'failure',
                'data' => [],
                'errors' => [
                    'message' => 'There was a problem transcribing and saving audio file.',
                    'error_code' => 1613606485,
                ],
            ], 502);

        } else {

            // No file uploaded respond with error
            return response()->json([
                'status' => 'failure',
                'data' => [],
                'errors' => [
                    'message' => 'No file attached!',
                    'error_code' => 1613606336,
                ]
            ], 400);
        }
    }
}
