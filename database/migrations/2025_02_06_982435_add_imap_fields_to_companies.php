<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddImapFieldsToCompanies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('imap_server')->after('state')->nullable();
            $table->string('imap_user')->after('imap_server')->nullable();
            $table->string('imap_password')->after('imap_user')->nullable();
            $table->string('imap_port')->after('imap_password')->nullable();
            $table->string('imap_encryption')->after('imap_port')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('imap_server');
            $table->dropColumn('imap_user');
            $table->dropColumn('imap_password');
            $table->dropColumn('imap_port');
            $table->dropColumn('imap_encryption');
        });
    }
}
