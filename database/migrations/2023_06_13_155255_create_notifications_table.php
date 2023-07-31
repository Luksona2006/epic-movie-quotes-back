<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('to_user')->constrained('users', 'id')->cascadeOnDelete();
            $table->foreignId('from_user')->constrained('users', 'id')->cascadeOnDelete();
            $table->foreignId('quote_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->boolean('seen')->default(false);
            $table->string('text')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};
