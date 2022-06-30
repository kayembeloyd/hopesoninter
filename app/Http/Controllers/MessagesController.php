<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;

use App\Events\SendMessageEvent;

use App\Http\Resources\ResourceBuilder; 

use Illuminate\Http\Request;

class MessagesController extends Controller
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
        $chat = Chat::find($id);

        if (!$chat){
            return $this->return_error('chat not found', 'ERR');
        }

        if (!auth()->user()->chats->contains($chat)){
            return $this->return_error('chat not yours', 'ERR');
        }

        $messages = $chat->messages;

        return ResourceBuilder::collection($messages);
    }

    public function create(Request $request, $id)
    {
        $fields = $request->validate([
            'type'=> 'required|string',
            'content' => 'required|string'
        ]);

        $chat = Chat::find($id);

        if (!$chat){
            return $this->return_error('chat not found', 'ERR');
        }

        if (!auth()->user()->chats->contains($chat)){
            return $this->return_error('chat not yours', 'ERR');
        }

        if ($chat->type == 'one-to-one'){
            $fields['to_uid'] = $chat->chat_with_id;
        } else if ($chat->type == 'forum'){
            $fields['to_forum_id'] = $chat->forum_id;
        }

        $fields['from_uid'] = auth()->user()->id;
        $fields['status'] = 'sent';

        $message = Message::create($fields);
        $message->chat()->associate($chat->id);
        $message->save();

        $message2 = Message::create($fields);
        $chat2 = $chat->chat_with_user->chats->where('chat_with_id', auth()->user()->id)->first();
        $message2->chat()->associate($chat2->id);
        $message2->save();

        // Make event
        event(new SendMessageEvent($message));
        event(new SendChatMessageEvent($message));

        return response([
            'status' => 'success',
            'message' => 'sent message successfully',
        ], 201);
    }

    public function update(Request $request, $id, $mid)
    {
        $fields = $request->validate([
            'status' => ''
        ]);

        $chat = Chat::find($id);

        if (!$chat){
            return $this->return_error('chat not found', 'ERR');
        }

        if (!auth()->user()->chats->contains($chat)){
            return $this->return_error('chat not yours', 'ERR');
        }

        $message = Message::find($mid);

        if (!$message){
            return $this->return_error('message not found', 'ERR');
        }

        $message->update($fields);

        return response([
            'status' => 'success',
            'message' => 'updated message successfully',
        ], 201);
    }
}
