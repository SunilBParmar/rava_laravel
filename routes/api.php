<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Dingo\Api\Exception\UnknownVersionException;
use Dingo\Api\Routing\Router;
use App\Http\Controllers\UsersController;
use App\Http\Middleware\JwtAuthentication;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
$api = app('Dingo\Api\Routing\Router');
$api->version('v1', [
    'middleware' => JwtAuthentication::class,
], function (Router $api) {
    $api->group(['prefix' => 'v1'], function ($api) { // Use this route group for v1

        // File Controller
        $api->post('upload', 'App\Api\V1\Controllers\FileController@upload');
        $api->post('move', 'App\Api\V1\Controllers\FileController@move');
        $api->delete('remove/{id}', 'App\Api\V1\Controllers\FileController@remove');
        $api->get('listing', 'App\Api\V1\Controllers\FileController@listing');
        $api->get('search/{search}', 'App\Api\V1\Controllers\FileController@search');
        $api->get('info/{id}', 'App\Api\V1\Controllers\FileController@info');
        // Tags Controller
        $api->post('tags/add', 'App\Api\V1\Controllers\TagsController@add');
        $api->put('tags/edit/{id}', 'App\Api\V1\Controllers\TagsController@edit');
        $api->delete('tags/remove/{id}', 'App\Api\V1\Controllers\TagsController@remove');
        $api->get('tags/listing', 'App\Api\V1\Controllers\TagsController@listing');
        $api->get('tags/search', 'App\Api\V1\Controllers\TagsController@search');
        // Users Controller
        $api->post('users/add', [UsersController::class, 'add']);
        $api->post('users/auth', [UsersController::class, 'authCustom']);
        $api->post('users/upload-image/{id}', [UsersController::class, 'uploadImage']);
        $api->patch('users/edit/{id}', [UsersController::class, 'edit']);
        $api->delete('users/remove/{id}', [UsersController::class, 'remove']);
        $api->get('users/listing', [UsersController::class, 'listing']);
        $api->get('users/search', [UsersController::class, 'search']);
        $api->get('users/info/{id}', [UsersController::class, 'info']);
        // Workouts Controller
        $api->post('workouts/add', 'App\Api\V1\Controllers\WorkoutsController@add');
        $api->post('workouts/upload-image/{id}', 'App\Api\V1\Controllers\WorkoutsController@uploadImage');
        $api->patch('workouts/edit/{id}', 'App\Api\V1\Controllers\WorkoutsController@edit');
        $api->delete('workouts/remove/{id}', 'App\Api\V1\Controllers\WorkoutsController@remove');
        $api->get('workouts/listing', 'App\Api\V1\Controllers\WorkoutsController@listing');
        $api->get('workouts/search', 'App\Api\V1\Controllers\WorkoutsController@search');
        $api->get('workouts/info/{id}', 'App\Api\V1\Controllers\WorkoutsController@info');
        // Activities Controller
        $api->post('activities/add', 'App\Api\V1\Controllers\ActivitiesController@add');
        $api->patch('activities/edit/{id}', 'App\Api\V1\Controllers\ActivitiesController@edit');
        $api->delete('activities/remove/{id}', 'App\Api\V1\Controllers\ActivitiesController@remove');
        $api->get('activities/listing', 'App\Api\V1\Controllers\ActivitiesController@listing');
        $api->get('activities/search', 'App\Api\V1\Controllers\ActivitiesController@search');
        $api->get('activities/info/{id}', 'App\Api\V1\Controllers\ActivitiesController@info');
        // Categories Controller
        $api->post('categories/add', 'App\Api\V1\Controllers\CategoriesController@add');
        $api->patch('categories/edit/{id}', 'App\Api\V1\Controllers\CategoriesController@edit');
        $api->delete('categories/remove/{id}', 'App\Api\V1\Controllers\CategoriesController@remove');
        $api->get('categories/listing', 'App\Api\V1\Controllers\CategoriesController@listing');
        $api->get('categories/search', 'App\Api\V1\Controllers\CategoriesController@search');
        // User Workouts Controller
        $api->post('user-workouts/add', 'App\Api\V1\Controllers\UserWorkoutsController@add');
        $api->patch('user-workouts/edit/{id}', 'App\Api\V1\Controllers\UserWorkoutsController@edit');
        $api->delete('user-workouts/remove/{id}', 'App\Api\V1\Controllers\UserWorkoutsController@remove');
        $api->get('user-workouts/listing', 'App\Api\V1\Controllers\UserWorkoutsController@listing');
        $api->get('user-workouts/info/{id}', 'App\Api\V1\Controllers\UserWorkoutsController@info');
        // User Activities Controller
        $api->patch('user-activities/edit/{id}', 'App\Api\V1\Controllers\UserActivitiesController@edit');
        $api->delete('user-activities/remove/{id}', 'App\Api\V1\Controllers\UserActivitiesController@remove');
        $api->get('user-activities/listing', 'App\Api\V1\Controllers\UserActivitiesController@listing');
        $api->get('user-activities/info/{id}', 'App\Api\V1\Controllers\UserActivitiesController@info');
        // Favorites Controller
        $api->post('favorites/add', 'App\Api\V1\Controllers\FavoritesController@add');
        $api->delete('favorites/remove/{id}', 'App\Api\V1\Controllers\FavoritesController@remove');
        $api->get('favorites/listing', 'App\Api\V1\Controllers\FavoritesController@listing');
        // Reports Controller
        $api->get('reports/listing', 'App\Api\V1\Controllers\ReportsController@listing');
        // Analytics Controller
        $api->get('analytics/listing', 'App\Api\V1\Controllers\AnalyticsController@listing');
        $api->get('analytics/info/{id}', 'App\Api\V1\Controllers\AnalyticsController@info');
        // Notifications
        $api->patch('notifications/edit/{id}', 'App\Api\V1\Controllers\NotificationsController@edit');
        $api->get('notifications/listing', 'App\Api\V1\Controllers\NotificationsController@listing');


        // Docs Controller
        $api->get('docs', 'App\Api\V1\Controllers\DocsController@index');
    });
});
