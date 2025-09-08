<?php

namespace App\Http\Controllers;

use App\HandwerkTodo;
use Illuminate\Http\Request;

class HandwerkTodoController extends Controller
{
  public function index()
  {
    // Get all todos and return to view
  }

  public function create()
  {
    // Return a view to create a new todo
  }


  public function show(HandwerkTodo $handwerkTodo)
  {
    // Show a single todo
  }

  public function edit($city, $id)
  {
    $todo = HandwerkTodo::findOrFail($id);

    return response()->json($todo, 200);
  }

public function updateTodo(Request $request, $city, $id)
{
    $todo = HandwerkTodo::findOrFail($id);

    $todo->title = $request->title;
    $todo->body = $request->body;
    $todo->save();

    // Load the user relationship to get the username
    $todo->load('submitter');

    // Get the updated_at date in German format
    $todo->updated_at_german = $todo->updated_at->format('d') . '.' . 
                               $todo->updated_at->locale('de')->monthName . '.' . 
                               $todo->updated_at->format('y H:i');

    // Add the user's username to the returned data
    $todo->username = $todo->submitter->username;

    return response()->json($todo, 200);
}
public function destroy($city, $id) 
{
    $todo = HandwerkTodo::findOrFail($id);
    $todo->delete();
    return response()->json(null, 204);
}
}
