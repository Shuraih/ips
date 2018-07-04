<?php

namespace App\Repositories;

use App\User;
use App\Module;
use App\ModuleREminder;

use Illuminate\Database\Eloquent\Model;


class ModuleRepository
{
    /**
     * @param  [string contact_email]
     * @return [eloquent object]
     *
     * Get all completed modules by user's contact_email
     */
	public function getCompletedModules($contact_email) 
    {
        $user = User::where('email', '=', $contact_email)->first();

        if(!$user) return false;

        return $user->completed_modules()->get();
    }

    /**
     * @param  [array courses]
     * @param  [eloquent object completed_modules]
     * @return [eloquent object next_module]
     *
     * Get user's next module to be reminded of given the courses 
     * that the user has subscribed for and the modules completed thus far
     */
    public function getNextModule($courses, $completed_modules)
    {
    	$next_module = null;

        foreach ($courses as $course) {

            $modules = Module::where('course_key', '=', $course)->get();

            $last_module_key = $modules->last();
            
            $last_completed_module = $completed_modules->where('course_key', '=', $course)->sortBy('id')->last();

            if($last_completed_module) {

                foreach ($modules as $module) {

                    if($module->id > $last_completed_module->id) {

                        $next_module = $module;

                        break;

                    } elseif ($last_completed_module->id == $last_module_key->id) {
                        
                        // finished all modules in course

                        break;

                    }
                }
            } else { 

            	// User has not started any modules in this course, attach first module of first course
            	if(!$next_module) $next_module = $modules->first();
            }

        }

        return $next_module;
    }
}
