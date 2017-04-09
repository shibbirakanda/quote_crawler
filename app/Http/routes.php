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


Route::get('jso/crawler',['uses' => 'QuoteController@quotographed_crawler']);
//Route::get('jso/pcrawler',['uses' => 'QuoteController@quotationspage_crawler']);

Route::get('brainycrawler',['uses' => 'QuoteController@brainyquote_crawler']);
Route::get('jso/quote',['uses' => 'QuoteController@find_quotes']);
Route::get('jso/tag',['uses' => 'QuoteController@find_tags']);
Route::get('jso/tag/{name}',['uses' => 'QuoteController@find_quotes_by_tag']);


