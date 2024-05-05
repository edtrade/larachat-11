<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Conversation;
use App\Models\Group;
use App\Models\Message;
use App\Models\MessageAttachment;
use Illuminate\Http\Request;
use App\Http\Requests\MessageStoreRequest;
use App\Http\Resources\MessageResource;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Events\MessageSocketEvent;
use Illuminate\Validation\ValidationExceptio;

class MessageController extends Controller
{
    //
    public function byUser(User $user)
    {
        $messages = Message::where('sender_id',auth()->id())
            ->where('receiver_id',$user->id)
            ->orWhere('receiver_id',auth()->id())
            ->where('sender_id',$user->id)
            ->latest()
            ->paginate(10);

        return inertia('Home',[
            'selectedConversation'  => $user->getConversationArray(),
            'messages'              => MessageResource::collection($messages)
        ]);
    }

    //
    public function byGroup(Group $group)
    {
        $messages = Message::where('group_id', $group->id)
            ->latest()
            ->paginate(10);

        return inertia('Home',[
            'selectedConversation'  => $group->getConversationArray(),
            'messages'              => MessageResource::collection($messages)
        ]);       
    }   
    
    //
    public function loadOlder(Message $message)
    {
        if($message->group_id){
            $messages = Message::where('created_at', '<', $message->created_at)
                ->where('group_id',$message->group_id)
                ->latest()
                ->paginate(10);
        }else{
            $messages = Message::where('created_at', '<', $message->created_at)
                ->where(function($query){
                    $query->where('sender_id',$message->sender_id)
                    ->where('receiver_id',$message->receiver_id)
                    ->orWhere('sender_id',$message->receiver_id)
                    ->where('receiver_id',$message->sender_id);
                })
                ->latest()
                ->paginate(10);           
        }

        return MessageResource::collection($messages);
    }

    public function store(MessageStoreRequest $request)
    {
        $validated = $request->validated();
        $validated['sender_id'] = auth()->id();
        $receiver_id = $validated['receiver_id'] ?? null;
        $group_id = $validated['group_id'] ?? null;

        $files = $validated['attachments'] ?? [];

        $message = Message::create($validated);

        $attachments = [];
        if($files){
            $directory = 'attachments/'.Str::uuid();
            Storage::makeDirectory($directory);

            $model = [
                'message_id'    => $this->message_id,
                'name'          => $file->getClientOriginalName(),
                'mime'          => $file->getClientMimeType(),
                'size'          => $file->getSize(),
                'path'          => $file->store($directory, 'public')
            ];

            $attachment = MessageAttachment::create($model);
            $attachments[] = $attachment;

            $message->attachments = $attachments;
        }

        if($receiver_id){
            Conversation::updateWithMessage(
                $receiver_id,
                auth()->id(),
                $message
            );
        }

        if($group_id){
            Group::updateWithMessage(
                $group_id,
                $message
            );
        }        

        MessageSocketEvent::dispatch($message);

        return new MessageResource($message);
    }

    public function destroy(Message $message)
    {
        throw_unless(
            $message->sender === auth()->id(),
            new ValidationExceptio('Forbidden')
        );

        $message->delete();

        return response(true, 204);
    }
}
