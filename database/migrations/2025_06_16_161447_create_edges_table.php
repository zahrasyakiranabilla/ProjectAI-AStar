<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('edges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_node_id')->constrained('nodes')->onDelete('cascade');
            $table->foreignId('to_node_id')->constrained('nodes')->onDelete('cascade');
            $table->decimal('distance', 8, 4);
            $table->enum('road_type', ['highway', 'primary', 'secondary', 'residential']);
            $table->decimal('weight', 3, 2);
            $table->timestamps();

            $table->index(['from_node_id', 'to_node_id']);
            $table->index('road_type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('edges');
    }
};
