<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260305143512 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C4B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE moderation_action_log ADD context JSON DEFAULT NULL, CHANGE reason reason LONGTEXT DEFAULT NULL, CHANGE moderator_id moderator_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE moderation_action_log ADD CONSTRAINT FK_835117CCD0AFA354 FOREIGN KEY (moderator_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE moderation_action_log ADD CONSTRAINT FK_835117CC4B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE moderation_action_log ADD CONSTRAINT FK_835117CCF8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post ADD comment_count INT DEFAULT 0 NOT NULL, ADD report_count INT DEFAULT 0 NOT NULL, ADD reaction_score INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('DROP INDEX uniq_user_comment_report ON report');
        $this->addSql('DROP INDEX uniq_user_post_report ON report');
        $this->addSql('ALTER TABLE report CHANGE reason reason LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F77844B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784F8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vote ADD guest_key VARCHAR(64) DEFAULT NULL, ADD guest_ip_hash VARCHAR(128) DEFAULT NULL, DROP guest_ip');
        $this->addSql('ALTER TABLE vote ADD CONSTRAINT FK_5A108564A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vote ADD CONSTRAINT FK_5A1085644B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CF675F31B');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C4B89032C');
        $this->addSql('ALTER TABLE moderation_action_log DROP FOREIGN KEY FK_835117CCD0AFA354');
        $this->addSql('ALTER TABLE moderation_action_log DROP FOREIGN KEY FK_835117CC4B89032C');
        $this->addSql('ALTER TABLE moderation_action_log DROP FOREIGN KEY FK_835117CCF8697D13');
        $this->addSql('ALTER TABLE moderation_action_log DROP context, CHANGE reason reason VARCHAR(255) DEFAULT NULL, CHANGE moderator_id moderator_id INT NOT NULL');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DF675F31B');
        $this->addSql('ALTER TABLE post DROP comment_count, DROP report_count, DROP reaction_score');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F7784A76ED395');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F77844B89032C');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F7784F8697D13');
        $this->addSql('ALTER TABLE report CHANGE reason reason VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_user_comment_report ON report (user_id, comment_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_user_post_report ON report (user_id, post_id)');
        $this->addSql('ALTER TABLE vote DROP FOREIGN KEY FK_5A108564A76ED395');
        $this->addSql('ALTER TABLE vote DROP FOREIGN KEY FK_5A1085644B89032C');
        $this->addSql('ALTER TABLE vote ADD guest_ip VARCHAR(45) DEFAULT NULL, DROP guest_key, DROP guest_ip_hash');
    }
}
