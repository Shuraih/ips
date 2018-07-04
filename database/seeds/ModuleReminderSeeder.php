<?php

use Illuminate\Database\Seeder;
use App\ModuleReminder;
use App\Http\Helpers\InfusionsoftHelper;

class ModuleReminderSeeder extends Seeder
{
    /**
     * Retrieve all tags from Infusionsoft and seed the Module Reminders table
     *
     * @return void
     */
    public function run()
    {
        $infusionsoft = new InfusionsoftHelper();

		$tags = json_decode($infusionsoft->getAllTags(), true);

		foreach ($tags as $tag) {
			ModuleReminder::insert($tag);
		}

    }
}