<?php

namespace App\Http\Controllers;

use App\Models\AudioFile;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

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
     * @apiSuccessExamples {json} Confirmation message or details of the audio file transcription if id supplied
     * 
     * {
     *   "data": { 
     *              "status":"success"
     *              "message":"Your connecting to the API! Now supply an ID of the audio file you'd like to see details for."
     *           },
     *   "error":[]
     * }
     * 
     * {
     *   "status": "success",
     *   "data": {
     *       "message": "Audio was transcribed on 2021-02-18 13:53:01.",
     *       "id": 3,
     *       "file_name": "base64encodedflacfile1613656379",
     *       "request_sent_at": "2021-02-18T13:52:59.000000Z",
     *       "transcript": "ok this is a testing track to see if you can hear me",
     *       "confidence": 0.95,
     *       "rate hertz": 44100,
     *       "no_of_alternatives": 1,
     *       "file_size": 364068
     *   },
     *   "errors": []
     * }
     * 
     * @apiError (Error 404) NotFound File id not found
     *
     * @apiErrorExamples {json} Error message the audio file searched could not be found
     * 
     * {
     *   "status": "failure",
     *   "data": [],
     *   "errors": {
     *       "message": "The file could not be found",
     *       "error_code": 1513606716
     *   }
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AudioFile  $audioFile
     * @return \Illuminate\Http\JsonResponse
     */
    public function transcriptions(Request $request, AudioFile $audioFile) : JsonResponse
    {
        // If the request contains an id then one is being searched for 
        if(isset($request->id)) {

            try {
                // Quick check to see if a model has been found
                $audioFile = AudioFile::findOrFail($request->id);

                // If successfully connected respond with the details
                return $audioFile->jsonSuccess('Audio was transcribed on '.$audioFile->created_at.'.', true, Response::HTTP_OK);

            } catch (ModelNotFoundException $e) {
                // If no model catch and return it here
                return $audioFile->jsonError('The file could not be found', 1513606716, Response::HTTP_NOT_FOUND);
            }
        }

        // If no filter/id supplied we still want them them to receive 200 response to confirm they're connecting OK
        return $audioFile->jsonSuccess('Your connecting to the API! Now supply an ID of the audio file you\'d like to see the transcription for.');
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
     * @apiError (Error 502) BadGateway The server received an invalid response from GoogleAPI
     * @apiError (Error 400) NotFound File not attached to request 
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AudioFile  $audioFile
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function transcribed(Request $request, AudioFile $audioFile) : JsonResponse
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

            // Call Google API and transcribe the file
            $audioFile->transcribeFile($contents);

            // Set the values of the file to be saved
            $audioFile->file_name = $origFilename;
            $audioFile->mime = 'audio/flac';
            $audioFile->rate_hertz = AudioFile::RATE_HERTZ;
            $audioFile->file_size = $audioFileSize;
            $audioFile->request_sent_at = $requestSentAt;
            $audioFile->created_at = now();
            $audioFile->updated_at = now();

            // Save the details of this file
            if ($audioFile->save()) {
                // If save was successful respond with the details
                return $audioFile->jsonSuccess('Your file has been transcribed.', true, Response::HTTP_OK);
            } else {
                /**
                 *@todo Failed to save to our database. Maybe retry from the file system copy.
                */
                return $audioFile->jsonError('There was a problem saving the audio file.', 1613606485, Response::HTTP_BAD_GATEWAY);
            }

        } else {

            // No file uploaded respond with error
            return $audioFile->jsonError('No file attached!', 1613606336, Response::HTTP_BAD_REQUEST);
        }
    }
}
