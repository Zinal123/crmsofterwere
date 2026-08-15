<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('jobs')->cascadeOnDelete();
            // product.id is a plain SIGNED `int` on this app's live schema
            // (predates Laravel's bigint id() convention) - must match
            // exactly or MySQL refuses the FK (errno 150, confirmed live on
            // the quotation_items migration this one runs right after).
            $table->integer('product_id');
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();
            $table->foreign('product_id')->references('id')->on('product')->restrictOnDelete();
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_materials');
    }
};
