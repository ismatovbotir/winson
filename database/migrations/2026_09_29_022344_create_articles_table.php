<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->date('published_at');
            $table->unsignedInteger('read_minutes')->default(4);
            $table->string('title_uz');
            $table->string('title_ru');
            $table->string('excerpt_uz');
            $table->string('excerpt_ru');
            $table->json('body_uz'); // array of paragraphs
            $table->json('body_ru');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
