<?php

namespace App\Http\Controllers;

use App\Models\WebnarEvent;
use App\Models\Community;
use App\Models\WebnarEventMedia;

use App\Http\Resources\ResourceBuilder;

use Illuminate\Http\Request;

class WebnarEventsController extends Controller
{
    private function return_error($error, $ref)
    {
        return response([
            'status' => 'error',
            'ref' => $ref,
            'message' => $error
        ], 201);
    }
    
    public function index()
    {
        $webnar_events = WebnarEvent::orderBy('created_at', 'DESC')->paginate(5);
        
        foreach($webnar_events as $webnar_event){
            $webnar_event->push($webnar_event->webnar_event_media);
        }

        return ResourceBuilder::collection($webnar_events);
    }

    public function show($id)
    {
        $webnar_event = WebnarEvent::find($id);

        if (!$webnar_event){
            return $this->return_error('webnar event not found', 'ERR');
        }

        $webnar_event->push($webnar_event->webnar_event_media);

        return new ResourceBuilder($webnar_event);
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string',
            'description' => '',
            'on_date_time' => '',
            'community_id' => ''
        ]);

        if (auth()->user()->access == 'user'){
            return $this->return_error('you are not allowed to webnar_event', 'ERR');
        }

        $community = auth()->user()->community;

        if (auth()->user()->access == 'admin'){
            $community = Community::find($fields['community_id']);

            if (!$community){
                return $this->return_error('community not found', 'ERR');
            }
        }

        if (!$community){
            return $this->return_error('you dont belong to any community', 'ERR');
        }

        $webnar_event = auth()->user()->webnar_events()->create($fields);
        $webnar_event->community_id = $community->id;
        $webnar_event->save(); 

        return response([
            'status' => 'success',
            'message' => 'webnar event created successfully',
            'webnar_event' => $webnar_event 
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

        $webnar_event = WebnarEvent::find($id);

        if (!$webnar_event){
            return $this->return_error('webnar event not found', 'ERR');
        }

        if (auth()->user()->access == 'community_leader' && 
                auth()->user()->community->id != $webnar_event->community->id){
            return $this->return_error('webnar event not in your community', 'ERR');
        }

        $media = $request->file('media');
        $media_name = $id . "_" . $fields['position'] . "_webnar_event_media." . $media->extension();
        $media_url = $media->storeAs('public/media/webnar_events', $media_name); // Switch

        $webnar_event_media = new WebnarEventMedia();
        $webnar_event_media->name = $fields['name'];
        $webnar_event_media->url = $media_url;

        $webnar_event->webnar_event_media()->save($webnar_event_media);

        return response([
            'status' => 'success',
            'message' => 'media uploaded successfully',
            'webnar_event' => $webnar_event_media 
        ], 201);
    }

    public function update(Request $request, $id)
    {
        if (auth()->user()->access != 'admin'){
            if (auth()->user()->access != 'community_leader'){
                return $this->return_error('you are not allowed to update', 'ERR');
            }
        }

        $webnar_event = WebnarEvent::find($id);

        if (!$webnar_event){
            return $this->return_error('webnar event not found', 'ERR');
        }

        if (auth()->user()->access == 'community_leader' && 
                auth()->user()->community->id != $webnar_event->community->id){
            return $this->return_error('webnar event not in your community', 'ERR');
        }

        $fields = $request->validate([
            'name' => 'required|string',
            'description' => '',
            'on_date_time' => ''
        ]);

        $webnar_event->update($fields);

        return response([
            'status' => 'success',
            'message' => 'webnar event updated successfully',
            'webnar_event' => $webnar_event 
        ], 201);
    }

    public function delete($id)
    {
        if (auth()->user()->access != 'admin'){
            if (auth()->user()->access != 'community_leader'){
                return $this->return_error('you are not allowed to update', 'ERR');
            }
        }

        $webnar_event = WebnarEvent::find($id);

        if (!$webnar_event){
            return $this->return_error('webnar event not found', 'ERR');
        }

        if (auth()->user()->access == 'community_leader' && 
                auth()->user()->community->id != $webnar_event->community->id){
            return $this->return_error('webnar event not in your community', 'ERR');
        }

        $webnar_event->delete();

        return response([
            'status' => 'success',
            'message' => 'webnar event deleted successfully',
        ], 201);
    }
}
