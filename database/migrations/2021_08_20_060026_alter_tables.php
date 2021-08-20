<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('userappointments', function(Blueprint $table) {
            $table->integer('id_service')->after('id_barber');
        });

        Schema::table('barbers', function(Blueprint $table) {
            $table->string('longitude')->nullable()->after('avatar');
            $table->string('latitude')->nullable()->after('longitude');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('userappointments', function(Blueprint $table) {
            $table->dropColumn('id_service');
        });

        Schema::table('barbers', function(Blueprint $table) {
            $table->dropColumn('longitude');
            $table->dropColumn('latitude');
        });
    }
}
