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
        Schema::table('clientes', function (Blueprint $table) {
            $table->uuid('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->boolean('can_delete_payments')->nullable()->default(false);
            $table->boolean('can_add_remote_credit')->nullable()->default(false);
            $table->boolean('can_add_edit_machine')->nullable()->default(false);
            $table->boolean('is_employee')->nullable()->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn([
                'parent_id',
                'can_delete_payments',
                'can_add_remote_credit',
                'can_add_edit_machine',
                'is_employee',
            ]);
        });
    }
};
