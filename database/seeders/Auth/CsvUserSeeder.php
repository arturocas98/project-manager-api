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
            // [id_card, name, email, telephone, address, birthdate, employee_type, title, senescyt_record, province, canton, has_electronic_signature, administrative_direction, administrative_unit, entity_ruc, entity_name]
            ['0930859640', 'ANYELO EDUARDO CHIQUITO JAMA', 'anyelo5891@gmail.com', '0981209231', 'GUAYAQUIL', '1991-08-05', 'INTERNO', 'INGENIERO EN SISTEMAS COMPUTACIONALES', '1006-2017-1820980', 'GUAYAS', 'GUAYAQUIL', 'SI', 'DESARROLLO DE SOFTWARE', 'TICS', '0992633794001', 'TECH2GO S.A.'],
            ['0951404953', 'CRISTHIAN JONATAHAN CHOEZ CHOEZ', 'jonathanchoez552@gmail.com', '0959915283', 'GUAYAQUIL', '1994-10-31', 'INTERNO', 'INGENIERO EN SISTEMAS COMPUTACIONALES', '1006-2023-2626155', 'GUAYAS', 'GUAYAQUIL', 'SI', 'DESARROLLO DE SOFTWARE', 'TICS', '0992633794001', 'TECH2GO S.A.'],
            ['0951655976', 'JOHN CHRISTIAN INTRIAGO SUAREZ', 'kriizz963@gmail.com', '0962975317', 'GUAYAQUIL', '1996-08-16', 'INTERNO', 'INGENIERO EN SISTEMAS COMPUTACIONALES', '1006-2023-2755121', 'GUAYAS', 'GUAYAQUIL', 'SI', 'DESARROLLO DE SOFTWARE', 'TICS', '0992633794001', 'TECH2GO S.A.'],
            ['2400039943', 'DAVID GABRIEL BORBOR MURILLO', 'david271998@gmail.com', '0985779864', 'GUAYAQUIL', '1998-03-27', 'INTERNO', 'INGENIERO EN SISTEMAS COMPUTACIONALES', '1006-2023-2755094', 'GUAYAS', 'GUAYAQUIL', 'NO', 'DESARROLLO DE SOFTWARE', 'TICS', '0992496479001', 'PROINFORMATICA S.A.'],
            ['0943891945', 'GEOMAYRA JACKELINE FAJARDO JACOME', 'geomayrafajardo@gmail.com', '0969968604', 'GUAYAQUIL', '1998-04-26', 'INTERNO', 'INGENIERO EN SISTEMAS COMPUTACIONALES', '1006-2021-2332708', 'GUAYAS', 'GUAYAQUIL', 'SI', 'DESARROLLO DE SOFTWARE', 'TICS', '0992633794001', 'TECH2GO S.A.'],
            ['0953255957', 'ANDY AUGUSTO SANCHEZ GONZALEZ', 'andysanchezgonzalez1996@gmail.com', '0939675882', 'GUAYAQUIL', '1996-08-10', 'INTERNO', 'LICENCIADO EN SISTEMAS DE INFORMACION', '1006-2018-2022004', 'GUAYAS', 'GUAYAQUIL', 'SI', 'DESARROLLO DE SOFTWARE', 'TICS', '0992633794001', 'TECH2GO S.A.'],
            ['1206224329', 'JUAN CARLOS VARGAS VERA', 'jcvargas1905@gmail.com', '0994559724', 'GUAYAQUIL', '1988-05-19', 'INTERNO', 'No hay dato', 'No hay dato', 'GUAYAS', 'GUAYAQUIL', 'SI', 'DESARROLLO DE SOFTWARE', 'TICS', '0992633794001', 'TECH2GO S.A.'],
            ['0921936761', 'AYRTON DARIO DUENAS TENESACA', 'ayrtondario@hotmail.com', '0995549770', 'GUAYAQUIL', '1996-05-14', 'INTERNO', 'No hay dato', 'No hay dato', 'GUAYAS', 'GUAYAQUIL', 'SI', 'DESARROLLO DE SOFTWARE', 'TICS', '0992496479001', 'PROINFORMATICA S.A.'],
            ['1309816468', 'ANGEL REMIGIO NAVARRO ARTEAGA', 'navarroangelr@gmail.com', '0958831183', 'GUAYAQUIL', '1986-10-31', 'INTERNO', 'LICENCIADO EN SISTEMAS DE INFORMACION', '1006-2016-1720367', 'GUAYAS', 'GUAYAQUIL', 'SI', 'DESARROLLO DE SOFTWARE', 'TICS', '0992496479001', 'PROINFORMATICA S.A.'],
            ['0953491602', 'MICHAEL ENRIQUE AVILA MORENO', 'michaelavilamoreno@gmail.com', '0978711086', 'GUAYAQUIL', '1998-04-21', 'INTERNO', 'TECNOLOGO EN SISTEMAS INFORMATICOS MENCION ANALISTA DE SISTEMAS', '2107-2022-2545157', 'GUAYAS', 'GUAYAQUIL', 'SI', 'DESARROLLO DE SOFTWARE', 'TICS', '0992496479001', 'PROINFORMATICA S.A.'],
            ['1003752993', 'ANDERSON RENATO PORTILLA MENDEZ', 'andersonportillamendez@hotmail.com', '0961538703', 'IBARRA', '1995-04-24', 'INTERNO', 'No hay dato', 'No hay dato', 'IMBABURA', 'IBARRA', 'SI', 'DESARROLLO DE SOFTWARE', 'TICS', '0992496479001', 'PROINFORMATICA S.A.'],
            ['0941088429', 'ARTURO CASTRO', 'arturocas2006@gmail.com', '0959523779', 'GUAYAQUIL', '1998-06-20', 'EXTERNO', 'INGENIERO EN SISTEMAS COMPUTACIONALES', '1006-2021-2333483', 'GUAYAS', 'GUAYAQUIL', 'SI', 'DESARROLLO DE SOFTWARE', 'TICS', '0992496479001', 'PROINFORMATICA S.A.'],
            ['0953864253', 'DORIS SANTA CRUZ', 'elisantacruz26@gmail.com', '0992887094', 'GUAYAQUIL', '1997-10-26', 'EXTERNO', 'INGENIERA EN SISTEMAS COMPUTACIONALES', '1006-2023-2616056', 'GUAYAS', 'GUAYAQUIL', 'SI', 'DESARROLLO DE SOFTWARE', 'TICS', '0992496479001', 'PROINFORMATICA S.A.'],
            ['0955367321', 'KERLLY LIZBETH SALINAS LEON', 'kerllysalinasl@gmail.com', '0968605979', 'GUAYAQUIL', '1996-12-28', 'INTERNO', 'INGENIERA EN SISTEMAS COMPUTACIONALES', '1006-2022-2572197', 'GUAYAS', 'GUAYAQUIL', 'SI', 'DESARROLLO DE SOFTWARE', 'TICS', '0992496479001', 'PROINFORMATICA S.A.'],
            ['1003738570', 'DAVID ALESSANDRO PALACIOS AVELLANA', 'alesandropascu@gmail.com', '0999156905', 'IBARRA', '2000-01-18', 'INTERNO', 'INGENIERO/A DE SOFTWARE', '1015-2025-3073186', 'IMBABURA', 'IBARRA', 'NO', 'DESARROLLO DE SOFTWARE', 'TICS', '0992496479001', 'PROINFORMATICA S.A.'],
            ['1004617864', 'CRISTIAN SALOMON TONTAQUIMBA LEMA', 'salomontes@hotmail.es', '0983755349', 'IBARRA', '1999-05-31', 'INTERNO', 'INGENIERO/A DE SOFTWARE', '1015-2024-2961987', 'IMBABURA', 'IBARRA', 'NO', 'DESARROLLO DE SOFTWARE', 'TICS', '0992496479001', 'PROINFORMATICA S.A.'],
            ['1005198468', 'VICTOR HUGO OCHOA BOLAŃOS', 'victor.hob@hotmail.com', '0997133314', 'IBARRA', '2002-09-24', 'INTERNO', 'No hay dato', 'No hay dato', 'IMBABURA', 'IBARRA', 'NO', 'DESARROLLO DE SOFTWARE', 'TICS', '0992496479001', 'PROINFORMATICA S.A.'],
            ['1005009962', 'ESTEVAN ALEJANDRO USUAY MORILLO', 'estevanac2@gmail.com', '0990387355', 'IBARRA', '2004-12-23', 'EXTERNO', 'TECNOLOGO/A SUPERIOR EN DESARROLLO DE SOFTWARE', '2039-2025-3206282', 'IMBABURA', 'IBARRA', 'NO', 'DESARROLLO DE SOFTWARE', 'TICS', '0992496479001', 'PROINFORMATICA S.A.'],
            ['1719539361', 'JORDY SEBASTIAN CALAHORRANO ALAVA', 'jordyjaja@hotmail.com', '0987628299', 'SANTO DOMINGO', '1998-01-08', 'EXTERNO', 'INGENIERO/A EN TECNOLOGIAS DE LA INFORMACION', '1079-2023-2777276', 'SANTO DOMINGO DE LOS TSÁCHILAS', 'SANTO DOMINGO', 'NO', 'DESARROLLO DE SOFTWARE', 'TICS', '0992496479001', 'PROINFORMATICA S.A.'],
            ['0202099578', 'MARIO RAUL PAZMINO YANEZ', 'mariopazmino78@gmail.com', '0989508266', 'SANTO DOMINGO', '2002-10-10', 'EXTERNO', 'INGENIERO/A EN TECNOLOGIAS DE LA INFORMACION', '1079-2025-3225053', 'SANTO DOMINGO DE LOS TSÁCHILAS', 'SANTO DOMINGO', 'NO', 'DESARROLLO DE SOFTWARE', 'TICS', '0992496479001', 'PROINFORMATICA S.A.'],
            ['2300805948', 'RAUL ENRIQUE FAZ INTRIAGO', 'fazintriagoraul@gmail.com', '0968174810', 'SANTO DOMINGO', '2000-03-21', 'EXTERNO', 'INGENIERO/A EN TECNOLOGIAS DE LA INFORMACION', '1079-2025-3241779', 'SANTO DOMINGO DE LOS TSÁCHILAS', 'SANTO DOMINGO', 'NO', 'DESARROLLO DE SOFTWARE', 'TICS', '0992496479001', 'PROINFORMATICA S.A.'],
            ['0550006753', 'BRYAN JOEL GALLARDO GUAMANI', 'gallarjoel@gmail.com', '0960155851', 'LATACUNGA', '2000-09-21', 'INTERNO', 'INGENIERO/A EN SISTEMAS DE INFORMACION', '1020-2025-3082969', 'COTOPAXI', 'LATACUNGA', 'NO', 'DESARROLLO DE SOFTWARE', 'TICS', '0992496479001', 'PROINFORMATICA S.A.'],
            ['1754201547', 'GENESIS CAROLINA SIMBANA CHAZA', 'genesiscarolina351@gmail.com', '0962336933', 'LATACUNGA', '2002-11-23', 'EXTERNO', 'INGENIERO/A EN TECNOLOGIAS DE LA INFORMACION', '1079-2025-3264037', 'COTOPAXI', 'LATACUNGA', 'NO', 'DESARROLLO DE SOFTWARE', 'TICS', '0992496479001', 'PROINFORMATICA S.A.'],
            ['0706712643', 'IRINA MABEL OCHOA RAMIREZ', 'irinaochoa32@gmail.com', '0998608496', 'MACHALA', '2000-11-30', 'INTERNO', 'INGENIERO/A EN TECNOLOGIAS DE LA INFORMACION', '1006-2025-3114648', 'EL ORO', 'MACHALA', 'NO', 'DESARROLLO DE SOFTWARE', 'TICS', '0992633794001', 'TECH2GO S.A.'],
            ['1004558431', 'JAIR RICARDO YEPEZ MONTENEGRO', 'jyepez843@gmail.com', '0994698992', 'IBARRA', '2004-08-10', 'INTERNO', 'TECNOLOGO/A SUPERIOR EN DESARROLLO DE SOFTWARE', '2039-2025-3206280', 'IMBABURA', 'IBARRA', 'NO', 'DESARROLLO DE SOFTWARE', 'TICS', '0992633794001', 'TECH2GO S.A.'],
            ['0550076681', 'ESTEBAN PAVON SEGOVIA', 'pavonesteban15@gmail.com', '0984311147', 'QUITO', '2002-08-29', 'INTERNO', 'INGENIERO/A DE SOFTWARE', '1079-2025-3108570', 'PICHINCHA', 'QUITO', 'NO', 'DESARROLLO DE SOFTWARE', 'TICS', '0591764069001', 'CONSORCIO SMART GOB'],
            ['0930625462', 'FABRICIO ALEXANDER ONTANEDA VALVERDE', 'faontane@espol.edu.ec', '0981952846', 'GUAYAQUIL', '2000-08-26', 'EXTERNO', 'INGENIERO/A EN CIENCIAS DE LA COMPUTACION', '1021-2024-2844453', 'GUAYAS', 'GUAYAQUIL', 'NO', 'DESARROLLO DE SOFTWARE', 'TICS', '0992496479001', 'PROINFORMATICA S.A.'],
            ['0953888831', 'LUIS ALEXANDER SUAREZ COLOMBA', 'luissuarez2t@gmail.com', '0968916456', 'GUAYAQUIL', '1997-02-12', 'EXTERNO', 'INGENIERO EN SISTEMAS COMPUTACIONALES', '1006-2021-2332012', 'GUAYAS', 'GUAYAQUIL', 'SI', 'DESARROLLO DE SOFTWARE', 'TICS', '0992633794001', 'TECH2GO S.A.'],
            ['0913897716', 'WILLIAM DAVID VELASCO SANTOS', 'wdvelas@gmail.com', '0987148442', 'DAULE', '1970-08-09', 'INTERNO', 'TECNOLOGO SUPERIOR EN CONTABILIDAD', '2397-2024-2934990', 'GUAYAS', 'DAULE', 'SI', 'DESARROLLO DE SOFTWARE', 'TICS', '0992633794001', 'TECH2GO S.A.'],
            ['0905356879', 'MORLA PAREDES OVIDIO JAVIER', 'ovidio.morla.docudata@gmail.com', '0984481507', 'GUAYAQUIL', '1956-10-23', 'EXTERNO', 'INGENIERO COMERCIAL', '1030-2018-1996296', 'GUAYAS', 'GUAYAQUIL', 'SI', 'ADMINISTRACION', 'ADMINISTRACION', '0992645792001', 'SUPERGOLD S.A.'],
            ['0943915561', 'JAVIER ALEJANDRO VEGA MOLINA', 'javiervegamolina29@gmail.com', '0990729257', 'GUAYAQUIL', '2001-05-29', 'EXTERNO', 'INGENIERO/A EN CIENCIAS DE LA COMPUTACION', '1021-2024-2844446', 'GUAYAS', 'GUAYAQUIL', 'NO', 'DESARROLLO DE SOFTWARE', 'TICS', '0992633794001', 'TECH2GO S.A.'],
            ['0918177262', 'JOSE ALBERTO ALEJANDRO BARZOLA', 'josealbertoab@yahoo.es', '0996451811', 'BALLENITA', '1979-03-19', 'EXTERNO', 'MAGISTER EN PLANIFICACION Y DISEŃO URBANO MENCIÓN EN CIUDADES INTELIGENTES', '1023-15-1388214', 'SANTA ELENA', 'BALLENITA', 'SI', 'CATASTRO', 'CATASTRO', '0992633794001', 'TECH2GO S.A.'],
        ];

        $count = 0;

        foreach ($users as $userData) {
            [
                $idCard,
                $name,
                $email,
                $telephone,
                $address,
                $birthdate,
                $employeeType,
                $title,
                $senescytRecord,
                $province,
                $canton,
                $hasSignature,
                $adminDirection,
                $adminUnit,
                $entityRuc,
                $entityName
            ] = $userData;

            // Convert 'SI'/'NO' to boolean/integer
            $hasElectronicSignature = match($hasSignature) {
                'SI' => 1,
                'NO' => 0,
                default => null
            };

            try {
                $user = $createUserAction->execute([
                    'name' => trim($name),
                    'email' => trim($email),
                    'password' => $idCard,
                    'id_card' => $idCard,
                    'telephone' => $telephone,
                    'address' => $address,
                    'birthdate' => $birthdate ?: null,
                    'employee_type' => $employeeType,
                    'title' => $title !== 'No hay dato' ? $title : null,
                    'senescyt_record' => $senescytRecord !== 'No hay dato' ? $senescytRecord : null,
                    'province' => $province,
                    'canton' => $canton,
                    'has_electronic_signature' => $hasElectronicSignature,
                    'administrative_direction' => $adminDirection,
                    'administrative_unit' => $adminUnit,
                    'entity_ruc' => $entityRuc,
                    'entity_name' => $entityName,
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