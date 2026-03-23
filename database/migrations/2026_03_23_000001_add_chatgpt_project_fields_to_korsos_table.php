<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChatgptProjectFieldsToKorsosTable extends Migration
{
    public function up()
    {
        Schema::table('korsos', function (Blueprint $table) {
            $table->boolean('is_chatgpt_project')->default(false)->after('onlinemarketing_item');
            $table->string('chatgpt_project_name')->nullable()->after('is_chatgpt_project');
            $table->text('chatgpt_introduction_reason')->nullable()->after('chatgpt_project_name');
            $table->text('chatgpt_goal')->nullable()->after('chatgpt_introduction_reason');
            $table->text('chatgpt_process_steps')->nullable()->after('chatgpt_goal');
            $table->boolean('chatgpt_has_existing_process')->nullable()->after('chatgpt_process_steps');
            $table->boolean('chatgpt_has_output_examples')->nullable()->after('chatgpt_has_existing_process');
            $table->boolean('chatgpt_has_knowledge_base')->nullable()->after('chatgpt_has_output_examples');
            $table->text('chatgpt_output_examples')->nullable()->after('chatgpt_has_knowledge_base');
            $table->text('chatgpt_knowledge_base')->nullable()->after('chatgpt_output_examples');
            $table->text('chatgpt_additional_requirements')->nullable()->after('chatgpt_knowledge_base');
        });
    }

    public function down()
    {
        Schema::table('korsos', function (Blueprint $table) {
            $table->dropColumn([
                'is_chatgpt_project',
                'chatgpt_project_name',
                'chatgpt_introduction_reason',
                'chatgpt_goal',
                'chatgpt_process_steps',
                'chatgpt_has_existing_process',
                'chatgpt_has_output_examples',
                'chatgpt_has_knowledge_base',
                'chatgpt_output_examples',
                'chatgpt_knowledge_base',
                'chatgpt_additional_requirements',
            ]);
        });
    }
}
