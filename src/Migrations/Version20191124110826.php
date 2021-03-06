<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191124110826 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE IF EXISTS ozg_implementation_project_service_system');
        $this->addSql('DROP TABLE IF EXISTS ozg_implementation_project_solution');
        $this->addSql('DROP TABLE IF EXISTS ozg_implementation_project');
        $this->addSql('DROP TABLE IF EXISTS ozg_implementation_status');
        $this->addSql('CREATE TABLE IF NOT EXISTS ozg_implementation_status (id INT AUTO_INCREMENT NOT NULL, level INT NOT NULL, description LONGTEXT DEFAULT NULL, modified_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', name VARCHAR(255) DEFAULT NULL, hidden TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS ozg_implementation_project (id INT AUTO_INCREMENT NOT NULL, status_id INT DEFAULT NULL, description LONGTEXT DEFAULT NULL, modified_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', name VARCHAR(255) DEFAULT NULL, hidden TINYINT(1) NOT NULL, INDEX IDX_56C75D246BF700BD (status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS ozg_implementation_project_service_system (implementation_project_id INT NOT NULL, service_system_id INT NOT NULL, INDEX IDX_A69E4012CC68C60 (implementation_project_id), INDEX IDX_A69E401880415EF (service_system_id), PRIMARY KEY(implementation_project_id, service_system_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS ozg_implementation_project_solution (implementation_project_id INT NOT NULL, solution_id INT NOT NULL, INDEX IDX_73A013EC2CC68C60 (implementation_project_id), INDEX IDX_73A013EC1C0BE183 (solution_id), PRIMARY KEY(implementation_project_id, solution_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ozg_implementation_project ADD CONSTRAINT FK_56C75D246BF700BD FOREIGN KEY (status_id) REFERENCES ozg_implementation_status (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE ozg_implementation_project_service_system ADD CONSTRAINT FK_A69E4012CC68C60 FOREIGN KEY (implementation_project_id) REFERENCES ozg_implementation_project (id)');
        $this->addSql('ALTER TABLE ozg_implementation_project_service_system ADD CONSTRAINT FK_A69E401880415EF FOREIGN KEY (service_system_id) REFERENCES ozg_service_system (id)');
        $this->addSql('ALTER TABLE ozg_implementation_project_solution ADD CONSTRAINT FK_73A013EC2CC68C60 FOREIGN KEY (implementation_project_id) REFERENCES ozg_implementation_project (id)');
        $this->addSql('ALTER TABLE ozg_implementation_project_solution ADD CONSTRAINT FK_73A013EC1C0BE183 FOREIGN KEY (solution_id) REFERENCES ozg_solution (id)');
        $this->addSql('DROP TABLE IF EXISTS ozg_commune_service');
        $this->addSql('DROP TABLE IF EXISTS ozg_form_servers_specialized_procedures');
        $this->addSql('DROP TABLE IF EXISTS ozg_payment_types_specialized_procedures');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ozg_implementation_project DROP FOREIGN KEY FK_56C75D246BF700BD');
        $this->addSql('ALTER TABLE ozg_implementation_project_service_system DROP FOREIGN KEY FK_A69E4012CC68C60');
        $this->addSql('ALTER TABLE ozg_implementation_project_solution DROP FOREIGN KEY FK_73A013EC2CC68C60');
        $this->addSql('CREATE TABLE ozg_commune_service (id INT AUTO_INCREMENT NOT NULL, commune_id INT DEFAULT NULL, service_id INT DEFAULT NULL, maturity_id INT DEFAULT NULL, service_provider_id INT DEFAULT NULL, modified_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', description LONGTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci, INDEX IDX_EC42799D131A4F72 (commune_id), INDEX IDX_EC42799D5074221B (maturity_id), INDEX IDX_EC42799DED5CA9E6 (service_id), INDEX IDX_EC42799DC6C98E06 (service_provider_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE ozg_form_servers_specialized_procedures (form_server_id INT NOT NULL, specialized_procedure_id INT NOT NULL, INDEX IDX_A4632DC3452D2882 (specialized_procedure_id), INDEX IDX_A4632DC3694459BB (form_server_id), PRIMARY KEY(form_server_id, specialized_procedure_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE ozg_payment_types_specialized_procedures (payment_type_id INT NOT NULL, specialized_procedure_id INT NOT NULL, INDEX IDX_B8DCC7F1DC058279 (payment_type_id), INDEX IDX_B8DCC7F1452D2882 (specialized_procedure_id), PRIMARY KEY(payment_type_id, specialized_procedure_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE ozg_commune_service ADD CONSTRAINT FK_EC42799D131A4F72 FOREIGN KEY (commune_id) REFERENCES ozg_commune (id)');
        $this->addSql('ALTER TABLE ozg_commune_service ADD CONSTRAINT FK_EC42799D5074221B FOREIGN KEY (maturity_id) REFERENCES ozg_maturity (id)');
        $this->addSql('ALTER TABLE ozg_commune_service ADD CONSTRAINT FK_EC42799DC6C98E06 FOREIGN KEY (service_provider_id) REFERENCES ozg_service_provider (id)');
        $this->addSql('ALTER TABLE ozg_commune_service ADD CONSTRAINT FK_EC42799DED5CA9E6 FOREIGN KEY (service_id) REFERENCES ozg_service (id)');
        $this->addSql('ALTER TABLE ozg_form_servers_specialized_procedures ADD CONSTRAINT FK_A4632DC3452D2882 FOREIGN KEY (specialized_procedure_id) REFERENCES ozg_specialized_procedure (id)');
        $this->addSql('ALTER TABLE ozg_form_servers_specialized_procedures ADD CONSTRAINT FK_A4632DC3694459BB FOREIGN KEY (form_server_id) REFERENCES ozg_form_server (id)');
        $this->addSql('ALTER TABLE ozg_payment_types_specialized_procedures ADD CONSTRAINT FK_B8DCC7F1452D2882 FOREIGN KEY (specialized_procedure_id) REFERENCES ozg_specialized_procedure (id)');
        $this->addSql('ALTER TABLE ozg_payment_types_specialized_procedures ADD CONSTRAINT FK_B8DCC7F1DC058279 FOREIGN KEY (payment_type_id) REFERENCES ozg_payment_type (id)');
        $this->addSql('DROP TABLE ozg_implementation_status');
        $this->addSql('DROP TABLE ozg_implementation_project');
        $this->addSql('DROP TABLE ozg_implementation_project_service_system');
        $this->addSql('DROP TABLE ozg_implementation_project_solution');
    }
}
