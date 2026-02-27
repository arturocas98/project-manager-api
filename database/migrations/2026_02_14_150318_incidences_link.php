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
        Schema::create('incidences_link', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_incidence_id');
            $table->foreign('source_incidence_id')->references('id')->on('incidences')->onDelete('cascade');
            $table->unsignedBigInteger('target_incidence_id');
            $table->foreign('target_incidence_id')->references('id')->on('incidences')->onDelete('cascade');
            $table->string('type'); //'blocks', 'relates_to', 'duplicates', 'clones', 'Causes', 'Depends', 'Implements', 'Tests'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidences_link');
    }
};
