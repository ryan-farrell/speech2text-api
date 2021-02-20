<?php

namespace App\Models;

use App\Models\File;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Google\Cloud\Speech\V1\SpeechClient;
use Google\Cloud\Speech\V1\RecognitionAudio;
use Google\Cloud\Speech\V1\RecognitionConfig;
use Google\Cloud\Speech\V1\RecognitionConfig\AudioEncoding;

class AudioFile extends File
{
    use HasFactory;

	/**
	 * @todo Use FFMPeg or similar to retrieve hertz & encoding so we 
	 * could always set the values dynamically each time no constants
	 */
    const RATE_HERTZ = 44100;
    const FILE_ENCODING = AudioEncoding::FLAC;

    const LANG_CODE = 'en-GB';

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

    /**
     * Method to call the Google API and transcribe the audio from this file
     * to text. It will then set the data received from Google's API into this file.
     * 
     * @return void
     **/
    public function transcribeFile(string $audioFileContents) : void
    {
		// Get the config details of the file
        $configRecognition = $this->getConfigRecognition();

        // Get content of the file on the audioRecognition ready to be passed to the SpeechClient 
        $audioRecognition = $this->getAudioRecognition($audioFileContents);

        // Create a new google speech client
        $speechClient = new SpeechClient();

        try {
            // Get our response from Google API
            $response = $speechClient->recognize($configRecognition, $audioRecognition);

            foreach ($response->getResults() as $result) {

                $alternatives = $result->getAlternatives();
                $mostLikely = $alternatives[0];
                $this->attributes['transcript'] = $mostLikely->getTranscript();
                $this->attributes['confidence'] = $mostLikely->getConfidence();
                $this->attributes['no_of_alternatives'] = count($alternatives);
            }
        } finally {
            // Close the speech client
            $speechClient->close();
        }
    }

    /**
     * Method to create the audio recognition object required for Google's
     * speech-to-text API from its file contents
     * 
     * @param   string   $audioFileContents   The file contents as a string
	 * 
     * @return  RecognitionAudio
     **/
    public function getAudioRecognition(string $audioFileContents) : RecognitionAudio
    {
        // Get content of the file on the audio object ready to be passed to the SpeechClient 
        return (new RecognitionAudio())->setContent($audioFileContents);
    }

    /**
     * Method to create the config recognition object required for Google's
     * speech-to-text API
     *
     * @return  RecognitionConfig
     **/
    public function getConfigRecognition() : RecognitionConfig
    {
        // Get content of the file on the audio object ready to be passed to the SpeechClient 
        return (new RecognitionConfig())
			->setEncoding(Self::FILE_ENCODING)
			->setSampleRateHertz(Self::RATE_HERTZ)
			->setLanguageCode(Self::LANG_CODE);
    }
}
