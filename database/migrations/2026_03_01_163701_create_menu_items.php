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
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('menu_id')->unsigned();
            $table->foreign('menu_id', 'menu_id_fk')
                ->references('id')->on('menus')->cascadeOnDelete();

            $table->bigInteger('link_id')->unsigned();
            $table->foreign('link_id', 'menu_link_fk')
                ->references('id')->on('links');

            $table->integer('sequence')->default(1);

            $table->bigInteger('parent_menu_item_id')->unsigned()->nullable();
            $table->foreign('parent_menu_item_id', 'parent_menu_item_id_fk')
                ->references('id')->on('menu_items');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['menu_id', 'link_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
