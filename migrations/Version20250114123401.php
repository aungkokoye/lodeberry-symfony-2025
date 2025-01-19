<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250114123401 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `product` (
         `id` INT AUTO_INCREMENT NOT NULL,
         `name` VARCHAR(255) NOT NULL,
         `uuid` CHAR(36) NOT NULL UNIQUE,
         `price` INT NOT NULL,
         `active` BOOLEAN NOT NULL DEFAULT TRUE,
         `description` LONGTEXT NOT NULL,
         `image` VARCHAR(255) NOT NULL,
          PRIMARY KEY(`id`)
          )
          DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`  ENGINE = InnoDB'
        );

        $this->addSql('INSERT INTO `product` (`name`,`uuid`, `price`, `active`, `description`, `image`)
            VALUES
                (
                 \'An Unlikely Prisoner\',
                 \'decfaa41-d67a-11ef-a6a7-0242ac140003\',
                 1999,
                 TRUE,
                 \'Genius discounts at this property are subject to book dates, stay dates and other available deals.\',
                 \'decfaa41-d67a-11ef-a6a7-0242ac140003.jpg\');'
        );

        $this->addSql('INSERT INTO `product` (`name`,`uuid`, `price`, `active`, `description`, `image`)
            VALUES
                (
                 \'Burma 44\',
                 \'decfd0e2-d67a-11ef-a6a7-0242ac140003\',
                 999,
                 TRUE,
                 \'This audio edition includes an exclusive Q&A between James Holland and Al Murray. In February 1944, a rag-tag collection of clerks, drivers, doctors, muleteers and other base troops, stiffened by a few dogged Yorkshiremen and a handful of tank crews managed to hold out against some of the finest infantry in the Japanese Army and then defeat them in what was one of the most astonishing battles of the Second World War. What became known as the Defence of the Admin Box, fought amongst the paddy fields and jungle of Northern Arakan over a 15-day period, turned the battle for Burma. Not only was it the first decisive victory for British troops against the Japanese, more significantly, it demonstrated how the Japanese could be defeated. The lessons learned in this tiny and otherwise insignificant corner of the Far East, set up the campaign in Burma that would follow, as General Slim’s Fourteenth Army finally turned defeat into victory. Burma 44 is a tale of incredible drama. As gripping as the story of Rorke drift, as momentous as the battle for the Ardennes, the Admin Box was a triumph of human grit and heroism and remains one of the most significant yet undervalued conflicts of World War Two.\',
                 \'decfd0e2-d67a-11ef-a6a7-0242ac140003.jpg\');'
        );

        $this->addSql('INSERT INTO `product` (`name`,`uuid`, `price`, `active`, `description`, `image`)
            VALUES
                (
                 \'Sun Flower Life\',
                 \'ded005a5-d67a-11ef-a6a7-0242ac140003\',
                 2999,
                 TRUE,
                 \'Genius discounts at this property are subject to book dates, stay dates and other available deals.\',
                 \'ded005a5-d67a-11ef-a6a7-0242ac140003.jpg\');'
        );



        $this->addSql('INSERT INTO `product` (`name`,`uuid`, `price`, `active`, `description`, `image`)
            VALUES
                (
                 \'Tiger\',
                 \'ded0256b-d67a-11ef-a6a7-0242ac140003\',
                 1999,
                 TRUE,
                 \'Genius discounts at this property are subject to book dates, stay dates and other available deals.\',
                 \'ded0256b-d67a-11ef-a6a7-0242ac140003.jpg\');'
        );

        $this->addSql('INSERT INTO `product` (`name`,`uuid`, `price`, `active`, `description`, `image`)
            VALUES
                (
                 \'Tiger Not Active\',
                 \'ded08eff-d67a-11ef-a6a7-0242ac140003\',
                 1999,
                 FALSE,
                 \'Genius discounts at this property are subject to book dates, stay dates and other available deals.\',
                 \'ded08eff-d67a-11ef-a6a7-0242ac140003.jpg\');'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE `product`');
    }
}
