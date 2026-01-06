<?php

namespace Database\Seeders\Testing;

use Illuminate\Database\Seeder;
use Laravel\Passport\Passport;

class PassportClientSeeder extends Seeder
{
    public function run(): void
    {
        $client = Passport::client()
            ->create([
                'id' => config('passport.personal_access_client.id'),
                'name' => 'Testing Personal Access Client',
                'secret' => config('passport.personal_access_client.secret'),
                'provider' => config('auth.guards.api.provider'),
                'redirect' => config('app.url'),
                'personal_access_client' => true,
                'password_client' => false,
                'revoked' => false,
            ]);

        $accessClient = Passport::personalAccessClient();
        $accessClient->client_id = $client->getKey();
        $accessClient->save();
    }
}
