<?php

/**
 * @file SchemaMigration.php
 *
 * Copyright (c) 2014-2026 Simon Fraser University
 * Copyright (c) 2000-2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SchemaMigration
 * @brief Describe database table structures.
 */

namespace APP\plugins\generic\objectsForReview\classes\migration\install;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PKP\install\DowngradeNotSupportedException;

class SchemaMigration extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::create('objects_for_review', function (Blueprint $table) {
            $table->bigInteger('object_id')->autoIncrement();
            $table->bigInteger('submission_id')->nullable();
            $table->bigInteger('context_id');
            $table->bigInteger('user_id')->nullable();
            $table->string('identifier', 255);
            $table->string('identifier_type', 255);
            $table->string('resource_type', 255);
            $table->string('creator', 255);
            $table->datetime('date_created');
        });

        Schema::create('objects_for_review_settings', function (Blueprint $table) {
            $table->bigInteger('object_id');
            $table->string('locale', 14)->default('');
            $table->string('setting_name', 255);
            $table->longText('setting_value')->nullable();
            $table->string('setting_type', 6)->comment('(bool|int|float|string|object)');
            $table->index(['object_id'], 'objects_for_review_settings_id');
            $table->unique(['object_id', 'locale', 'setting_name'], 'objects_for_review_settings_pkey');
        });
    }

    /**
     * Rollback the migrations.
     */
    public function down(): void
    {
        throw new DowngradeNotSupportedException();
    }
}
