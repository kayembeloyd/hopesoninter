<?php

namespace App\Http\Controllers;
 
use Illuminate\Http\Request;

class SettingsController extends Controller
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
        $settings = auth()->user()->settings;
         
        return ResourceBuilder::collection($settings);
    }

    public function show($id)
    {
        $setting = auth()->user()->settings->where('id', $id)->first();

        if (!$setting){
            return $this->return_error('setting not found', 'ERR');
        }

        return new ResourceBuilder($setting);
    }

    public function create()
    {
        $fields = $request->validate([
            'key' => 'required|string',
            'value' => '',
        ]);

        $setting = auth()->user()->settings->create($fields);

        return response([
            'status' => 'success',
            'message' => 'setting created successfully',
            'setting' => $setting 
        ], 201);
    }

    public function delete($id)
    {
        $setting = auth()->user()->settings->where('id', $id)->first();

        if (!$setting){
            return $this->return_error('setting not found', 'ERR');
        }

        $setting->delete();

        return response([
            'status' => 'success',
            'message' => 'setting deleted successfully'
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $fields = $request->validate([
            'key' => 'required|string',
            'value' => '',
        ]);

        $setting = auth()->user()->settings->where('id', $id)->first();

        if (!$setting){
            return $this->return_error('setting not found', 'ERR');
        }

        $setting->update($fields);

        return response([
            'status' => 'success',
            'message' => 'setting updated successfully'
        ], 201);
    }
}
