<?php

use Illuminate\Support\Facades\Route;

Route::get('login',  'AuthController@showFormLogin')->name('auth.login');
Route::post('login', 'AuthController@postLogin')->name('auth.login.post');
Route::get('logout', 'AuthController@logout')->name('auth.logout');

Route::get('/', 'DashboardController@index')->name('dashboard');
Route::group(['prefix'=>'category/', 'as'=>'category.'], function(){
    Route::get('/', ['as' => 'index', 'uses' => 'CategoryController@index']);
    Route::get('/create', ['as' => 'create', 'uses' => 'CategoryController@create']);
    Route::post('/store', ['as' => 'store', 'uses' => 'CategoryController@store']);
    Route::get('/{id}/edit', ['as' => 'edit', 'uses' => 'CategoryController@edit']);
    Route::delete('/{id}', ['as' => 'destroy', 'uses' => 'CategoryController@destroy']);
    Route::get('test/{id}', ['as' => 'test', 'uses' => 'CategoryController@test']);
    Route::post('reorder', ['as' => 'reorder', 'uses' => 'CategoryController@reorder']);
    Route::post('/{id}', ['as' => 'update', 'uses' => 'CategoryController@update']);
});

Route::resource('new', 'NewController');
Route::resource('product', 'ProductController');
Route::resource('contact', 'ContactController')->only(['index', 'show', 'destroy']);
Route::post('contact/{id}/status', 'ContactController@updateStatus')->name('contact.status');
Route::post('tinymce_editor/upload', ['as' => 'tinymce_editor.upload', 'uses' => 'TinyMceEditorController@upload']);
