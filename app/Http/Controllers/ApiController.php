<?php

namespace App\Http\Controllers;

use App\Http\Helpers\InfusionsoftHelper;
use Illuminate\Http\Request;
use Response;

use App\Repositories\ModuleRepository;
use App\User;
use App\Module;
use App\ModuleReminder;

use App\Http\Requests\ModuleReminderAssignRequest;

class ApiController extends Controller
{
    // Todo: Module reminder assigner

    private function exampleCustomer(){

        $infusionsoft = new InfusionsoftHelper();

        $uniqid = uniqid();

        $infusionsoft->createContact([
            'Email' => $uniqid.'@test.com',
            "_Products" => 'ipa,iea'
        ]);

        $user = User::create([
            'name' => 'Test ' . $uniqid,
            'email' => $uniqid.'@test.com',
            'password' => bcrypt($uniqid)
        ]);

        // attach IPA M1-3 & M5
        $user->completed_modules()->attach(Module::where('course_key', 'ipa')->limit(3)->get());
        $user->completed_modules()->attach(Module::where('name', 'IPA Module 5')->first());


        return $user;
    }

    /**
     * @param  ModuleReminderAssignRequest
     * @return [json] Response
     *
     * Get user's module reminder and assign it via infusionsoft given the following conditions:
     *
     * - If no modules are completed - attach first tag in order.
     * - If any of first course modules are completed - attach next uncompleted module 
     *   after the last completed of the first course. (e.g.. M1, M2 & M4 are completed, then attach M5 tag)
     * - If all (or last) first course modules are completed - attach next uncompleted module after the last 
     *   completed of the second course. Same applies in case of a third course.
     * - If all (or last) modules of all courses are completed - attach “Module reminders completed” tag.
     */
    public function assignReminder(ModuleReminderAssignRequest $request) {

        $validated = $request->validated();

        $contact_email = $validated['contact_email'];

        // Validate user and get completed modules
        $repo = app(ModuleRepository::class);

        $completed_modules = $repo->getCompletedModules($contact_email);

        if($completed_modules == false) {
            return response()->json(['success' => 'false', 'message' => 'No user found with the provided email address']);
        }

        // Validate user existence in infusionsoft and get courses
        $infusionsoft = app(InfusionsoftHelper::class);

        $user_infusionsoft = $infusionsoft->getContact($contact_email);

        if(!is_array($user_infusionsoft)) {
            return response()->json(['success' => 'false', 'message' => 'No infusionsoft user found with the provided email address']);
        }

        // User is not subscribed to any courses
        if(!isset($user_infusionsoft['_Products'])) {
            return response()->json(['success' => 'false', 'message' => 'User has not subscribed to any courses']);
        }

        $courses = explode(',', $user_infusionsoft['_Products']);

        // Get next module for user to be reminded of
        $next_module = $repo->getNextModule($courses, $completed_modules);

        $tag_success = false;

        // All modules complete
        if($next_module === null) {

            $tag = ModuleReminder::where(['name' => 'Module reminders completed'])->first();

            $tag_success = $infusionsoft->addTag($user_infusionsoft['Id'], $tag->id);

            if($tag_success) {

                return response()->json(['success' => 'true', 'message' => 'Successfully added reminder for all modules being completed']);

            }

        } else { // User has a module to be reminded of

            $tag = ModuleReminder::where(['name' => 'Start '.$next_module->name.' Reminders'])->first();

            $tag_success = $infusionsoft->addTag($user_infusionsoft['Id'], $tag->id);

            if($tag_success) {

                return response()->json(['success' => 'true', 'message' => 'Successfully added reminder for: '.$next_module->name]);

            }

        }
        
        return response()->json(['success' => 'false', 'message' => 'Failed to assign reminder']);

    }
}
