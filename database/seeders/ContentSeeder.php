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
            // Only create forum and posts if doctor doesn't have a forum yet
            if (!$doctor->forum()->exists()) {
                $forum = \App\Models\Forum::factory()->create([
                    'doctor_id' => $doctor->id,
                ]);

                \App\Models\ForumPost::factory(5)->create([
                    'forum_id' => $forum->id,
                    'user_id' => $doctor->user_id,
                ]);
            }
        }

        // Tips - only create if we have few tips
        if (\App\Models\Tip::count() < 20) {
            \App\Models\Tip::factory(20)->create([
                'admin_id' => $admin->id,
            ]);
        }

        // Ads - only create if we have few ads
        if (\App\Models\Advertisement::count() < 5) {
            \App\Models\Advertisement::factory(5)->create([
                'admin_id' => $admin->id,
            ]);
        }
    }
}
