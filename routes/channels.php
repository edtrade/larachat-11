<?php

use Illuminate\Support\Facades\Broadcast;
use App\Http\Resources\UserResource;
use App\Models\User;

// Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });

Broadcast::channel('online', function ($user) {
    return $user ? new UserResource($user) : null;
});

Broadcast::channel('message.user.{user1_id}_{user2_id}',
    function(User $user, int $user1_id, int $user2_id){
        return $user->id === $user1_id ||
            $user->id === $user2_id 
            ? $user
            : null;
});

Broadcast::channel('message.group.{group_id}',
    function(User $user, int $group_id){
        return $user->groups->contains('id',$group_id)
            ? $user
            : null;
});