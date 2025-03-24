<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSkippedPagesToCommonsTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('commons', function (Blueprint $table) {
            $table->text('skipped_pages')->nullable()->after('rooms');
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
            $table->dropColumn('skipped_pages');
        });
    }
}
