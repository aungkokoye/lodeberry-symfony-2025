<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250115111805 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
//        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('
            CREATE TABLE `order` (
                `id` INT AUTO_INCREMENT NOT NULL,
                `order_by_id` INT NOT NULL,
                `uuid` CHAR(36) NOT NULL,
                `address` VARCHAR(255) NOT NULL,
                `status` INT NOT NULL,
                `created_at` DATETIME NOT NULL,
                `updated_at` DATETIME NOT NULL,
                INDEX IDX_F5299398297954F9 (`order_by_id`),
                PRIMARY KEY(`id`))
                DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE `product_order` (
                `id` INT AUTO_INCREMENT NOT NULL,
                `product_id` INT NOT NULL,
                `order_ref_id` INT NOT NULL,
                `quantity` INT NOT NULL,
                INDEX IDX_5475E8C44584665A (`product_id`),
                INDEX IDX_5475E8C4E238517C (`order_ref_id`),
                PRIMARY KEY(`id`))
                DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
                
        ');
        $this->addSql(
            'ALTER TABLE `order` ADD CONSTRAINT FK_F5299398297954F9 FOREIGN KEY (`order_by_id`) REFERENCES user (`id`)'
        );
        $this->addSql(
            'ALTER TABLE `product_order` ADD CONSTRAINT FK_5475E8C44584665A
                    FOREIGN KEY (`product_id`) REFERENCES product (`id`)'
        );
        $this->addSql(
            'ALTER TABLE `product_order` ADD CONSTRAINT FK_5475E8C4E238517C
                    FOREIGN KEY (`order_ref_id`) REFERENCES `order` (`id`)'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398297954F9');
        $this->addSql('ALTER TABLE `product_order` DROP FOREIGN KEY FK_5475E8C44584665A');
        $this->addSql('ALTER TABLE `product_order` DROP FOREIGN KEY FK_5475E8C4E238517C');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE `product_order`');
    }
}
