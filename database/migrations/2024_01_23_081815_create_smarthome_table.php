<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE TABLE smarthomes (
                id UUID PRIMARY KEY,
                description TEXT,
                properties JSONB,
                created_at TIMESTAMP(6),
                updated_at TIMESTAMP(6)
            );
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP TABLE smarthomes;");
    }
};