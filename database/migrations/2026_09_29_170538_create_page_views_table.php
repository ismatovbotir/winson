<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per public page view, written by App\Http\Middleware\TrackPageView.
     * No raw IPs: visitor_hash is a salted daily hash used only to count
     * unique visitors.
     */
    public function up(): void
    {
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->string('path')->index();
            $table->string('route_name')->nullable()->index();
            $table->nullableMorphs('subject'); // Product / Category / Article
            $table->string('locale', 5)->nullable();
            $table->string('visitor_hash', 64)->index();
            $table->string('referrer_host')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_views');
    }
};
