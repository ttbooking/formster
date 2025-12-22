<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use TTBooking\Formster\Facades\ActionHandler;
use Workbench\App\Models\Frankenstein;

Route::get('/formster/{model}', function (Frankenstein $model) {
    return view('table', compact('model'));
})->name('view');

Route::get('/formster/{model}/edit', function (Frankenstein $model) {
    return view('form', compact('model'));
})->name('edit');

Route::put('/formster/{model}', function (Request $request, Frankenstein $model) {
    ActionHandler::update($request, $model)->save();

    return redirect()->back();
})->name('update');
