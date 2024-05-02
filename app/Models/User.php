<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'email_verified_at',
        'is_admin'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }

    //
    public static function getUsersExceptUser(User $exceptUser)
    {
        $except_user_id = $exceptUser->id;
        $query = self::select([
            'users.*',
            'messages.message as last_message',
            'messages.created_at as last_message_date'
        ])
        ->where('users.id','!=',$except_user_id)
        ->when(! $exceptUser->is_admin,function($query){
            $query->whereNull('users.blocked_at');
        })
        ->leftJoin('conversations',function($join) use ($except_user_id){
            $join->on('conversations.user1_id','=','users.id')
                ->where('conversations.user2_id','=',$except_user_id)
                ->orWhere(function($query) use ($except_user_id){
                    $query->on('conversations.user2_id','=','users.id')
                    ->where('conversations.user1_id','=',$except_user_id);
                });
        })
        ->leftJoin('messages','messages.id','=','conversations.last_message_id')
        ->orderByRaw('IFNULL(users.blocked_at,1)')
        ->orderBy('messages.created_at','desc')
        ->orderBy('users.name');

        //dd($query->toSql());

        return $query->get();
    }

    //
    public function getConversationArray()
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'is_group'          => false,
            'is_user'           => true,
            'is_admin'          => (bool) $this->is_admin,
            'created_at'        => $this->created_at,
            'blocked_at'        => $this->blocked_at,
            'last_message'      => $this->last_message,
            'last_message_date' => $this->last_message_date
        ];
    }
}
