<?php

use Illuminate\Support\Facades\Broadcast;
use App\Http\Resources\UserResource;
// Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });

Broadcast::channel('online', function ($user) {
    return $user ? new UserResource($user) : null;
});
