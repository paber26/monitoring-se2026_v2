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
        Schema::create('petugas', function (Blueprint $table) {
            $table->id();
            $table->string('kode_identitas')->nullable()->unique();
            $table->string('nama')->nullable();
            $table->string('email')->nullable();
            $table->integer('open')->default(0);
            $table->integer('draft')->default(0);
            $table->integer('submitted_by_pencacah')->default(0);
            $table->integer('approved_by_pengawas')->default(0);
            $table->integer('rejected_by_pengawas')->default(0);
            $table->integer('submitted_respondent')->default(0);
            $table->integer('revoked_by_pengawas')->default(0);
            $table->integer('completed_by_admin_kabupaten')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petugas');
    }
};
