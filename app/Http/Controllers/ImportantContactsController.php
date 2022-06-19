<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\ImportantContact;
use App\Models\ImportantContactMedia;

use Illuminate\Http\Request;

class ImportantContactsController extends Controller
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

        $important_contacts = $community->important_contacts;

        return ResourceBuilder::collection($important_contacts);
    }

    public function show($id, $icid)
    {
        if (auth()->user()->access != 'admin' || 
                auth()->user()->access != 'community_leader') {
            return $this->return_error('not allowed to view community data', 'ERR');
        }

        $important_contact = ImportantContact::find($icid);

        if (!$important_contact){
            return $this->return_error('community data not found', 'ERR');
        } 

        if (auth()->user()->access != 'admin' &&
                auth()->user()->community->id != $important_contact->community->id){
            return $this->return_error('community data not in your community', 'ERR');
        }

        return new ResourceBuilder($important_contact);
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

        $community->important_contact()->create($fields);

        return response([
            'status' => 'success',
            'message' => 'community data created successfully',
        ], 201);

    }

    public function mediaStore(Request $request, $id, $icid)
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

        $important_contact = ImportantContact::find($icid);

        if (!$important_contact){
            return $this->return_error('community data not found', 'ERR');
        } 

        if (auth()->user()->access != 'admin' &&
                auth()->user()->community->id != $important_contact->community->id){
            return $this->return_error('community data not in your community', 'ERR');
        }

        $media = $request->file('media');
        $media_name = $id . "_" . $fields['position'] . "_important_contact_media." . $media->extension();
        $media_url = $media->storeAs('public/media/important_contact', $media_name); // Switch

        $important_contact_media = new ImportantContactMedia();
        $important_contact_media->name = $fields['name'];
        $important_contact_media->url = $media_url;

        $important_contact->important_contact_media()->save($important_contact_media);

        return response([
            'status' => 'success',
            'message' => 'media uploaded successfully',
            'post' => $important_contact_media 
        ], 201);
    }

    public function delete($id, $icid)
    {
        if (auth()->user()->access != 'admin' || 
                auth()->user()->access != 'community_leader') {
            return $this->return_error('not allowed to view community data', 'ERR');
        }

        $important_contact = ImportantContact::find($icid);

        if (!$important_contact){
            return $this->return_error('community data not found', 'ERR');
        } 

        if (auth()->user()->access != 'admin' &&
                auth()->user()->community->id != $important_contact->community->id){
            return $this->return_error('community data not in your community', 'ERR');
        }

        $important_contact->delete();

        return response([
            'status' => 'success',
            'message' => 'community data deleted successfully',
        ], 201);
    }

    public function update($id, $icid)
    {
        if (auth()->user()->access != 'admin' || 
                auth()->user()->access != 'community_leader') {
            return $this->return_error('not allowed to view community data', 'ERR');
        }

        $important_contact = ImportantContact::find($icid);

        if (!$important_contact){
            return $this->return_error('community data not found', 'ERR');
        } 

        if (auth()->user()->access != 'admin' &&
                auth()->user()->community->id != $important_contact->community->id){
            return $this->return_error('community data not in your community', 'ERR');
        }

        $important_contact->update($fields);

        return response([
            'status' => 'success',
            'message' => 'community data updated successfully',
        ], 201);
    }
}
