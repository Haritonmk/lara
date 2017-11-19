<?php
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
//use App\Http\Controllers;

Route::auth();

Route::get('/',function(){
    return redirect('/login');
});
Route::get('/tasks', 'TaskController@index');
Route::post('/task', 'TaskController@store');
Route::delete('/task/{task}', 'TaskController@destroy');
Route::get('/learn/list-category', 'LearnController@listCategory');
Route::get('/learn/phrase', 'LearnController@phrase');
Route::get('/learn/articles', 'LearnController@articles');
Route::resource('learn', 'LearnController');
Route::resource('categories', 'CategoriesController');
Route::controller('words', 'WordController');
Route::resource('articles','ArticleController');
