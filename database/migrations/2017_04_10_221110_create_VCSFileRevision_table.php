<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVCSFileRevisionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('VCSFileRevision', function (Blueprint $table) {
            $table->bigIncrements('Id');

            $table->string('Name');

            $table->bigInteger('FileId')->unsigned();
//            $table->foreign('FileId')->references('Id')->on('VCSFile');

            $table->dateTimeTz('Date');
            $table->longText('Comment');

            $table->bigInteger('PreviousRevisionId')->unsigned();
//            $table->foreign('PreviousRevisionId')->references('Id')->on('VCSFileRevision');

            $table->string('Alias');

            $table->bigInteger('ProjectLOC')->unsigned();
            $table->string('CommitterId');
            $table->string('Extension');
            $table->string('ExtensionId');

            $table->integer('FiletypeId')->unsigned();
//            $table->foreign('FiletypeId')->references('Id')->on('VCSFileTypes');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('VCSFileRevision');
    }
    /**
     *
     CREATE TABLE `projects` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `identifier` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    `organization_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'framework',
    `private` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    `language` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    `description` text COLLATE utf8_unicode_ci NOT NULL,
    `homepage` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    `api_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    `web_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    `commits_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    `issues_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    `prs_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    `date_created` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    `default_branch` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    `size` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    `merges_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    `labels_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    `languages_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    `contributors_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    `clone_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `projects_identifier_unique` (`identifier`),
    UNIQUE KEY `projects_name_unique` (`name`),
    UNIQUE KEY `projects_api_url_unique` (`api_url`),
    UNIQUE KEY `projects_web_url_unique` (`web_url`),
    UNIQUE KEY `projects_commits_url_unique` (`commits_url`),
    UNIQUE KEY `projects_issues_url_unique` (`issues_url`),
    UNIQUE KEY `projects_prs_url_unique` (`prs_url`)
    ) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
     */
}
