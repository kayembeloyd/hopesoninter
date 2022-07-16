<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

use App\Models\Post;
use App\Models\Community;
use App\Models\PostMedia;

use App\Http\Resources\ResourceBuilder;

use Illuminate\Http\Request;

class PostsController extends Controller
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
        if ($request->has('community_id')){
            $posts = Post::where('community_id', $request->community_id);
        } else {
            $posts = Post::orderBy('created_at', 'DESC')->paginate(5);
        }

        
        foreach($posts as $post){
            $post->push($post->post_media);
        }

        return ResourceBuilder::collection($posts);
    }

    public function indexRelated($id)
    {
        $post = Post::find($id);

        if (!$post){
            return $this->return_error('post not found', 'ERR');
        }

        $posts = Post::where('title', 'like', "%$post->title%")->get()->except($post->id);

        return ResourceBuilder::collection($posts);
    }

    public function show($id)
    {
        $post = Post::find($id);

        if (!$post){
            return $this->return_error('post not found', 'ERR');
        }

        $post->push($post->post_media);

        return new ResourceBuilder($post);
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'title' => 'required|string',
            'short_description' => '',
            'long_description' => '',
            'community_id' => ''
        ]);

        if (auth()->user()->access == 'user'){
            return $this->return_error('you are not allowed to post', 'ERR');
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

        $post = auth()->user()->posts()->create($fields);
        $post->community_id = $community->id;
        $post->save(); 

        return response([
            'status' => 'success',
            'message' => 'post created successfully',
            'post' => $post 
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

        $post = Post::find($id);

        if (!$post){
            return $this->return_error('post not found', 'ERR');
        }

        if (auth()->user()->access == 'community_leader' && 
                auth()->user()->community->id != $post->community->id){
            return $this->return_error('Post not in your community', 'ERR');
        }

        /* ONLINE */
        $media_name = $id . "_" . $fields['position'] . "_post_media." . "jpeg";
        $media_url = "storage/media/posts/" . $media_name;
        $media_url = Storage::disk('s3')->put('/storage/media/posts/' . $media_name, base64_decode($fields['media']));
        $media_url = Storage::disk('s3')->url('/storage/media/posts/' . $media_name);

        /* LOCAL
        $media = $request->file('media');
        $media_name = $id . "_" . $fields['position'] . "_post_media." . $media->extension();
        $media_url = $media->storeAs('public/media/posts', $media_name); // Switch
        */

        $post_media = new PostMedia();
        $post_media->name = $fields['name'];
        $post_media->url = $media_url;

        $post->post_media()->save($post_media);

        return response([
            'status' => 'success',
            'message' => 'media uploaded successfully',
            'post_media' => $post_media 
        ], 201);
    }

    public function update(Request $request, $id)
    {
        if (auth()->user()->access != 'admin'){
            if (auth()->user()->access != 'community_leader'){
                return $this->return_error('you are not allowed to update', 'ERR');
            }
        }

        $post = Post::find($id);

        if (!$post){
            return $this->return_error('post not found', 'ERR');
        }

        if (auth()->user()->access == 'community_leader' && 
                auth()->user()->community->id != $post->community->id){
            return $this->return_error('post not in your community', 'ERR');
        }

        $fields = $request->validate([
            'title' => 'required|string',
            'short_description' => '',
            'long_description' => '',
            'community_id' => ''
        ]);

        $post->update($fields);

        return response([
            'status' => 'success',
            'message' => 'post updated successfully',
            'post' => $post 
        ], 201);
    }

    public function delete($id)
    {
        if (auth()->user()->access != 'admin'){
            if (auth()->user()->access != 'community_leader'){
                return $this->return_error('you are not allowed to update', 'ERR');
            }
        }

        $post = Post::find($id);

        if (!$post){
            return $this->return_error('post not found', 'ERR');
        }

        if (auth()->user()->access == 'community_leader' && 
                auth()->user()->community->id != $post->community->id){
            return $this->return_error('Post not in your community', 'ERR');
        }

        $post->delete();

        return response([
            'status' => 'success',
            'message' => 'post deleted successfully',
        ], 201);
    }
}
