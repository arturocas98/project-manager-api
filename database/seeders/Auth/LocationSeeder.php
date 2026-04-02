<?php

namespace Database\Seeders\Auth;

use App\Models\Locate;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
    $locations = [
            // Provincia de Azuay
            ['name_provinces' => 'Azuay', 'name_canton' => 'Cuenca'],
            ['name_provinces' => 'Azuay', 'name_canton' => 'Girón'],
            ['name_provinces' => 'Azuay', 'name_canton' => 'Gualaceo'],
            ['name_provinces' => 'Azuay', 'name_canton' => 'Nabón'],
            ['name_provinces' => 'Azuay', 'name_canton' => 'Paute'],
            ['name_provinces' => 'Azuay', 'name_canton' => 'Pucará'],
            ['name_provinces' => 'Azuay', 'name_canton' => 'San Fernando'],
            ['name_provinces' => 'Azuay', 'name_canton' => 'Santa Isabel'],
            ['name_provinces' => 'Azuay', 'name_canton' => 'Sígsig'],
            ['name_provinces' => 'Azuay', 'name_canton' => 'Oña'],
            ['name_provinces' => 'Azuay', 'name_canton' => 'Chordeleg'],
            ['name_provinces' => 'Azuay', 'name_canton' => 'El Pan'],
            ['name_provinces' => 'Azuay', 'name_canton' => 'Sevilla de Oro'],
            ['name_provinces' => 'Azuay', 'name_canton' => 'Guachapala'],
            ['name_provinces' => 'Azuay', 'name_canton' => 'Camilo Ponce Enríquez'],
            
            // Provincia de Bolívar
            ['name_provinces' => 'Bolívar', 'name_canton' => 'Guaranda'],
            ['name_provinces' => 'Bolívar', 'name_canton' => 'Chillanes'],
            ['name_provinces' => 'Bolívar', 'name_canton' => 'Chimbo'],
            ['name_provinces' => 'Bolívar', 'name_canton' => 'Echeandía'],
            ['name_provinces' => 'Bolívar', 'name_canton' => 'San Miguel'],
            ['name_provinces' => 'Bolívar', 'name_canton' => 'Caluma'],
            ['name_provinces' => 'Bolívar', 'name_canton' => 'Las Naves'],
            
            // Provincia de Cañar
            ['name_provinces' => 'Cañar', 'name_canton' => 'Azogues'],
            ['name_provinces' => 'Cañar', 'name_canton' => 'Biblian'],
            ['name_provinces' => 'Cañar', 'name_canton' => 'Cañar'],
            ['name_provinces' => 'Cañar', 'name_canton' => 'Déleg'],
            ['name_provinces' => 'Cañar', 'name_canton' => 'El Tambo'],
            ['name_provinces' => 'Cañar', 'name_canton' => 'La Troncal'],
            ['name_provinces' => 'Cañar', 'name_canton' => 'Suscal'],
            
            // Provincia de Carchi
            ['name_provinces' => 'Carchi', 'name_canton' => 'Tulcán'],
            ['name_provinces' => 'Carchi', 'name_canton' => 'Bolívar'],
            ['name_provinces' => 'Carchi', 'name_canton' => 'Espejo'],
            ['name_provinces' => 'Carchi', 'name_canton' => 'Mira'],
            ['name_provinces' => 'Carchi', 'name_canton' => 'Montúfar'],
            ['name_provinces' => 'Carchi', 'name_canton' => 'San Pedro de Huaca'],
            
            // Provincia de Chimborazo
            ['name_provinces' => 'Chimborazo', 'name_canton' => 'Riobamba'],
            ['name_provinces' => 'Chimborazo', 'name_canton' => 'Alausí'],
            ['name_provinces' => 'Chimborazo', 'name_canton' => 'Colta'],
            ['name_provinces' => 'Chimborazo', 'name_canton' => 'Chambo'],
            ['name_provinces' => 'Chimborazo', 'name_canton' => 'Chunchi'],
            ['name_provinces' => 'Chimborazo', 'name_canton' => 'Guamote'],
            ['name_provinces' => 'Chimborazo', 'name_canton' => 'Guano'],
            ['name_provinces' => 'Chimborazo', 'name_canton' => 'Pallatanga'],
            ['name_provinces' => 'Chimborazo', 'name_canton' => 'Penipe'],
            ['name_provinces' => 'Chimborazo', 'name_canton' => 'Cumandá'],
            
            // Provincia de Cotopaxi
            ['name_provinces' => 'Cotopaxi', 'name_canton' => 'Latacunga'],
            ['name_provinces' => 'Cotopaxi', 'name_canton' => 'La Maná'],
            ['name_provinces' => 'Cotopaxi', 'name_canton' => 'Pangua'],
            ['name_provinces' => 'Cotopaxi', 'name_canton' => 'Pujilí'],
            ['name_provinces' => 'Cotopaxi', 'name_canton' => 'Salcedo'],
            ['name_provinces' => 'Cotopaxi', 'name_canton' => 'Saquisilí'],
            ['name_provinces' => 'Cotopaxi', 'name_canton' => 'Sigchos'],
            
            // Provincia de El Oro
            ['name_provinces' => 'El Oro', 'name_canton' => 'Machala'],
            ['name_provinces' => 'El Oro', 'name_canton' => 'Arenillas'],
            ['name_provinces' => 'El Oro', 'name_canton' => 'Atahualpa'],
            ['name_provinces' => 'El Oro', 'name_canton' => 'Balsas'],
            ['name_provinces' => 'El Oro', 'name_canton' => 'Chilla'],
            ['name_provinces' => 'El Oro', 'name_canton' => 'El Guabo'],
            ['name_provinces' => 'El Oro', 'name_canton' => 'Huaquillas'],
            ['name_provinces' => 'El Oro', 'name_canton' => 'Las Lajas'],
            ['name_provinces' => 'El Oro', 'name_canton' => 'Marcabelí'],
            ['name_provinces' => 'El Oro', 'name_canton' => 'Pasaje'],
            ['name_provinces' => 'El Oro', 'name_canton' => 'Piñas'],
            ['name_provinces' => 'El Oro', 'name_canton' => 'Portovelo'],
            ['name_provinces' => 'El Oro', 'name_canton' => 'Santa Rosa'],
            ['name_provinces' => 'El Oro', 'name_canton' => 'Zaruma'],
            
            // Provincia de Esmeraldas
            ['name_provinces' => 'Esmeraldas', 'name_canton' => 'Esmeraldas'],
            ['name_provinces' => 'Esmeraldas', 'name_canton' => 'Atacames'],
            ['name_provinces' => 'Esmeraldas', 'name_canton' => 'Eloy Alfaro'],
            ['name_provinces' => 'Esmeraldas', 'name_canton' => 'Muisne'],
            ['name_provinces' => 'Esmeraldas', 'name_canton' => 'Quinindé'],
            ['name_provinces' => 'Esmeraldas', 'name_canton' => 'Río Verde'],
            ['name_provinces' => 'Esmeraldas', 'name_canton' => 'San Lorenzo'],
            
            // Provincia de Galápagos
            ['name_provinces' => 'Galápagos', 'name_canton' => 'San Cristóbal'],
            ['name_provinces' => 'Galápagos', 'name_canton' => 'Isabela'],
            ['name_provinces' => 'Galápagos', 'name_canton' => 'Santa Cruz'],
            
            // Provincia de Guayas
            ['name_provinces' => 'Guayas', 'name_canton' => 'Guayaquil'],
            ['name_provinces' => 'Guayas', 'name_canton' => 'Alfredo Baquerizo Moreno'],
            ['name_provinces' => 'Guayas', 'name_canton' => 'Balao'],
            ['name_provinces' => 'Guayas', 'name_canton' => 'Balzar'],
            ['name_provinces' => 'Guayas', 'name_canton' => 'Colimes'],
            ['name_provinces' => 'Guayas', 'name_canton' => 'Coronel Marcelino Maridueña'],
            ['name_provinces' => 'Guayas', 'name_canton' => 'Daule'],
            ['name_provinces' => 'Guayas', 'name_canton' => 'Durán'],
            ['name_provinces' => 'Guayas', 'name_canton' => 'El Empalme'],
            ['name_provinces' => 'Guayas', 'name_canton' => 'El Triunfo'],
            ['name_provinces' => 'Guayas', 'name_canton' => 'General Antonio Elizalde'],
            ['name_provinces' => 'Guayas', 'name_canton' => 'Isidro Ayora'],
            ['name_provinces' => 'Guayas', 'name_canton' => 'Lomas de Sargentillo'],
            ['name_provinces' => 'Guayas', 'name_canton' => 'Milagro'],
            ['name_provinces' => 'Guayas', 'name_canton' => 'Naranjal'],
            ['name_provinces' => 'Guayas', 'name_canton' => 'Naranjito'],
            ['name_provinces' => 'Guayas', 'name_canton' => 'Nobol'],
            ['name_provinces' => 'Guayas', 'name_canton' => 'Palestina'],
            ['name_provinces' => 'Guayas', 'name_canton' => 'Pedro Carbo'],
            ['name_provinces' => 'Guayas', 'name_canton' => 'Playas'],
            ['name_provinces' => 'Guayas', 'name_canton' => 'Salitre'],
            ['name_provinces' => 'Guayas', 'name_canton' => 'Samborondón'],
            ['name_provinces' => 'Guayas', 'name_canton' => 'Santa Lucía'],
            ['name_provinces' => 'Guayas', 'name_canton' => 'Simón Bolívar'],
            ['name_provinces' => 'Guayas', 'name_canton' => 'Yaguachi'],
            
            // Provincia de Imbabura
            ['name_provinces' => 'Imbabura', 'name_canton' => 'Ibarra'],
            ['name_provinces' => 'Imbabura', 'name_canton' => 'Antonio Ante'],
            ['name_provinces' => 'Imbabura', 'name_canton' => 'Cotacachi'],
            ['name_provinces' => 'Imbabura', 'name_canton' => 'Otavalo'],
            ['name_provinces' => 'Imbabura', 'name_canton' => 'Pimampiro'],
            ['name_provinces' => 'Imbabura', 'name_canton' => 'San Miguel de Urcuquí'],
            
            // Provincia de Loja
            ['name_provinces' => 'Loja', 'name_canton' => 'Loja'],
            ['name_provinces' => 'Loja', 'name_canton' => 'Calvas'],
            ['name_provinces' => 'Loja', 'name_canton' => 'Catamayo'],
            ['name_provinces' => 'Loja', 'name_canton' => 'Celica'],
            ['name_provinces' => 'Loja', 'name_canton' => 'Chaguarpamba'],
            ['name_provinces' => 'Loja', 'name_canton' => 'Espíndola'],
            ['name_provinces' => 'Loja', 'name_canton' => 'Gonzanamá'],
            ['name_provinces' => 'Loja', 'name_canton' => 'Macará'],
            ['name_provinces' => 'Loja', 'name_canton' => 'Olmedo'],
            ['name_provinces' => 'Loja', 'name_canton' => 'Paltas'],
            ['name_provinces' => 'Loja', 'name_canton' => 'Pindal'],
            ['name_provinces' => 'Loja', 'name_canton' => 'Puyango'],
            ['name_provinces' => 'Loja', 'name_canton' => 'Quilanga'],
            ['name_provinces' => 'Loja', 'name_canton' => 'Saraguro'],
            ['name_provinces' => 'Loja', 'name_canton' => 'Sozoranga'],
            ['name_provinces' => 'Loja', 'name_canton' => 'Zapotillo'],
            
            // Provincia de Los Ríos
            ['name_provinces' => 'Los Ríos', 'name_canton' => 'Babahoyo'],
            ['name_provinces' => 'Los Ríos', 'name_canton' => 'Baba'],
            ['name_provinces' => 'Los Ríos', 'name_canton' => 'Buena Fe'],
            ['name_provinces' => 'Los Ríos', 'name_canton' => 'Mocache'],
            ['name_provinces' => 'Los Ríos', 'name_canton' => 'Montalvo'],
            ['name_provinces' => 'Los Ríos', 'name_canton' => 'Palenque'],
            ['name_provinces' => 'Los Ríos', 'name_canton' => 'Puebloviejo'],
            ['name_provinces' => 'Los Ríos', 'name_canton' => 'Quevedo'],
            ['name_provinces' => 'Los Ríos', 'name_canton' => 'Quinsaloma'],
            ['name_provinces' => 'Los Ríos', 'name_canton' => 'Urdaneta'],
            ['name_provinces' => 'Los Ríos', 'name_canton' => 'Valencia'],
            ['name_provinces' => 'Los Ríos', 'name_canton' => 'Ventanas'],
            ['name_provinces' => 'Los Ríos', 'name_canton' => 'Vinces'],
            
            // Provincia de Manabí
            ['name_provinces' => 'Manabí', 'name_canton' => 'Portoviejo'],
            ['name_provinces' => 'Manabí', 'name_canton' => 'Bolívar'],
            ['name_provinces' => 'Manabí', 'name_canton' => 'Chone'],
            ['name_provinces' => 'Manabí', 'name_canton' => 'El Carmen'],
            ['name_provinces' => 'Manabí', 'name_canton' => 'Flavio Alfaro'],
            ['name_provinces' => 'Manabí', 'name_canton' => 'Jama'],
            ['name_provinces' => 'Manabí', 'name_canton' => 'Jaramijó'],
            ['name_provinces' => 'Manabí', 'name_canton' => 'Jipijapa'],
            ['name_provinces' => 'Manabí', 'name_canton' => 'Junín'],
            ['name_provinces' => 'Manabí', 'name_canton' => 'Manta'],
            ['name_provinces' => 'Manabí', 'name_canton' => 'Montecristi'],
            ['name_provinces' => 'Manabí', 'name_canton' => 'Olmedo'],
            ['name_provinces' => 'Manabí', 'name_canton' => 'Paján'],
            ['name_provinces' => 'Manabí', 'name_canton' => 'Pedernales'],
            ['name_provinces' => 'Manabí', 'name_canton' => 'Pichincha'],
            ['name_provinces' => 'Manabí', 'name_canton' => 'Rocafuerte'],
            ['name_provinces' => 'Manabí', 'name_canton' => 'Santa Ana'],
            ['name_provinces' => 'Manabí', 'name_canton' => 'Sucre'],
            ['name_provinces' => 'Manabí', 'name_canton' => 'Tosagua'],
            ['name_provinces' => 'Manabí', 'name_canton' => '24 de Mayo'],
            
            // Provincia de Morona Santiago
            ['name_provinces' => 'Morona Santiago', 'name_canton' => 'Macas'],
            ['name_provinces' => 'Morona Santiago', 'name_canton' => 'Gualaquiza'],
            ['name_provinces' => 'Morona Santiago', 'name_canton' => 'Huamboya'],
            ['name_provinces' => 'Morona Santiago', 'name_canton' => 'Limón Indanza'],
            ['name_provinces' => 'Morona Santiago', 'name_canton' => 'Logroño'],
            ['name_provinces' => 'Morona Santiago', 'name_canton' => 'Morona'],
            ['name_provinces' => 'Morona Santiago', 'name_canton' => 'Pablo Sexto'],
            ['name_provinces' => 'Morona Santiago', 'name_canton' => 'Palora'],
            ['name_provinces' => 'Morona Santiago', 'name_canton' => 'San Juan Bosco'],
            ['name_provinces' => 'Morona Santiago', 'name_canton' => 'Santiago de Méndez'],
            ['name_provinces' => 'Morona Santiago', 'name_canton' => 'Sucúa'],
            ['name_provinces' => 'Morona Santiago', 'name_canton' => 'Taisha'],
            ['name_provinces' => 'Morona Santiago', 'name_canton' => 'Tiwintza'],
            
            // Provincia de Napo
            ['name_provinces' => 'Napo', 'name_canton' => 'Tena'],
            ['name_provinces' => 'Napo', 'name_canton' => 'Archidona'],
            ['name_provinces' => 'Napo', 'name_canton' => 'Carlos Julio Arosemena Tola'],
            ['name_provinces' => 'Napo', 'name_canton' => 'El Chaco'],
            ['name_provinces' => 'Napo', 'name_canton' => 'Quijos'],
            
            // Provincia de Orellana
            ['name_provinces' => 'Orellana', 'name_canton' => 'Francisco de Orellana'],
            ['name_provinces' => 'Orellana', 'name_canton' => 'Aguarico'],
            ['name_provinces' => 'Orellana', 'name_canton' => 'La Joya de los Sachas'],
            ['name_provinces' => 'Orellana', 'name_canton' => 'Loreto'],
            
            // Provincia de Pastaza
            ['name_provinces' => 'Pastaza', 'name_canton' => 'Puyo'],
            ['name_provinces' => 'Pastaza', 'name_canton' => 'Arajuno'],
            ['name_provinces' => 'Pastaza', 'name_canton' => 'Mera'],
            ['name_provinces' => 'Pastaza', 'name_canton' => 'Pastaza'],
            ['name_provinces' => 'Pastaza', 'name_canton' => 'Santa Clara'],
            
            // Provincia de Pichincha
            ['name_provinces' => 'Pichincha', 'name_canton' => 'Quito'],
            ['name_provinces' => 'Pichincha', 'name_canton' => 'Cayambe'],
            ['name_provinces' => 'Pichincha', 'name_canton' => 'Mejía'],
            ['name_provinces' => 'Pichincha', 'name_canton' => 'Pedro Moncayo'],
            ['name_provinces' => 'Pichincha', 'name_canton' => 'Pedro Vicente Maldonado'],
            ['name_provinces' => 'Pichincha', 'name_canton' => 'Puerto Quito'],
            ['name_provinces' => 'Pichincha', 'name_canton' => 'Rumiñahui'],
            ['name_provinces' => 'Pichincha', 'name_canton' => 'San Miguel de Los Bancos'],
            
            // Provincia de Santa Elena
            ['name_provinces' => 'Santa Elena', 'name_canton' => 'Santa Elena'],
            ['name_provinces' => 'Santa Elena', 'name_canton' => 'La Libertad'],
            ['name_provinces' => 'Santa Elena', 'name_canton' => 'Salinas'],
            
            // Provincia de Santo Domingo de los Tsáchilas
            ['name_provinces' => 'Santo Domingo de los Tsáchilas', 'name_canton' => 'Santo Domingo'],
            ['name_provinces' => 'Santo Domingo de los Tsáchilas', 'name_canton' => 'La Concordia'],
            
            // Provincia de Sucumbíos
            ['name_provinces' => 'Sucumbíos', 'name_canton' => 'Lago Agrio'],
            ['name_provinces' => 'Sucumbíos', 'name_canton' => 'Cascales'],
            ['name_provinces' => 'Sucumbíos', 'name_canton' => 'Cuyabeno'],
            ['name_provinces' => 'Sucumbíos', 'name_canton' => 'Gonzalo Pizarro'],
            ['name_provinces' => 'Sucumbíos', 'name_canton' => 'Putumayo'],
            ['name_provinces' => 'Sucumbíos', 'name_canton' => 'Shushufindi'],
            ['name_provinces' => 'Sucumbíos', 'name_canton' => 'Sucumbíos'],
            
            // Provincia de Tungurahua
            ['name_provinces' => 'Tungurahua', 'name_canton' => 'Ambato'],
            ['name_provinces' => 'Tungurahua', 'name_canton' => 'Baños'],
            ['name_provinces' => 'Tungurahua', 'name_canton' => 'Cevallos'],
            ['name_provinces' => 'Tungurahua', 'name_canton' => 'Mocha'],
            ['name_provinces' => 'Tungurahua', 'name_canton' => 'Patate'],
            ['name_provinces' => 'Tungurahua', 'name_canton' => 'Pelileo'],
            ['name_provinces' => 'Tungurahua', 'name_canton' => 'Píllaro'],
            ['name_provinces' => 'Tungurahua', 'name_canton' => 'Quero'],
            ['name_provinces' => 'Tungurahua', 'name_canton' => 'Tisaleo'],
            
            // Provincia de Zamora Chinchipe
            ['name_provinces' => 'Zamora Chinchipe', 'name_canton' => 'Zamora'],
            ['name_provinces' => 'Zamora Chinchipe', 'name_canton' => 'Centinela del Cóndor'],
            ['name_provinces' => 'Zamora Chinchipe', 'name_canton' => 'Chinchipe'],
            ['name_provinces' => 'Zamora Chinchipe', 'name_canton' => 'El Pangui'],
            ['name_provinces' => 'Zamora Chinchipe', 'name_canton' => 'Nangaritza'],
            ['name_provinces' => 'Zamora Chinchipe', 'name_canton' => 'Palanda'],
            ['name_provinces' => 'Zamora Chinchipe', 'name_canton' => 'Paquisha'],
            ['name_provinces' => 'Zamora Chinchipe', 'name_canton' => 'Yacuambi'],
            ['name_provinces' => 'Zamora Chinchipe', 'name_canton' => 'Yantzaza'],
        ];

        // Insertar datos en la tabla
        foreach ($locations as $location) {
            Locate::create($location);
        }
    }
}
