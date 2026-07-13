<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $addProvider = ! Schema::hasColumn(
            'chatbot_usage_logs',
            'provider'
        );

        $addRequestId = ! Schema::hasColumn(
            'chatbot_usage_logs',
            'request_id'
        );

        $addCachedInputTokens = ! Schema::hasColumn(
            'chatbot_usage_logs',
            'cached_input_tokens'
        );

        $addErrorCode = ! Schema::hasColumn(
            'chatbot_usage_logs',
            'error_code'
        );

        Schema::table(
            'chatbot_usage_logs',
            function (Blueprint $table) use (
                $addProvider,
                $addRequestId,
                $addCachedInputTokens,
                $addErrorCode
            ) {
                if ($addProvider) {
                    $table->string('provider', 30)
                        ->default('openai')
                        ->after('message_id');
                }

                if ($addRequestId) {
                    $table->string('request_id')
                        ->nullable()
                        ->after('model')
                        ->index();
                }

                if ($addCachedInputTokens) {
                    $table->unsignedInteger(
                        'cached_input_tokens'
                    )
                        ->nullable()
                        ->after('input_tokens');
                }

                if ($addErrorCode) {
                    $table->string('error_code')
                        ->nullable()
                        ->after('status');
                }
            }
        );
    }

    public function down(): void
    {
        $columns = [];

        foreach ([
            'provider',
            'request_id',
            'cached_input_tokens',
            'error_code',
        ] as $column) {
            if (
                Schema::hasColumn(
                    'chatbot_usage_logs',
                    $column
                )
            ) {
                $columns[] = $column;
            }
        }

        if ($columns !== []) {
            Schema::table(
                'chatbot_usage_logs',
                function (Blueprint $table) use ($columns) {
                    $table->dropColumn($columns);
                }
            );
        }
    }
};