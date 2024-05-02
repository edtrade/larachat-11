<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'owner_id',
        'last_message_id'
    ];   

    public function users()
    {
        return $this->belongsToMany(User::class);
    } 
    
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class,'owner_id','id');
    }

    //
    public static function getGroupsForUser(User $user)
    {
        $query = self::select([
            'groups.*',
            'messages.message as last_message',
            'messages.created_at as last_message_date'
        ])
        ->join('group_user','group_user.group_id','=','groups.id')
        ->leftJoin('messages','messages.id','=','groups.last_message_id')
        ->where('group_user.user_id', $user->id)
        ->orderBy('messages.created_at','desc')
        ->orderBy('groups.name');

        return $query->get();
    }

    //
    public function getConversationArray()
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'description'       => $this->description,
            'is_group'          => true,
            'is_user'           => false,
            'owner_id'          => $this->owner_id,
            'users'             => $users = $this->users,
            'user_ids'          => $users->pluck('id'),
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
            'last_message'      => $this->last_message,
            'last_message_date' => $this->last_message_date
        ];
    }    
}
