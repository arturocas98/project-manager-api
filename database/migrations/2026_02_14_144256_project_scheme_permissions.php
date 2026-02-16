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
        Schema::create('scheme_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('permission_scheme_id');
            $table->foreign('permission_scheme_id')->references('id')->on('project_permission_schemes')->onDelete('Cascade');
            $table->unsignedBigInteger('project_permission_id');
            $table->foreign('project_permission_id')->references('id')->on('project_permissions')->onDelete('Cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheme_permissions');
    }
};
