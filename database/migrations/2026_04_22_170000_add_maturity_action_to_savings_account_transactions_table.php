<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('savings_account_transactions', function (Blueprint $table): void {
            if (! Schema::hasColumn('savings_account_transactions', 'maturity_action')) {
                $table->string('maturity_action')->nullable()->after('status');
            }

            if (! Schema::hasColumn('savings_account_transactions', 'maturity_action_selected_at')) {
                $table->timestamp('maturity_action_selected_at')->nullable()->after('maturity_action');
            }
        });
    }

    public function down(): void
    {
        Schema::table('savings_account_transactions', function (Blueprint $table): void {
            $columns = [];

            if (Schema::hasColumn('savings_account_transactions', 'maturity_action')) {
                $columns[] = 'maturity_action';
            }

            if (Schema::hasColumn('savings_account_transactions', 'maturity_action_selected_at')) {
                $columns[] = 'maturity_action_selected_at';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
