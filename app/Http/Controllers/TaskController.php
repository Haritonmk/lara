<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Task;
use App\Http\Requests;

class TaskController extends Controller {

    /**
     * 
     * @return void
     */
    public function __construct() {
        $this->middleware('auth');
    }

    public function index() {
        $tasks = Task::orderBy('created_at', 'asc')->get();

        return view('tasks', [
            'tasks' => $tasks
        ]);
    }

    /**
     * Add new task
     */
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
                    'name' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return redirect('/tasks')
                            ->withInput()
                            ->withErrors($validator);
        }

        $task = new Task;
        $task->name = $request->name;
        $task->save();

        return redirect('/tasks');
    }

    /**
     * Delete task
     */
    public function destroy(Task $task) {
        $task->delete();

        return redirect('/tasks');
    }

}
