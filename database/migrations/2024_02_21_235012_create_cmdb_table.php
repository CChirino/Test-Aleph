<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cmdb', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('categoria_id');
            $table->string('identificador');
            $table->string('nombre');
            $table->json('campos_adicionales')->nullable();
            $table->timestamps();

            $table->index('categoria_id');
            $table->index('identificador');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cmdb');
    }
};
