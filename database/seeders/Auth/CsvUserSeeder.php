<?php

namespace Database\Seeders\Auth;

use App\Actions\Auth\UserCreateAction;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class CsvUserSeeder extends Seeder
{
    public function run(UserCreateAction $createUserAction): void
    {
        $csvPath = 'C:\Users\justs\Downloads\Informacion_ORIGAMIEC_SMARTGOB v1 (1).csv';
        
        if (!file_exists($csvPath)) {
            $this->command->error("CSV File not found at {$csvPath}");
            return;
        }

        $file = fopen($csvPath, 'r');
        
        // Skip header
        $header = fgetcsv($file, 0, ';');

        $roleName = 'collaborator';
        $role = Role::findByName($roleName, 'api');
        
        if (!$role) {
            // Let's try to create it if it doesn't exist
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'api']);
            $this->command->info("Created role 'collaborator'.");
        }

        $count = 0;
        
        while (($data = fgetcsv($file, 0, ';')) !== false) {
            if (count($data) < 17) {
                continue; // invalid row
            }

            $idCard = trim($data[0] ?? '');
            $name = trim($data[2] ?? '');
            $email = trim($data[6] ?? '');
            $telephone = trim($data[7] ?? '');
            $address = trim($data[10] ?? '');

            if (empty($idCard) || empty($email)) {
                continue;
            }

            try {
                $user = $createUserAction->execute([
                    'name' => $name,
                    'email' => $email,
                    'password' => $idCard, // Default password as ID card
                    'id_card' => $idCard,
                    'telephone' => $telephone,
                    'address' => $address,
                ]);

                $user->assignRole($roleName);

                $count++;
            } catch (\Exception $e) {
                Log::warning("Could not create user {$email}: " . $e->getMessage());
            }
        }

        fclose($file);
        
        $this->command->info("Seeded {$count} users from CSV.");
    }
}
