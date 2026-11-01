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
    Schema::create('tickets', function (Blueprint $table) {
         $table->id();
         $table->foreignId('employe_id')->constrained('employe', 'idEmploye');
         $table->string('sujet');
         $table->text('description');
         $table->enum('categorie', ['Réseau','Téléphonie IP','Matériel','Autre']);
         $table->enum('urgence',   ['Basse','Moyenne','Haute','Critique'])->default('Moyenne');
         $table->enum('statut',    ['Nouveau','En cours','Résolu','Fermé'])->default('Nouveau');
         $table->timestamps();
     });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
