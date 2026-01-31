<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * This migration sets the controller and title for the index page.
 */
final class Version20260131182855 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'This migration sets the controller and title for the index page.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE documents_page SET controller = "App\\\\Controller\\\\IndexController::indexAction", title = "SemanticSearch" WHERE id = 1');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE documents_page SET controller = "App\\\\Controller\\\\DefaultController::defaultAction", title = "" WHERE id = 1');
    }
}
