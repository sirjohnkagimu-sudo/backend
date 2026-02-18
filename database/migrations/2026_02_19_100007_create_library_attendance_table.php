<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_attendance', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->unsignedBigInteger('student_id');
            $table->string('student_name', 200)->nullable();
            $table->string('student_class', 50)->nullable();
            $table->time('entry_time');
            $table->time('exit_time')->nullable();
            $table->enum('purpose', ['reading', 'borrowing', 'returning', 'study', 'computer'])->default('reading');
            $table->decimal('reading_hours', 5, 2)->default(0);
            $table->integer('books_read')->default(0);
            $table->boolean('qr_code_scanned')->default(false);
            $table->enum('gate', ['main', 'side'])->default('main');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('library_students')->onDelete('cascade');
            $table->index(['tenant_id', 'entry_time']);
            $table->index(['tenant_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_attendance');
    }
};
