<?php declare(strict_types=1);

namespace Wns\WnsArShopware\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;
use Wns\WnsArShopware\Entity\ImageBackgroundUpload\ImageBackgroundUploadDefinition;

class Migration1656499766CreateWnsImageBackgroundUploadTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1656499766;
    }

    public function update(Connection $connection): void
    {
        $connection->executeUpdate("
            CREATE TABLE IF NOT EXISTS `". ImageBackgroundUploadDefinition::ENTITY_NAME ."` (
                id binary(16) NOT NULL,
                media_id binary(16) NOT NULL,
                bg_upload_key varchar(255) DEFAULT NULL,
                bg_width int DEFAULT 0,
                left_img_position int DEFAULT 0,
                top_img_position int DEFAULT 0,
                active tinyint(1)  DEFAULT 1,
                created_at datetime(3) NOT NULL,
                updated_at datetime(3) DEFAULT NULL,
                PRIMARY KEY ( `id` )
            ) ENGINE = INNODB DEFAULT CHARSET = utf8;
           ");
    }

    public function updateDestructive(Connection $connection): void
    {
        $connection->executeUpdate("
            DROP TABLE IF EXISTS `". ImageBackgroundUploadDefinition::ENTITY_NAME ."`;
        ");
    }
}
