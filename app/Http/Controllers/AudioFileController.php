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
     * @apiVersion 0.1.0
     *
     * @apiName Transcription
     * @apiGroup Audio
     *
     * @apiDescription  Endpoint to retrieve a previously transcribed files details. It will also allow
     * a quick check that the API is responding for a client if no file id passed in the URL, see example usage.
     *
     * @apiExample Example usage (without id):
     * curl -i http://localhost:8000/api/v1/audiofiles/
     *
     * @apiExample Example usage (with id):
     * curl -i http://localhost:8000/api/v1/audiofiles/1
     * 
     * @apiParam (Filter Parameters) {null}   -   Just leave it empty
     * @apiParam (Filter Parameters) {number} id  The audio file id
     *
     * @apiSuccess {string}      status             Response outcome
     * @apiSuccess {object}      data               All the data held on the file
     * @apiSuccess {string}      message            Confirmation message
     * @apiSuccess {number}      id                 file id
     * @apiSuccess {string}      file_name          New unique filename
     * @apiSuccess {datetime}    request_sent_at    Datetime request received by the server
     * @apiSuccess {string}      transcript         The audio transcribed
     * @apiSuccess {number}      confidence         The APIs accuracy of the transcription
     * @apiSuccess {number}      rate_hertz         The hertz rate
     * @apiSuccess {number}      no_of_alternatives No. of alternative transcriptions
     * @apiSuccess {number}      file_size          The file size
     * @apiSuccess {array}       errors             An array of errors (empty on success)
     * 
     * @apiSuccessExample {json} Confirmation message or details of the audio file transcription if valid id supplied
     * 
     * {
     *   "status":"success",
     *   "data": { 
     *      "message":"Your connecting to the API! Now supply an ID of the audio file you'd like to see details for."
     *   },
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
     * @apiErrorExample {json} Error message: The file could not be found
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
     * @apiVersion 0.1.0
     *
     * @apiDescription  Get the text from an audio file in a response from Google's speech-to-text API.
     * The uploaded audio file details will be stored in the database along with the transcription details. 
     *
     * @apiName Transcode
     * @apiGroup Audio
     *
     * @apiExample Example usage:
     * curl -X POST -F "file=@{PATH_TO\YOUR\ENCODED_FILE)" http://127.0.0.1:8000/api/v1/audiofiles
     * 
     * @apiParam (Filter Parameters) {string} file The base64encoded flac audio file
     *
     * @apiSuccess {AudioFile} object Details of the audio file
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
     * @apiError (Error 400) NoFileAttached File not attached to request 
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
             * Send back response to user file to large not good enough hertz wrong encoding etc. We could check the length here
             * and if over 60 secs we could call the async version of google api that work on the larger files and then respond
             * to the user at a later time with another form of communication. Then delete file if not transcribing
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
