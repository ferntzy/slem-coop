<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('savings_account_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('savings_account_transactions', 'transaction_date')) {
                $table->date('transaction_date')->nullable()->after('type');
            }

            if (! Schema::hasColumn('savings_account_transactions', 'direction')) {
                $table->string('direction', 20)->nullable()->after('type');
            }

            if (! Schema::hasColumn('savings_account_transactions', 'amount')) {
                $table->decimal('amount', 14, 2)->nullable()->after('withdrawal');
            }

            if (! Schema::hasColumn('savings_account_transactions', 'reference_no')) {
                $table->string('reference_no')->nullable()->after('proof_of_transaction');
            }

            if (! Schema::hasColumn('savings_account_transactions', 'posted_by_user_id')) {
                $table->unsignedBigInteger('posted_by_user_id')->nullable()->after('reference_no');
            }
        });
    }

    public function down(): void
    {
        Schema::table('savings_account_transactions', function (Blueprint $table) {
            $table->dropColumn([
                'transaction_date',
                'direction',
                'amount',
                'reference_no',
                'posted_by_user_id',
            ]);
        });
    }
};
