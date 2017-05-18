<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserTest extends TestCase
{
    use DatabaseTransactions;
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $user = factory(App\User::class)->create();

        $this->actingAs($user)
             ->withSession(['foo' => 'bar'])
             ->visit('/')
             ->see($user->name);
    }
    
    public function testAddTask()
    {
        $user = factory(App\User::class)->create();
        $response = $this->actingAs($user)
                ->visit('/tasks')
                ->type('Taylor', 'name')
                ->press('addTask')
                ->see('Taylor');
                //->action('POST', 'TaskController@store', ['name' => 'Taylor']);
        //$this->assertEquals(200, $response->status());
    }
}
