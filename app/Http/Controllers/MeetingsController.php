<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\Community;

use Illuminate\Http\Request;

class MeetingsController extends Controller
{
    private function return_error($error, $ref)
    {
        return response([
            'status' => 'error',
            'ref' => $ref,
            'message' => $error
        ], 201);
    }
    
    public function join($name)
    {
        $meeting = Meeting::where('name', $name)->first();

        if (!$meeting){
            return $this->return_error('meeting not found', 'ERR');
        }

        if ($meeting->only_community_members){
            if (auth()->user()->community->id != 
                    $meeting->creater->community->id){
                return $this->return_error('you dont belong to this community', 'ERR');
            }
        }

        $meeting->attendees()->save(auth()->user()); 
    }

    public function leave($name)
    {
        $meeting = Meeting::where('name', $name)->first();

        if (!$meeting){
            return $this->return_error('meeting not found', 'ERR');
        }

        if (!$meeting->attendees->contains(auth()->user())){
            return $this->return_error('you dont belong to this meeting', 'ERR');
        }

        $meeting->attendees()->delete(auth()->user()); 
    }

    public function show($name)
    {
        $meeting = Meeting::where('name', $name)->first();

        if (!$meeting){
            return $this->return_error('meeting not found', 'ERR');
        }

        if (!$meeting->attendees->contains(auth()->user())){
            return $this->return_error('you dont belong to this meeting', 'ERR');
        }

        return new ResourceBuilder($meeting);
    }

    public function create(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string',
            'only_community_members' => '',
            'community_id' => ''
        ]);

        if (auth()->user()->access == 'user'){
            return $this->return_error('you are not allowed to create a meeting', 'ERR');
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

        $meeting = Meeting::create($fields);
        $meeting->creator()->save(auth()->user());
        $meeting->attendees()->save(auth()->user()); 

        return response([
            'status' => 'success',
            'message' => 'meeting created successfully',
            'meeting' => $meeting 
        ], 201);
    }

    public function delete()
    {
        $meeting = Meeting::where('name', $name)->first();

        if (!$meeting){
            return $this->return_error('meeting not found', 'ERR');
        }

        if (!$meeting->attendees->contains(auth()->user())){
            if ($meeting->creator->id != auth()->user()->id){
                return $this->return_error('you dont belong to this meeting', 'ERR');
            }
        }

        $meeting_attendees = $meeting->attendees;
        foreach($meeting_attendees as $attendee){
            $meeting->attendees()->delete($attendee); 
        }

        $meeting->delete();

        return response([
            'status' => 'success',
            'message' => 'meeting deleted successfully',
        ], 201);
    }
}
