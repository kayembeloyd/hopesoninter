<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

use App\Models\Chat;
use App\Models\Community;
use App\Models\CommunityMedia;
use App\Models\Forum;
use App\Models\User;

use App\Http\Resources\ResourceBuilder; 

use Illuminate\Http\Request;

class CommunitiesController extends Controller
{
    private function return_error($error, $ref)
    {
        return response([
            'status' => 'error',
            'ref' => $ref,
            'message' => $error
        ], 201);
    }

    private function create_chat($uid_1, $uid_2)
    {
        $chat_check = Chat::where('owner_id', $uid_1)
                        ->where('chat_with_id', $uid_2)->first();
        
        if (!$chat_check){
            $chat = Chat::create([
                'type' => 'one-to-one',
                'owner_id' => $uid_1,
                'chat_with_id' => $uid_2
            ]);
        }
    }

    private function delete_chat($uid_1, $uid_2)
    {
        $chat_check = Chat::where('owner_id', $uid_1)
                        ->where('chat_with_id', $uid_2)->first();

        if ($chat_check){
            $chat_check->delete();
        }
    }

    private function create_chats($user)
    {
        $community = $user->community;

        $community_users = $community->users->except($user->id);

        foreach($community_users as $community_user){
            $this->create_chat($community_user->id, $user->id);
            $this->create_chat($user->id, $community_user->id);
        }
    }

    private function delete_chats($user)
    {
        $community = $user->community;

        $community_users = $community->users->except($user->id);

        foreach($community_users as $community_user){ 
            $this->delete_chat($community_user->id, $user->id);
            $this->delete_chat($user->id, $community_user->id);
        }
    }
    
    public function index()
    {
        $communities = Community::orderBy('created_at', 'DESC')->paginate(5);
        
        foreach($communities as $community){
            $community->push($community->community_media);
        }

        return ResourceBuilder::collection($communities);
    }

    public function show($id)
    {
        $community = Community::find($id);

        if (!$community){
            return $this->return_error('community not found', 'ERR');
        }

        $community->push($community->community_media);

        return new ResourceBuilder($community);
    }

    public function create(Request $request)
    { 
        $fields = $request->validate([
            'name' => 'required|string',
            'location' => ''
        ]);

        if (auth()->user()->access != 'admin'){
            return $this->return_error('you are not allowed to create a community', 'ERR');
        }

        $community = Community::create([
            'name' => $fields['name'],
            'location' => $fields['location']
        ]);

        $forum = Forum::create([
            'name' => $community->name
        ]);

        $chat = Chat::create([
            'type' => 'forum'
        ]);

        $forum->chat()->save($chat);
        $forum->users()->save(auth()->user());

        $community->forum()->associate($forum);
        $community->save();
        
        return response([
            'status' => 'success',
            'message' => 'community, forum, and chat created successfully',
            'community' => $community
        ], 201);
    }

    public function mediaStore(Request $request, $id)
    {
        $fields = $request->validate([
            'name' => '',
            'media' => 'required',
            'position' => 'required'
        ]);

        if (auth()->user()->access != 'admin'){
            if (auth()->user()->access != 'community_leader'){
                return $this->return_error('you are not allowed to store photo', 'ERR');
            }
        }

        $community = Community::find($id);

        if (!$community){
            return $this->return_error('community not found', 'ERR');
        }

        if (auth()->user()->access == 'community_leader' && 
                auth()->user()->community->id != $community->id){
            return $this->return_error('This is not your community', 'ERR');
        }

        /* ONLINE */
        $media_name = $id . "_" . $fields['position'] . "_community_media." . "jpeg";
        $media_url = "storage/media/communities/" . $media_name;
        $media_url = Storage::disk('s3')->put('/storage/media/communities/' . $media_name, base64_decode($fields['media']));
        $media_url = Storage::disk('s3')->url('/storage/media/communities/' . $media_name);

        /* LOCAL
        $media = $request->file('media');
        $media_name = $id . "_" . $fields['position'] . "_community_media." . $media->extension();
        $media_url = $media->storeAs('public/media/communities', $media_name); // Switch
        */

        $community_media = new CommunityMedia();
        $community_media->name = $fields['name'];
        $community_media->url = $media_url;

        $community->community_media()->save($community_media);

        return response([
            'status' => 'success',
            'message' => 'media uploaded successfully',
            'post' => $community_media 
        ], 201);
    }

