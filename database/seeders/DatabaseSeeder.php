<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Group;
use App\Models\Message;
use App\Models\Conversation;
use Carbon\Carbon;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name'      => 'John Doe',
            'email'     => 'john@example.com',
            'password'  => bcrypt('password'),
            'is_admin'  => 1
        ]);

        User::factory()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password'  => bcrypt('password'),
            'is_admin'  => 0
        ]);    
        
        User::factory(10)->create();

        for($i = 0;$i < 5;$i++){
            $group = Group::factory()->create([
                'owner_id'  => 1
            ]);

            $users = User::inRandomOrder()->limit(rand(2,5))->pluck('id');
            $group->users()->attach(array_unique([1,...$users]));
        }

        Message::factory(1000)->create();

        $messages = Message::whereNull('group_id')->orderBy('created_at')->get();

        $conversations = $messages->groupBy(function($message){
            return collect([$message->sender_id, $message->receiver_id])
                ->sort()
                ->implode('_');
        })
        ->map(function($groupedMessages){
            return [
                'user1_id'          => $groupedMessages->first()->sender_id,
                'user2_id'          => $groupedMessages->first()->receiver_id,
                'last_message_id'   => $groupedMessages->last()->id,
                'created_at'        => Carbon::now(),
                'updated_at'        => Carbon::now(),
            ];
        })
        ->values();

        Conversation::insertOrIgnore($conversations->toArray());
    }
}
