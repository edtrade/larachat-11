<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user1_id',
        'user2_id',
        'last_message_id'
    ];    

    public function lastMessage()
    {
        return $this->belongsTo(Message::class, 'last_message_id');
    }

    public function user1()
    {
        return $this->belongsTo(User::class, 'user1_id','id');
    }
    
    public function user2()
    {
        return $this->belongsTo(User::class, 'user2_id','id');
    }    

    public static function getConversationsForSidebar(User $user)
    {
        $users = User::getUsersExceptUser($user);
        $groups = Group::getGroupsForUser($user);

        return $users->map(function(User $user){
            return $user->getConversationArray();
        })
        ->concat($groups->map(function(Group $group){
            return $group->getConversationArray();
        }));
    } 

    public static function updateWithMessage($receiver_id, $sender_id, $message)
    {
        $conversation = self::where(function($query) use ($receiver_id, $sender_id){
            $query->where('user1_id', $receiver_id)
                ->where('user2_id', $sender_id);
        })
        ->orWhere(function($query) use ($receiver_id, $sender_id){
            $query->where('user1_id', $sender_id)
                ->where('user2_id', $receiver_id);
        })->first();

        if($conversation){
            $conversation->update([
                'last_message_id' => $message->id
            ]);
        }else{
            self::create([
                'user1_id'          => $receiver_id,
                'user2_id'          => $sender_id,
                'last_message_id'   => $message->id
            ]);
        }
    }
}
