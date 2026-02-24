<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = \App\Models\User::role('admin')->first();
        $doctors = \App\Models\Doctor::all();

        // Forums
        foreach ($doctors as $doctor) {
            $forum = \App\Models\Forum::factory()->create([
                'doctor_id' => $doctor->id,
            ]);

            \App\Models\ForumPost::factory(5)->create([
                'forum_id' => $forum->id,
                'user_id' => $doctor->user_id,
            ]);
        }

        // Tips
        \App\Models\Tip::factory(20)->create([
            'admin_id' => $admin->id,
        ]);

        // Ads
        \App\Models\Advertisement::factory(5)->create([
            'admin_id' => $admin->id,
        ]);
    }
}
