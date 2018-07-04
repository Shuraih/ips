<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Module;

use Mockery;

class AssignModuleReminder extends TestCase
{
	protected $user;
	protected $modules;
	protected $infusionsoft;
	protected $contact_email;

	/**
	 * Preload test attributes
	 */
	public function setUp()
    {
        parent::setUp();

        $this->contact_email = 'test-'.uniqid().'@test.com';

    	$this->user = factory(\App\User::class)->make();
    	$this->user->email = $this->contact_email;

		$this->modules = Mockery::mock(\App\Repositories\ModuleRepository::class);

        $this->infusionsoft = Mockery::mock(\App\Http\Helpers\InfusionsoftHelper::class);
        
    }

    /**
     * Abstract functionality for module additions for tests
     */
    private function addModules()
    {

    	$this->modules
           ->shouldReceive('getCompletedModules')
           ->withAnyArgs()
           ->andReturn(collect($this->user->completed_modules));

        $this->modules->makePartial();

        $this->app->instance(\App\Repositories\ModuleRepository::class, $this->modules);

        $this->app->instance(\App\Http\Helpers\InfusionsoftHelper::class, $this->infusionsoft);

    }


	/**
	 * Test: If no modules are completed - attach first tag in order.
	 */
	public function testFirstModule()
	{

		

        $this->infusionsoft->shouldReceive('getContact')
        	->with($this->contact_email)
        	->andReturn(['_Products' => 'ipa', 'Id' => 1]);

        $this->infusionsoft->shouldReceive('addTag')
        	->with('1', '110')
        	->andReturn(true);

        $this->addModules();

        

        $response = $this->call('POST', '/api/module_reminder_assigner', ['contact_email' => $this->contact_email]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => 'true',
                'message' => 'Successfully added reminder for: IPA Module 1'
            ]);
	}


	/**
	 * Test: If any of first course modules are completed - attach next uncompleted module 
     *       after the last completed of the first course. (e.g.. M1, M2 & M4 are completed, then attach M5 tag)
	 */
	public function testNextSequentialModule()
	{

        $this->user->completed_modules->push(Module::where('name', 'IPA Module 1')->first());

        $this->user->completed_modules->push(Module::where('name', 'IPA Module 2')->first());

        $this->user->completed_modules->push(Module::where('name', 'IPA Module 4')->first());

        $this->infusionsoft->shouldReceive('getContact')
        	->with($this->contact_email)
        	->andReturn(['_Products' => 'ipa', 'Id' => 1]);

        $this->infusionsoft->shouldReceive('addTag')
        	->with('1', '118')
        	->andReturn(true);

        $this->addModules();

        $response = $this->call('POST', '/api/module_reminder_assigner', ['contact_email' => $this->contact_email]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => 'true',
                'message' => 'Successfully added reminder for: IPA Module 5'
            ]);
	}


	/**
	 * Test: If all (or last) first course modules are completed - attach next uncompleted module after the last 
     *       completed of the second course. Same applies in case of a third course.
	 */
	public function testNextCourseModule()
	{

        $this->user->completed_modules->push(Module::where('name', 'IPA Module 7')->first());


        $this->infusionsoft = Mockery::mock(\App\Http\Helpers\InfusionsoftHelper::class);

        $this->infusionsoft->shouldReceive('getContact')
        	->with($this->contact_email)
        	->andReturn(['_Products' => 'ipa,iea', 'Id' => 1]);

        $this->infusionsoft->shouldReceive('addTag')
        	->with('1', '124')
        	->andReturn(true);

        $this->addModules();

        $response = $this->call('POST', '/api/module_reminder_assigner', ['contact_email' => $this->contact_email]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => 'true',
                'message' => 'Successfully added reminder for: IEA Module 1'
            ]);
	}


	/**
	 * Test: If all (or last) modules of all courses are completed - attach “Module reminders completed” tag.
	 */
	public function testModulesComplete()
	{
        $this->user->completed_modules->push(Module::where('name', 'IPA Module 7')->first());

        $this->user->completed_modules->push(Module::where('name', 'IEA Module 7')->first());

        $this->infusionsoft->shouldReceive('getContact')
        	->with($this->contact_email)
        	->andReturn(['_Products' => 'ipa,iea', 'Id' => 1]);

        $this->infusionsoft->shouldReceive('addTag')
        	->with('1', '154')
        	->andReturn(true);

        $this->addModules();

        $response = $this->call('POST', '/api/module_reminder_assigner', ['contact_email' => $this->contact_email]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => 'true',
                'message' => 'Successfully added reminder for all modules being completed'
            ]);
	}

}
