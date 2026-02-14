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
        Schema::create('incidences', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedBigInteger('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('Cascade');
            $table->unsignedBigInteger('incidence_type_id');
            $table->foreign('incidence_type_id')->references('id')->on('incidence_type')->onDelete('Cascade');
            $table->unsignedBigInteger('incidence_priority_id');
            $table->foreign('incidence_priority_id')->references('id')->on('incidence_priority')->onDelete('Cascade');
            $table->unsignedBigInteger('incidence_state_id');
            $table->foreign('incidence_state_id')->references('id')->on('incidence_state')->onDelete('Cascade');
            $table->unsignedBigInteger('parent_incidence_id');
            $table->foreign('parent_incidence_id')->references('id')->on('incidences')->onDelete('Cascade');
            $table->unsignedBigInteger('created_by_id');
            $table->foreign('created_by_id')->references('id')->on('users')->onDelete('Cascade');
            $table->unsignedBigInteger('assigned_user_id');
            $table->foreign('assigned_user_id')->references('id')->on('users')->onDelete('Cascade');
            $table->text('description');
            $table->date('date');
            $table->enum('priority', ['alta', 'media', 'baja']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidences');
    }
};