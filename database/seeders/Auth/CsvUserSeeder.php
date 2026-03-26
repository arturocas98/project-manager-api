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
        $roleName = 'collaborator';
        $role = Role::findByName($roleName, 'api');

        if (!$role) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'api']);
            $this->command->info("Created role 'collaborator'.");
        }

        $users = [
            ['0930859640', 'ANYELO EDUARDO CHIQUITO JAMA', 'anyelo5891@gmail.com', '0981209231', 'GUAYAQUIL'],
            ['0951404953', 'CRISTHIAN JONATAHAN CHOEZ CHOEZ', 'jonathanchoez552@gmail.com', '0959915283', 'GUAYAQUIL'],
            ['0951655976', 'JOHN CHRISTIAN INTRIAGO SUAREZ', 'kriizz963@gmail.com', '0962975317', 'GUAYAQUIL'],
            ['2400039943', 'DAVID GABRIEL BORBOR MURILLO', 'david271998@gmail.com', '0985779864', 'GUAYAQUIL'],
            ['0943891945', 'GEOMAYRA JACKELINE FAJARDO JACOME', 'geomayrafajardo@gmail.com', '0969968604', 'GUAYAQUIL'],
            ['0953255957', 'ANDY AUGUSTO SANCHEZ GONZALEZ', 'andysanchezgonzalez1996@gmail.com', '0939675882', 'GUAYAQUIL'],
            ['1206224329', 'JUAN CARLOS VARGAS VERA', 'jcvargas1905@gmail.com', '0994559724', 'GUAYAQUIL'],
            ['0921936761', 'AYRTON DARIO DUENAS TENESACA', 'ayrtondario@hotmail.com', '0995549770', 'GUAYAQUIL'],
            ['1309816468', 'ANGEL REMIGIO NAVARRO ARTEAGA', 'navarroangelr@gmail.com', '0958831183', 'GUAYAQUIL'],
            ['0953491602', 'MICHAEL ENRIQUE AVILA MORENO', 'michaelavilamoreno@gmail.com', '0978711086', 'GUAYAQUIL'],
            ['1003752993', 'ANDERSON RENATO PORTILLA MENDEZ', 'andersonportillamendez@hotmail.com', '0961538703', 'IBARRA'],
            ['0941088429', 'ARTURO CASTRO', 'arturocas2006@gmail.com', '0959523779', 'GUAYAQUIL'],
            ['0953864253', 'DORIS SANTA CRUZ', 'elisantacruz26@gmail.com', '0992887094', 'GUAYAQUIL'],
            ['0955367321', 'KERLLY LIZBETH SALINAS LEON', 'kerllysalinasl@gmail.com', '0968605979', 'GUAYAQUIL'],
            ['1003738570', 'DAVID ALESSANDRO PALACIOS AVELLANA', 'alesandropascu@gmail.com', '0999156905', 'IBARRA'],
            ['1004617864', 'CRISTIAN SALOMON TONTAQUIMBA LEMA', 'salomontes@hotmail.es', '0983755349', 'IBARRA'],
            ['1005198468', 'VICTOR HUGO OCHOA BOLAŃOS', 'victor.hob@hotmail.com', '0997133314', 'IBARRA'],
            ['1005009962', 'ESTEVAN ALEJANDRO USUAY MORILLO', 'estevanac2@gmail.com', '0990387355', 'IBARRA'],
            ['1719539361', 'JORDY SEBASTIAN CALAHORRANO ALAVA', 'jordyjaja@hotmail.com', '0987628299', 'SANTO DOMINGO'],
            ['0202099578', 'MARIO RAUL PAZMINO YANEZ', 'mariopazmino78@gmail.com', '0989508266', 'SANTO DOMINGO'],
            ['2300805948', 'RAUL ENRIQUE FAZ INTRIAGO', 'fazintriagoraul@gmail.com', '0968174810', 'SANTO DOMINGO'],
            ['0550006753', 'BRYAN JOEL GALLARDO GUAMANI', 'gallarjoel@gmail.com', '0960155851', 'LATACUNGA'],
            ['1754201547', 'GENESIS CAROLINA SIMBANA CHAZA', 'genesiscarolina351@gmail.com', '0962336933', 'LATACUNGA'],
            ['0706712643', 'IRINA MABEL OCHOA RAMIREZ', 'irinaochoa32@gmail.com', '0998608496', 'MACHALA'],
            ['1004558431', 'JAIR RICARDO YEPEZ MONTENEGRO', 'jyepez843@gmail.com', '0994698992', 'IBARRA'],
            ['0550076681', 'ESTEBAN PAVON SEGOVIA', 'pavonesteban15@gmail.com', '0984311147', 'QUITO'],
            ['0930625462', 'FABRICIO ALEXANDER ONTANEDA VALVERDE', 'faontane@espol.edu.ec', '0981952846', 'GUAYAQUIL'],
            ['0953888831', 'LUIS ALEXANDER SUAREZ COLOMBA', 'luissuarez2t@gmail.com', '0968916456', 'GUAYAQUIL'],
            ['0913897716', 'WILLIAM DAVID VELASCO SANTOS', 'wdvelas@gmail.com', '0987148442', 'DAULE'],
            ['0905356879', 'MORLA PAREDES OVIDIO JAVIER', 'ovidio.morla.docudata@gmail.com', '0984481507', 'GUAYAQUIL'],
            ['0943915561', 'JAVIER ALEJANDRO VEGA MOLINA', 'javiervegamolina29@gmail.com', '0990729257', 'GUAYAQUIL'],
            ['0918177262', 'JOSE ALBERTO ALEJANDRO BARZOLA', 'josealbertoab@yahoo.es', '0996451811', 'BALLENITA'],
        ];

        $count = 0;

        foreach ($users as $userData) {
            [$idCard, $name, $email, $telephone, $address] = $userData;

            try {
                $user = $createUserAction->execute([
                    'name' => trim($name),
                    'email' => trim($email),
                    'password' => $idCard,
                    'id_card' => $idCard,
                    'telephone' => $telephone,
                    'address' => $address,
                ]);

                $user->assignRole($roleName);
                $count++;

                $this->command->info("Created user: {$email}");
            } catch (\Exception $e) {
                Log::warning("Could not create user {$email}: " . $e->getMessage());
                $this->command->error("Failed to create user {$email}: " . $e->getMessage());
            }
        }

        $this->command->info("Seeded {$count} users from CSV.");
    }
}