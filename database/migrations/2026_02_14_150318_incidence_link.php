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
        Schema::table('incidence_link', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_incidences_id');
            $table->foreign('source_incidences_id')->references('id')->on('incidences')->onDelete('Cascade');
            $table->unsignedBigInteger('target_incidences_id');
            $table->foreign('target_incidence_id')->references('id')->on('incidences')->onDelete('Cascade');
            $table->enum('type', ['blocks', 'relates_to', 'duplicates', 'clones', 'Causes', 'Depends', 'Implements', 'Tests']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidence_link');
    }
};
