<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // No foreign key on source_id: source_type varies across Invoice /
        // VendorBill / SalaryPayment / DailyTransaction (future stages),
        // whose id columns aren't all the same underlying type (see the
        // product/invoice.id signed-int gotcha) - polymorphic reference by
        // design, not enforced at the DB level.
        Schema::create('tally_sync_queue', function (Blueprint $table) {
            $table->id();
            $table->string('source_type');
            $table->unsignedBigInteger('source_id');
            $table->string('voucher_type');
            $table->string('reference_no')->unique();
            $table->json('payload');
            $table->enum('status', ['pending', 'synced', 'failed'])->default('pending');
            $table->string('tally_voucher_id')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->index(['source_type', 'source_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tally_sync_queue');
    }
};
