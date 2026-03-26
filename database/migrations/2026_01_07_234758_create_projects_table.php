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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('ContractNo')->unique();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('project_type');
            $table->date('start_date');
            $table->integer('duration_days')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedBigInteger('administrator');
            $table->foreign('administrator')->references('id')
                ->on('users')->onDelete('cascade');
            $table->string('administrator_email')->nullable();
            $table->string('contracted_company')->nullable();
            $table->string('last_phase')->nullable();
            $table->unsignedBigInteger('project_state_id');
            $table->foreign('project_state_id')->references('id')
                ->on('project_states')->onDelete('cascade');
            $table->text('objectContract')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
