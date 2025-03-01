<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getUsers()
    {
        $users = json_decode(file_get_contents(storage_path('app/data/users.json')), true);
        return response()->json($users);
    }

    public function getViews()
    {
        $views = json_decode(file_get_contents(storage_path('app/data/views.json')), true);
        return response()->json($views);
    }
}
