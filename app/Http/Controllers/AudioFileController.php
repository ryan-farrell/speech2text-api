<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AudioFile;

class AudioFileController extends Controller
{
    /**
     * Create the audio file in storage and save the file details
     * in the database along with the transcription from Google's speech-to-text API.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AudioFile  $audioFile
     *
     * @return \Illuminate\Http\Response
     */
    public function uploadAndTranscode(Request $request, AudioFile $audioFile)
    {

        // Set the values of the file to be saved
        $audioFile->file_name = 'Test';
        $audioFile->transcript ='Test test test';
        $audioFile->request_sent_at = now();
        $audioFile->created_at = now();
        $audioFile->updated_at = now();

        // Save the details of this file
        if ($audioFile->save()) {

            // If successful respond
            return response()->json(['message' => 'Your file has been transcribed.']);
        }
    }
}
