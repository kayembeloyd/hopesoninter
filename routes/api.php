<?php

use App\Http\Controllers\CommunityDataController;
use App\Http\Controllers\ImportantContactsController;
use App\Http\Controllers\CommunitiesController;
use App\Http\Controllers\ChatsController;
use App\Http\Controllers\MessagesController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\WebnarEventsController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

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

Route::middleware('auth:sanctum')->get('/communities/{id}/community-data', [CommunityDataController::class, 'index']);
Route::middleware('auth:sanctum')->get('/communities/{id}/community-data/{cdid}', [CommunityDataController::class, 'show']);
Route::middleware('auth:sanctum')->delete('/communities/{id}/community-data/{cdid}', [CommunityDataController::class, 'delete']);
Route::middleware('auth:sanctum')->put('/communities/{id}/community-data/{cdid}', [CommunityDataController::class, 'update']);
Route::middleware('auth:sanctum')->post('/communities/{id}/community-data', [CommunityDataController::class, 'create']);
Route::middleware('auth:sanctum')->post('/communities/{id}/community-data/{cdid}/media', [CommunityDataController::class, 'mediaStore']);

Route::middleware('auth:sanctum')->get('/communities/{id}/important-contacts', [ImportantContactsController::class, 'index']);
Route::middleware('auth:sanctum')->post('/communities/{id}/important-contacts', [ImportantContactsController::class, 'create']);
Route::middleware('auth:sanctum')->get('/communities/{id}/important-contacts/{icid}', [ImportantContactsController::class, 'show']);
Route::middleware('auth:sanctum')->put('/communities/{id}/important-contacts/{icid}', [ImportantContactsController::class, 'update']);
Route::middleware('auth:sanctum')->delete('/communities/{id}/important-contacts/{icid}', [ImportantContactsController::class, 'delete']);
Route::middleware('auth:sanctum')->post('/communities/{id}/important-contacts/{icid}/media', [ImportantContactsController::class, 'mediaStore']);

/* TESTED */ Route::get('/communities', [CommunitiesController::Class, 'index']); 
/* TESTED */ Route::get('/communities/{id}', [CommunitiesController::class, 'show']); 
/* TESTED */ Route::middleware('auth:sanctum')->post('/communities', [CommunitiesController::class, 'create']); 
/* TESTED */ Route::middleware('auth:sanctum')->post('/communities/{id}/media', [CommunitiesController::class, 'mediaStore']); 
/* TESTED */ Route::middleware('auth:sanctum')->delete('/communities/{id}', [CommunitiesController::class, 'delete']); 
/* TESTED */ Route::middleware('auth:sanctum')->put('/communities/{id}', [CommunitiesController::class, 'update']); 
/* TESTED */ Route::middleware('auth:sanctum')->get('/communities/{id}/users', [CommunitiesController::class, 'members']); 
/* TESTED */ Route::middleware('auth:sanctum')->put('/communities/{id}/users/{uid}', [CommunitiesController::class, 'addMember']); 
/* TESTED */ Route::middleware('auth:sanctum')->delete('/communities/{id}/users/{uid}', [CommunitiesController::class, 'removeMember']); 
/* TESTED */ Route::middleware('auth:sanctum')->get('/communities/{id}/user/request-membership', [CommunitiesController::class, 'requestMembership']);  
/* TESTED */ Route::middleware('auth:sanctum')->put('/communities/{id}/users/{uid}/accept-membership', [CommunitiesController::class, 'acceptMembership']); 
/* TESTED */ Route::middleware('auth:sanctum')->put('/communities/{id}/users/{uid}/reject-membership', [CommunitiesController::class, 'rejectMembership']); 
/* TESTED */ Route::middleware('auth:sanctum')->put('/communities/{id}/users/{uid}/assign-leadership', [CommunitiesController::class, 'assignLeadership']); 
/* TESTED */ Route::middleware('auth:sanctum')->put('/communities/{id}/users/{uid}/remove-leadership', [CommunitiesController::class, 'removeLeadership']); 

/* TESTED */ Route::middleware('auth:sanctum')->get('/user/chats', [ChatsController::class, 'index']);
/* TESTED */ Route::middleware('auth:sanctum')->get('/user/chats/{id}', [ChatsController::class, 'show']);

