<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRoomFieldsToCommonsTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('commons', function (Blueprint $table) {
            $table->string('room_prihod')->nullable();
            $table->string('room_detskaya')->nullable();
            $table->string('room_kladovaya')->nullable();
            $table->string('room_kukhni_i_gostinaya')->nullable();
            $table->string('room_gostevoi_sanuzel')->nullable();
            $table->string('room_gostinaya')->nullable();
            $table->string('room_rabocee_mesto')->nullable();
            $table->string('room_stolovaya')->nullable();
            $table->string('room_vannaya')->nullable();
            $table->string('room_kukhnya')->nullable();
            $table->string('room_kabinet')->nullable();
            $table->string('room_spalnya')->nullable();
            $table->string('room_garderobnaya')->nullable();
            $table->string('room_druge')->nullable();
            $table->text('rooms')->nullable(); // Для хранения JSON со всеми выбранными комнатами
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('commons', function (Blueprint $table) {
            $table->dropColumn([
                'room_prihod',
                'room_detskaya',
                'room_kladovaya',
                'room_kukhni_i_gostinaya',
                'room_gostevoi_sanuzel',
                'room_gostinaya',
                'room_rabocee_mesto',
                'room_stolovaya',
                'room_vannaya',
                'room_kukhnya',
                'room_kabinet',
                'room_spalnya',
                'room_garderobnaya',
                'room_druge',
                'rooms'
            ]);
        });
    }
}
