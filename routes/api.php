<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AudioFileController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/**
 * API has GET route to retrieve an individual transcription {if id supplied}. If not 
 * the GET response will be just a confirm connection response to that effect.
 * 
 * The POST route is used to upload a base 64 encoded FLAC audio file to be transcribed.
 * The POST route body will need "form-data" with a key value pair [file => 'your_file']
 * attached and the content type set to "multipart/form-data".
 */
Route::get('/v1/audiofiles/{id?}', [AudioFileController::class, 'transcriptions']);
Route::post('/v1/audiofiles/', [AudioFileController::class, 'transcribed']);