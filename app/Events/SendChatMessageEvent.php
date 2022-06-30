<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendChatMessageEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }
 
    public function broadcastOn()
    {
        $message_type = $this->message->type;

        if ($message_type == "one-to-one"){
            return ['chats_channel_' . $this->message->to_uid . '_key_123ert']; 
        } else if ($message_type == "forum"){
            return ['forum_chats_channel_key_123ert'];
        }
    }

    public function broadcastAs()
    {
        return 'message.sent';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->message->id,
            'type' => $this->message->type,
            'content' => $this->message->content,
            'status' => $this->message->status,
            'to_uid' => $this->message->to_uid,
            'from_uid' => $this->message->from_uid,
            'to_forum_id' => $this->message->to_forum_id,
            'chat_id' => $this->message->chat_id
        ];
    }
}
