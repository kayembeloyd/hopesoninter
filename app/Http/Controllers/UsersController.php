<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserMedia;

use App\Http\Resources\ResourceBuilder; 

use Illuminate\Http\Request;

class UsersController extends Controller
{
    private function return_error($error, $ref)
    {
        return response([
            'status' => 'error',
            'ref' => $ref,
            'message' => $error
        ], 201);
    }

    public function index(Request $request)
    {
        if (auth()->user()->access != 'admin'){
            if (auth()->user()->access != 'community_leader'){
                return $this->return_error('you are not allowed get users', 'ERR');
            }
        }

        if($request->has('all')){
            if($request['all'] == 'yes'){
                $users = User::orderBy('created_at', 'DESC')->paginate(5);
                
                foreach($users as $user){
                    $user->push($user->user_media);
                }

                return ResourceBuilder::collection($users);
            }
        }

        $users_without_community = User::where('community_id', null)
                                        ->orWhere('community_id', -1)->get();
        foreach($users_without_community as $user_without_community){
            $user_without_community->push($user_without_community->user_media);
        }
        return ResourceBuilder::collection($users_without_community);
    }

    public function update(Request $request, $id)
    {
        $fields = $request->validate([
            'name' => '',
            'phone_numbers' => ''
        ]);

        $user = auth()->user();

        if (auth()->user()->access == 'admin'){
            $user = User::find(1);

            if (!$user){
                $user = auth()->user();
            }
        }

        $user->update($fields);

        return response([
            'status' => 'success',
            'message' => 'user updated successfully',
        ], 201);
    } 

    public function mediaStore(Request $request, $id)
    {
        $fields = $request->validate([
            'name' => '',
            'media' => 'required',
            'position' => 'required'
        ]);

        $user = auth()->user();

        if (auth()->user()->access == 'admin'){
            $user = User::find($id);
            
            if (!$user){
                return $this->return_error('user not found', 'ERR');
            }
        }

        $media = $request->file('media');
        $media_name = $id . "_" . $fields['position'] . "_user_media." . $media->extension();
        $media_url = $media->storeAs('public/media/users', $media_name); // Switch

        $user_media = new UserMedia();
        $user_media->name = $fields['name'];
        $user_media->url = $media_url;

        $user->user_media()->save($user_media);

        return response([
            'status' => 'success',
            'message' => 'media uploaded successfully',
            'post' => $user_media 
        ], 201);
    }

    public function show($id)
    {
        $user = User::find($id);

        if (!$user){
            return $this->return_error('user not found', 'ERR');
        }

        if (auth()->user()->access != 'admin'){
            if (auth()->user()->access == 'community_leader'){
                if ($user->community == null){
                    return new ResourceBuilder($user);
                }
            }

            if (auth()->user()->community != null){
                if ($user->community->id == auth()->user()->community->id){
                    return new ResourceBuilder($user);
                }
            }

            return $this->return_error('you are not allowed to view user', 'ERR');
        }

        return new ResourceBuilder($user);
    }
}
