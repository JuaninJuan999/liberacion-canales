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
        Schema::create('sesiones_usuario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('login_at');
            $table->timestamp('ultima_actividad')->nullable();
            $table->timestamp('logout_at')->nullable();
            $table->integer('duracion_minutos')->default(0);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'login_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesiones_usuario');
    }
};
