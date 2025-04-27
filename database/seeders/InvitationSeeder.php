<?php

namespace Database\Seeders;

use App\Models\Invitation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InvitationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create random invitations
        Invitation::factory()->count(2)->create();

        // Create used invitations
        Invitation::factory()->count(2)->used()->create();

        // Create expired invitations
        Invitation::factory()->count(2)->expired()->create();

        // Create used and expired invitations
        Invitation::factory()->count(2)->used()->expired()->create();

        // Create used but not expired invitations
        Invitation::factory()->count(2)->used()->notExpired()->create();
    }
}
