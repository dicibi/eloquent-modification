<?php

use Dicibi\EloquentModification\Models\Modification;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('modifications', static function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('modifiable_type');
            $table->uuid('modifiable_uuid')->nullable();
            $table->unsignedInteger('modifiable_id')->nullable();

            $table->string('identifier')->nullable();

            $table->string('action');
            $table->jsonb('state')->nullable();
            $table->jsonb('payloads')->nullable();
            $table->string('status')->default(Modification::STATUS_PENDING);
            $table->string('condition')->nullable();
            $table->string('info')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained()->references('id')->on('users');
            $table->foreignId('reviewed_by')->nullable()->constrained()->references('id')->on('users');
            $table->foreignId('applied_by')->nullable()->constrained()->references('id')->on('users');
            $table->dateTime('applied_at')->nullable();
            $table->timestamps();

            $table->index(['modifiable_id', 'modifiable_type'], 'modifiable_id_type_index');
            $table->index(['modifiable_uuid', 'modifiable_type'], 'modifiable_uuid_type_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modifications');
    }
};
