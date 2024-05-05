<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Message;
use App\Http\Resources\MessageResource;

class MessageSocketEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Message $message)
    {
        //
    }

    public function broadcastWith()
    {
        return [
            'message' => new MessageResource($this->message)
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    { 
        $message = $this->message;

        $sorted_user_str = collect([
            $message->sender_id,
            $message->receiver_id
        ])
        ->sort()
        ->implode('_');
        

        $channels = [];

        if($message->group_id){
            $channels[] = new PrivateChannel('message.group.'.$message->group_id);
        }else{
            $channels[] = new PrivateChannel('message.user.'.$sorted_user_str);
        }

        return $channels;
    }
}
