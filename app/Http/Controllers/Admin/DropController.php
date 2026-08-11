<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class DropController extends Controller
{
    public function index()
    {
        return view('admin.drops.index');
    }

    public function edit(string $id)
    {
        return view('admin.drops.edit', ['id' => $id]);
    }
}
