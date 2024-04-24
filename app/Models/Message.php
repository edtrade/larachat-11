<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'message',
        'sender_id',
        'group_id',
        'receiver_id',
    ];  
    
    public function sender()
    {
        return $this->belongsTo(User::class,'sender_id','id');
    }    
    
    public function receiver()
    {
        return $this->belongsTo(User::class,'receiver_id','id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }  
    
    public function attachments()
    {
        return $this->hasMany(MessageAttachment::class);
    }
}
