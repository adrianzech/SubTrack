<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250313194818 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE payment_method (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, UNIQUE INDEX UNIQ_7B61A1F65E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE subscriptions ADD payment_method_id INT DEFAULT NULL, DROP payment_method');
        $this->addSql('ALTER TABLE subscriptions ADD CONSTRAINT FK_4778A015AA1164F FOREIGN KEY (payment_method_id) REFERENCES payment_method (id)');
        $this->addSql('CREATE INDEX IDX_4778A015AA1164F ON subscriptions (payment_method_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE subscriptions DROP FOREIGN KEY FK_4778A015AA1164F');
        $this->addSql('DROP TABLE payment_method');
        $this->addSql('DROP INDEX IDX_4778A015AA1164F ON subscriptions');
        $this->addSql('ALTER TABLE subscriptions ADD payment_method VARCHAR(100) DEFAULT NULL, DROP payment_method_id');
    }
}