    public function delete($id)
    {
        if (auth()->user()->access != 'admin'){
            return $this->return_error('you are not allowed to delete a community', 'ERR');
        }

        $community = Community::find($id);

        if (!$community){
            return $this->return_error('community not found', 'ERR');
        }

        $forum = $community->forum;

        $chat = $forum->chat;

        $messages = $chat->messages;

        if ($messages){
            foreach($messages as $message){
                $message->delete();
            }
        }

        $chat->delete();
        $forum->delete();
        $community->delete();

        return response([
            'status' => 'success',
            'message' => 'community, forum, chat and messages deleted successfully',
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $fields = $request->validate([
            'name' => '',
            'location' => ''
        ]);

        if (auth()->user()->access != 'admin'){
            return $this->return_error('you are not allowed to update a community', 'ERR');
        }

        $community = Community::find($id);

        if (!$community){
            return $this->return_error('community not found', 'ERR');
        }

        $community->update($fields);

        $community->forum->update(['name' => $community->name]);

        return response([
            'status' => 'success',
            'message' => 'update community and forum successfully',
        ], 201);
    }

    public function members($id)
    { 
        if (auth()->user()->access != 'admin'){ 
            if (auth()->user()->access != 'community_leader'){
                return $this->return_error('you are not allowed to get members of this community', 'ERR');
            }
        }

        $community = auth()->user()->community;
        
        if (auth()->user()->access == 'admin'){
            $community = Community::find($id);

            if (!$community){
                return $this->return_error('community not found', 'ERR');
            }
        }

        if (!$community){
            return $this->return_error('community not found', 'ERR');
        }

        $members = $community->users;

        return ResourceBuilder::collection($members);
    }

    public function addMember($id, $uid)
    {
        if (auth()->user()->access != 'admin'){ 
            if (auth()->user()->access != 'community_leader'){
                return $this->return_error('you are not allowed to get members of this community', 'ERR');
            }
        }

        $user = User::find($uid);

        if (!$user){
            return $this->return_error('user not found', 'ERR');
        }

        $community = Community::find($id);

        if (!$community){
            return $this->return_error('community not found', 'ERR');
        }

        if (auth()->user()->access == 'community_leader'){
            if (auth()->user()->community == null){
                return $this->return_error('user is a community leader without a community', 'ERR');
            }

            if ($community->id != auth()->user->community->id){
                return $this->return_error('this is not your community', 'ERR');
            }
        }

        if ($user->requesting_membership_community != null){
            if (auth()->user()->access != 'admin'){
                return $this->return_error('user is requesting for another community membership', 'ERR');
            }
        }

        $user->requesting_membership_community()->associate(null);
        $community->users()->save($user);
        $community->forum->users()->save($user); 

        $this->create_chats($user);

        return response([
            'status' => 'success',
            'message' => 'member added successfully',
        ], 201);
    }

    public function removeMember($id, $uid)
    {
        if (auth()->user()->access != 'admin'){
            if (auth()->user()->access != 'community_leader'){
                return $this->return_error('you are not allowed to reject this membership request', 'ERR');
            }
        }

        $user = User::find($uid);

        if (!$user){
            return $this->return_error('user not found', 'ERR');
        }

        $community = $user->community;

        if (auth()->user()->access != 'admin'){
            if (auth()->user()->community->id != $community->id){
                return $this->return_error('user not in your community', 'ERR');
            }
        }
         
        $this->delete_chats($user);
        
        $user->community()->associate(null);
        $user->save();

        $forum = user()->community->forum;
        $user->forums()->detach($forum->id);
        return response([
            'status' => 'success',
            'message' => 'removed member successfully',
        ], 201);
    }

    public function requestMembership($id)
    {
        $community = Community::find($id);

        if (!$community){
            return $this->return_error('community not found', 'ERR');
        }

        if (auth()->user()->community){
            if (auth()->user()->community->id == $community->id){
                return $this->return_error('already in this community', 'ERR');
            }
        }

        if (auth()->user()->access == 'admin'){
            return $this->return_error('admins are not allowed to belong to a community');
        }

        $community->membership_requestees()->save(auth()->user());

        return response([
            'status' => 'success',
            'message' => 'requested membership successfully',
        ], 201);
    } 
    
    public function rejectMembership($id, $uid)
    {
        if (auth()->user()->access != 'admin'){
            if (auth()->user()->access != 'community_leader'){
                return $this->return_error('you are not allowed to reject this membership request', 'ERR');
            }
        }

        $user = User::find($uid);

        if (!$user){
            return $this->return_error('user not found', 'ERR');
        }

        $requesting_membership_community = $user->requesting_membership_community;

        if (!$requesting_membership_community){
            return $this->return_error('user is not requesting a membership', 'ERR');
        }

        if (auth()->user()->community){
            if ($requesting_membership_community->id == auth()->user()->community->id){
                auth()->user()->community->membership_requestees()->remove($user);
            } else {
                return $this->return_error('user is not requesting an membership in your community', 'ERR');
            }
        } else {
            $user->requesting_membership_community()->associate(null);
            $user->save();
        }


        return response([
            'status' => 'success',
            'message' => 'rejected membership successfully',
        ], 201);
    }

    public function acceptMembership($id, $uid)
    {
        if (auth()->user()->access != 'admin'){
            if (auth()->user()->access != 'community_leader'){
                return $this->return_error('you are not allowed to accept this membership request', 'ERR');
            }
        }

        $user = User::find($uid);

        if (!$user){
            return $this->return_error('user not found', 'ERR');
        }

        $requesting_membership_community = $user->requesting_membership_community;

        if (!$requesting_membership_community){
            return $this->return_error('user is not requesting a membership', 'ERR');
        }

        if (auth()->user()->community){
            if ($requesting_membership_community->id != auth()->user()->community->id){
                return $this->return_error('user is not requesting an membership in your community', 'ERR');
            }

            $user->requesting_membership_community()->associate(null);
            auth()->user()->community->users()->save($user);
            auth()->user()->community->forum->users()->save($user); 
        } else {
            $user->requesting_membership_community()->associate(null);
            $requesting_membership_community->users()->save($user);
            $requesting_membership_community->forum->users()->save($user); 
        }

        $this->create_chats($user);

        return response([
            'status' => 'success',
            'message' => 'accepted membership successfully',
        ], 201);
    }

    public function removeLeadership($id, $uid)
    {
        if (auth()->user()->access != 'admin'){
            return $this->return_error('you are not allowed to remove leadership', 'ERR');
        }

        $user = User::find($uid);

        if (!$user){
            return $this->return_error('user not found', 'ERR');
        }

        $community = $user->community;

        if (!$community){
            return $this->return_error('user not in a community', 'ERR');
        }

        $community_users = $community->users;

        foreach ($community_users as $community_user){
            $community_user->update(['access' => 'user']);
        }

        $other_communities = Community::all()->except($community->id);

        foreach($other_communities as $other_community){
            if ($other_community->leader == null){
                continue;
            }

            $this->delete_chat($other_community->leader->id, $user->id);
            $this->delete_chat($user->id, $other_community->leader->id);
        }

        return response([
            'status' => 'success',
            'message' => 'removed leadership successfully',
        ], 201); 
    }

    public function assignLeadership($id, $uid)
    {
        if (auth()->user()->access != 'admin'){
            return $this->return_error('you are not allowed to assign leadership', 'ERR');
        }

        $user = User::find($uid);

        if (!$user){
            return $this->return_error('user not found', 'ERR');
        }

        $community = $user->community;

        if (!$community){
            return $this->return_error('user not in a community', 'ERR');
        }

        $community_users = $community->users;

        foreach ($community_users as $community_user){
            $community_user->update(['access' => 'user']);
        }

        $user->update(['access' => 'community_leader']);

        $other_communities = Community::all()->except($community->id);

        foreach($other_communities as $other_community){
            if ($other_community->leader == null){
                continue;
            }

            $this->create_chat($other_community->leader->id, $user->id);
            $this->create_chat($user->id, $other_community->leader->id);
        }

        return response([
            'status' => 'success',
            'message' => 'assigned leadership successfully',
        ], 201); 
    }
}
