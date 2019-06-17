<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190617103124 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE app_user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE role (name VARCHAR(191) NOT NULL, PRIMARY KEY(name))');
        $this->addSql('CREATE TABLE user_role (user_id INT NOT NULL, role_name VARCHAR(191) NOT NULL, PRIMARY KEY(user_id, role_name))');
        $this->addSql('CREATE INDEX IDX_2DE8C6A3A76ED395 ON user_role (user_id)');
        $this->addSql('CREATE INDEX IDX_2DE8C6A3E09C0C92 ON user_role (role_name)');
        $this->addSql('COMMENT ON COLUMN user_role.user_id IS \'(DC2Type:msgphp_user_id)\'');
        $this->addSql('CREATE TABLE app_user (id INT NOT NULL, credential_email VARCHAR(191) NOT NULL, credential_password VARCHAR(255) NOT NULL, password_reset_token VARCHAR(191) DEFAULT NULL, password_requested_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_88BDF3E9A5D24B55 ON app_user (credential_email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_88BDF3E96B7BA4B6 ON app_user (password_reset_token)');
        $this->addSql('COMMENT ON COLUMN app_user.id IS \'(DC2Type:msgphp_user_id)\'');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3E09C0C92 FOREIGN KEY (role_name) REFERENCES role (name) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE user_role DROP CONSTRAINT FK_2DE8C6A3E09C0C92');
        $this->addSql('ALTER TABLE user_role DROP CONSTRAINT FK_2DE8C6A3A76ED395');
        $this->addSql('DROP SEQUENCE app_user_id_seq CASCADE');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE user_role');
        $this->addSql('DROP TABLE app_user');
    }
}
