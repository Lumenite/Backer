<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBackupSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('backer.connection', config('database.default')))
            ->create('backup_settings', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('model');
                $table->string('filename');
                $table->string('status');
                $table->json('logs');
                $table->unsignedBigInteger('total')->default(0);
                $table->date('from');
                $table->date('to');
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
        Schema::dropIfExists('backup_settings');
    }
}
