<?php

namespace App\Http\Controllers;

use App\Models\Chat;

use App\Http\Resources\ResourceBuilder; 

use Illuminate\Http\Request;

class ChatsController extends Controller
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
        $chats = array();

        $forums = auth()->user()->forums;
        foreach($forums as $forum){
            $forum_chat = $forum->chat;

            $forum_chat->push($forum_chat->forum);
            $forum_chat->push($forum_chat->last_message);

            array_push($chats, $forum_chat);
        }

        $user_chats = auth()->user()->chats;
        foreach($user_chats as $user_chat){

            $user_chat->push($user_chat->chat_with_user);
            $user_chat->push($user_chat->last_message);

            array_push($chats, $user_chat);
        }
 
        return ResourceBuilder::collection($chats);
    }

    public function show($id)
    {
        $chat = Chat::find($id);

        if (!$chat){
            return $this->return_error('chat does not exist', 'ERR');
        }

        if (!auth()->user()->chats->contains($chat)){
            return $this->return_error('this is not your chat', 'ERR');
        }

        if ($chat->type == 'one-to-one'){
            $chat->push($chat->chat_with_user);
        } else if ($chat->type == 'forum'){
            $chat->push($chat->forum);
        }

        $chat->push($chat->last_message);

        return new ResourceBuilder($chat);
    }
}
