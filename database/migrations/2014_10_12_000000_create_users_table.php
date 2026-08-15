<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            // Plain signed auto-increment int, matching this app's actual
            // live schema (predates Laravel's bigint id() convention, same
            // drift as create_product_table/create_invoice_table - confirmed
            // via SHOW CREATE TABLE on production). Every migration with a
            // FK to users.id (jobs, job_photos, employees, audit_logs, etc.)
            // already uses plain integer() to match.
            $table->integer('id', true);
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            // Nullable: the live DB's `avatar` column is `varchar(255) DEFAULT NULL`,
            // not NOT NULL as this migration originally declared - the schema drifted
            // from this file at some point. User::$fillable also doesn't include
            // 'avatar'/'email_verified_at', so the seed insert below silently drops
            // both via mass assignment; not fixed here to avoid scope creep into the
            // auth system - documented as a known pre-existing quirk.
            $table->string('avatar')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
        User::create(['name' => 'admin','email' => 'admin@themesbrand.com','password' => Hash::make('12345678'),'email_verified_at'=>'2022-01-02 17:04:58','avatar' => 'avatar-1.jpg','created_at' => now(),]);
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
