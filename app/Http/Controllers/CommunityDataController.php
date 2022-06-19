<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\CommunityData;
use App\Models\CommunityDataMedia;

use Illuminate\Http\Request;

class CommunityDataController extends Controller
{
    private function return_error($error, $ref)
    {
        return response([
            'status' => 'error',
            'ref' => $ref,
            'message' => $error
        ], 201);
    }
    
    public function index($id)
    {
        if (auth()->user()->access != 'admin' || 
                auth()->user()->access != 'community_leader') {
            return $this->return_error('not allowed to view community data', 'ERR');
        }

        $community = auth()->user()->community;

        if (auth()->user()->access = 'admin'){
            $community = Community::find($id);

            if (!$community){
                return $this->return_error('community not found', 'ERR');
            }
        }

        $community_data = $community->community_data;

        return ResourceBuilder::collection($community_data);
    }

    public function show($id, $cdid)
    {
        if (auth()->user()->access != 'admin' || 
                auth()->user()->access != 'community_leader') {
            return $this->return_error('not allowed to view community data', 'ERR');
        }

        $community_data = CommunityData::find($cdid);

        if (!$community_data){
            return $this->return_error('community data not found', 'ERR');
        } 

        if (auth()->user()->access != 'admin' &&
                auth()->user()->community->id != $community_data->community->id){
            return $this->return_error('community data not in your community', 'ERR');
        }

        return new ResourceBuilder($community_data);
    }

    public function create(Request $request, $id)
    {
        $fields = $request->validate([
            'name' => '',
            'category' => '',
            'location' => ''
        ]);

        if (auth()->user()->access != 'admin' || 
                auth()->user()->access != 'community_leader') {
            return $this->return_error('not allowed to view community data', 'ERR');
        }

        $community = auth()->user()->community;

        if (auth()->user()->access = 'admin'){
            $community = Community::find($id);

            if (!$community){
                return $this->return_error('community not found', 'ERR');
            }
        }

        if(!$community){
            return $this->return_error('community not found', 'ERR');
        }

        $community->community_data()->create($fields);

        return response([
            'status' => 'success',
            'message' => 'community data created successfully',
        ], 201);

    }

    public function mediaStore(Request $request, $id, $cdid)
    {
        $fields = $request->validate([
            'name' => '',
            'media' => 'required',
            'position' => 'required'
        ]);

        if (auth()->user()->access != 'admin' || 
                auth()->user()->access != 'community_leader') {
            return $this->return_error('not allowed to view community data', 'ERR');
        }

        $community_data = CommunityData::find($cdid);

        if (!$community_data){
            return $this->return_error('community data not found', 'ERR');
        } 

        if (auth()->user()->access != 'admin' &&
                auth()->user()->community->id != $community_data->community->id){
            return $this->return_error('community data not in your community', 'ERR');
        }

        $media = $request->file('media');
        $media_name = $id . "_" . $fields['position'] . "_community_data_media." . $media->extension();
        $media_url = $media->storeAs('public/media/community_data', $media_name); // Switch

        $community_data_media = new CommunityDataMedia();
        $community_data_media->name = $fields['name'];
        $community_data_media->url = $media_url;

        $community_data->community_data_media()->save($community_data_media);

        return response([
            'status' => 'success',
            'message' => 'media uploaded successfully',
            'post' => $community_data_media 
        ], 201);
    }

    public function delete($id, $cdid)
    {
        if (auth()->user()->access != 'admin' || 
                auth()->user()->access != 'community_leader') {
            return $this->return_error('not allowed to view community data', 'ERR');
        }

        $community_data = CommunityData::find($cdid);

        if (!$community_data){
            return $this->return_error('community data not found', 'ERR');
        } 

        if (auth()->user()->access != 'admin' &&
                auth()->user()->community->id != $community_data->community->id){
            return $this->return_error('community data not in your community', 'ERR');
        }

        $community_data->delete();

        return response([
            'status' => 'success',
            'message' => 'community data deleted successfully',
        ], 201);
    }

    public function update(Request $request, $id, $cdid)
    {
        if (auth()->user()->access != 'admin' || 
                auth()->user()->access != 'community_leader') {
            return $this->return_error('not allowed to view community data', 'ERR');
        }

        $community_data = CommunityData::find($cdid);

        if (!$community_data){
            return $this->return_error('community data not found', 'ERR');
        } 

        if (auth()->user()->access != 'admin' &&
                auth()->user()->community->id != $community_data->community->id){
            return $this->return_error('community data not in your community', 'ERR');
        }

        $community_data->update($fields);

        return response([
            'status' => 'success',
            'message' => 'community data updated successfully',
        ], 201);
    }
}
