<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    // Show a list of tasks based on status
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');

        if ($status === 'pending') {
            $tasks = Task::where('is_completed', 0)->get();
        } else {
            $tasks = Task::all();
        }

        if ($request->ajax()) {
            return view('tasks.partials.task_list', compact('tasks'))->render();
        }

        return view('tasks.index', compact('tasks'));
    }

    // Store a new task
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        // Check for duplicate task
        $exists = Task::where('name', $request->input('name'))->exists();
        if ($exists) {
            return response()->json(['error' => 'Task already exists'], 400);
        }

        // Create and save the new task
        $task = new Task();
        $task->name = $request->input('name');
        $task->save();

        return response()->json($task, 201);
    }


    // Update task completion status
    public function update(Request $request, $id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        $task->is_completed = !$task->is_completed;
        $task->save();

        return response()->json($task);
    }

    // Delete a task
    public function destroy($id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        $task->delete();

        return response()->json(['success' => true]);
    }
}
