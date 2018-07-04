<?php

use Illuminate\Http\Request;

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
 * Route: module_reminder_assigner
 * Description: Assign module reminders for the given contact
 *
 * Params: contact_email
 */
Route::post('module_reminder_assigner', 'ApiController@assignReminder')->name('api.module_reminder_assigner');