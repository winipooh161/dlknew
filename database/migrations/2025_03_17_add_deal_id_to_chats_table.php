<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDealIdToChatsTableNew extends Migration
{
    public function up()
    {
        Schema::table('chats', function (Blueprint $table) {
            if (!Schema::hasColumn('chats', 'deal_id')) {
                $table->unsignedBigInteger('deal_id')->nullable()->after('type');
                $table->foreign('deal_id')->references('id')->on('deals')->onDelete('cascade');
            }
        });
    }
    
    public function down()
    {
        Schema::table('chats', function (Blueprint $table) {
            if (Schema::hasColumn('chats', 'deal_id')) {
                $table->dropForeign(['deal_id']);
                $table->dropColumn('deal_id');
            }
        });
    }
}
