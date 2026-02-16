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
        Schema::create('project_role_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('permission_scheme_id');
            $table->foreign('permission_scheme_id')->references('id')->on('project_permission_schemes')->onDelete('Cascade');
            $table->unsignedBigInteger('project_role_id');
            $table->foreign('project_role_id')->references('id')->on('project_roles')->onDelete('Cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_role_permissions');
    }
};
