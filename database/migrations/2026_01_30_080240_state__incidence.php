<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('incidence_state', function (Blueprint $table) {
            $table->id();
            $table->enum('state', ['Progreso', 'Revision', 'Cerrado', 'Bloqueado', 'Terminado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidence_state');
    }
};
