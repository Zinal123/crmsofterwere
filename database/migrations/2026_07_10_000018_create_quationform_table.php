<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quationform', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id')->nullable();
            $table->string('clientname')->nullable();
            $table->string('companyname')->nullable();
            $table->string('gstno')->nullable();
            $table->string('companyaddress')->nullable();
            $table->integer('bank')->nullable();
            $table->string('email')->nullable();
            $table->integer('phone')->nullable();
            $table->date('date')->nullable();
            $table->date('reminderdate')->nullable();
            $table->integer('softweredetails')->nullable();
            $table->integer('lasercutting')->nullable();
            $table->integer('focus')->nullable();
            $table->integer('power')->nullable();
            $table->string('inputpower')->nullable();
            $table->integer('cuttingway')->nullable();
            $table->string('cncspan')->nullable();
            $table->string('cnslenght')->nullable();
            $table->string('cuttingrang')->nullable();
            $table->string('liftingheight')->nullable();
            $table->string('headquantity')->nullable();
            $table->integer('cuttingthickess')->nullable();
            $table->string('strokespeed')->nullable();
            $table->string('cuttingspeed')->nullable();
            $table->string('drive')->nullable();
            $table->integer('motor')->nullable();
            $table->integer('motortype')->nullable();
            $table->integer('gearbox')->nullable();
            $table->integer('rack')->nullable();
            $table->integer('software')->nullable();
            $table->text('description')->nullable();
            $table->text('description1')->nullable();
            $table->text('description2')->nullable();
            $table->string('amount')->nullable();
            $table->string('amount1')->nullable();
            $table->string('amount2')->nullable();
            $table->text('optionparthyscope')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quationform');
    }
};
