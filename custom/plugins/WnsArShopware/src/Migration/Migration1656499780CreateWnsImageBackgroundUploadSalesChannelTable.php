<?php declare(strict_types=1);

namespace Wns\WnsArShopware\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;
use Wns\WnsArShopware\Entity\ImageBackgroundUpload\ImageBackgroundUploadDefinition;
use Wns\WnsArShopware\Entity\ImageBackgroundUploadSalesChannel\ImageBackgroundUploadSalesChannelDefinition;

class Migration1656499780CreateWnsImageBackgroundUploadSalesChannelTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1656499780;
    }

    public function update(Connection $connection): void
    {
        $connection->executeUpdate("
            CREATE TABLE IF NOT EXISTS `". ImageBackgroundUploadSalesChannelDefinition::ENTITY_NAME ."` (
                id binary(16) NOT NULL,
                image_background_upload_id binary(16) NOT NULL,
                sales_channel_id binary(16) NOT NULL,
                created_at datetime(3) NOT NULL,
                updated_at datetime(3) DEFAULT NULL,
                PRIMARY KEY ( `id` ),
                CONSTRAINT `img_upload_sales_channel_bg_upload_cascade_delete` FOREIGN KEY (`image_background_upload_id`)
                    REFERENCES `". ImageBackgroundUploadDefinition::ENTITY_NAME ."` (`id`) ON DELETE CASCADE
            ) ENGINE = INNODB DEFAULT CHARSET = utf8;
           ");
    }

    public function updateDestructive(Connection $connection): void
    {
        $connection->executeUpdate("
            DROP TABLE IF EXISTS `". ImageBackgroundUploadSalesChannelDefinition::ENTITY_NAME ."`;
        ");
    }
}
