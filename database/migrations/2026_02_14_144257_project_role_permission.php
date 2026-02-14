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
        Schema::create('project_role_permission', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_permission_scheme_id');
            $table->foreign('project_permission_scheme_id')->references('id')->on('project_permission_scheme')->onDelete('Cascade');
            $table->unsignedBigInteger('project_role_id');
            $table->foreign('project_role_id')->references('id')->on('project_role')->onDelete('Cascade');
            $table->unsignedBigInteger('project_permission_id');
            $table->foreign('project_permission_id')->references('id')->on('project_permission')->onDelete('Cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_role_permission');
    }
};