/* TESTED */ Route::middleware('auth:sanctum')->get('/user/chats/{id}/messages', [MessagesController::class, 'index']);
/* TESTED */ Route::middleware('auth:sanctum')->post('/user/chats/{id}/messages', [MessagesController::class, 'create']);
Route::middleware('auth:sanctum')->put('/user/chats/{id}/messages/{mid}', [MessagesController::class, 'update']);
/* TESTED */ Route::middleware('auth:sanctum')->get('/user/chats/{id}/messages/{mid}', [MessagesController::class, 'show']);

Route::middleware('auth:sanctum')->post('/meeting', [MeetingsController::class, 'create']);
Route::middleware('auth:sanctum')->get('/meeting/join/{name}', [MeetingsController::class, 'join']); 
Route::middleware('auth:sanctum')->get('/meeting/leave/{name}', [MeetingsController::class, 'leave']); 
Route::middleware('auth:sanctum')->get('/meeting/{id}', [MeetingsController::class, 'show']);
Route::middleware('auth:sanctum')->delete('/meeting/{id}', [MeetingsController::class, 'delete']);

Route::middleware('auth:sanctum')->get('/user/settings', [SettingsController::class, 'index']);
Route::middleware('auth:sanctum')->get('/user/settings/{id}', [SettingsController::class, 'show']);
Route::middleware('auth:sanctum')->post('/user/settings', [SettingsController::class, 'create']);
Route::middleware('auth:sanctum')->delete('/user/settings/{id}', [SettingsController::class, 'delete']);
Route::middleware('auth:sanctum')->put('/user/settings/{id}', [SettingsController::class, 'update']);

/* TESTED */ Route::post('/user/register', [AuthController::class, 'create']); 
/* TESTED */ Route::post('/user/login', [AuthController::class, 'login']); 
/* TESTED */ Route::middleware('auth:sanctum')->get('/user/logout', [AuthController::class, 'logout']);  
/* Social login */
Route::get('/user/login/{provider}', [AuthController::class, 'redirectToLoginProvider']);
Route::get('/user/login/{provider}/callback', [AuthController::class, 'handleLoginProviderCallback']);

/* TESTED */ Route::middleware('auth:sanctum')->get('/users', [UsersController::class, 'index']); 
/* TESTED */ Route::middleware('auth:sanctum')->get('/users/{id}', [UsersController::class, 'show']); 
/* TESTED */ Route::middleware('auth:sanctum')->put('/users/{id}', [UsersController::class, 'update']); 
/* TESTED */ Route::middleware('auth:sanctum')->post('/users/{id}/media', [UsersController::class, 'mediaStore']); 

/* TESTED */ Route::get('/posts', [PostsController::class, 'index']); 
/* TESTED */ Route::get('/posts/{id}', [PostsController::class, 'show']); 
/* TESTED */ Route::get('/posts/{id}/related', [PostsController::class, 'indexRelated']); 
/* TESTED */ Route::middleware('auth:sanctum')->post('/posts', [PostsController::class, 'store']); 
/* TESTED */ Route::middleware('auth:sanctum')->post('/posts/{id}/media', [PostsController::class, 'mediaStore']); 
/* TESTED */ Route::middleware('auth:sanctum')->put('/posts/{id}', [PostsController::class, 'update']); 
/* TESTED */ Route::middleware('auth:sanctum')->delete('/posts/{id}', [PostsController::class, 'delete']); 

// Note that an event is just the same as a webnar 
// Hence no need to make routes for webnars 
// Search whether i can use EventsController as controller name, 
    // Event as model name
    // e.t.c

/* TESTED */ Route::get('/webnar-events', [WebnarEventsController::class, 'index']); 
/* TESTED */ Route::get('/webnar-events/{id}', [WebnarEventsController::class, 'show']); 
/* TESTED */ Route::middleware('auth:sanctum')->post('/webnar-events', [WebnarEventsController::class, 'store']); 
/* TESTED */ Route::middleware('auth:sanctum')->post('/webnar-events/{id}/media', [WebnarEventsController::class, 'mediaStore']); 
/* TESTED */ Route::middleware('auth:sanctum')->delete('/webnar-events/{id}', [WebnarEventsController::class, 'delete']); 
/* TESTED */ Route::middleware('auth:sanctum')->put('/webnar-events/{id}', [WebnarEventsController::class, 'update']); 

Route::get('/run/migrate', function(){
    artisan::call('migrate');
    echo Artisan::output();
});

Route::get('/run/migrate-fresh', function(){
    artisan::call('migrate:fresh');
    echo Artisan::output();
});

Route::get('/run/storage-link', function(){
    artisan::call('storage:link');
    echo Artisan::output();
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
