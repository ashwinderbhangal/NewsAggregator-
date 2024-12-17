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
        Schema::table('articles', function (Blueprint $table) {
            $table->index('title');
            $table->index('category');
            $table->index('source');
            $table->index('published_at');
            $table->index('author');
            $table->index(['category', 'source']); // Composite index
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropIndex(['title']);
            $table->dropIndex(['category']);
            $table->dropIndex(['source']);
            $table->dropIndex(['published_at']);
            $table->dropIndex(['author']);
            $table->dropIndex(['articles_category_source_index']); // Drop composite index
        });
    }
};
