<?php declare(strict_types=1);

namespace Wns\WnsArShopware\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Shopware\Core\Content\Media\Aggregate\MediaDefaultFolder\MediaDefaultFolderDefinition;
use Shopware\Core\Content\Media\Aggregate\MediaFolder\MediaFolderDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;
use Wns\WnsArShopware\Entity\ImageBackgroundUpload\ImageBackgroundUploadDefinition;

class Migration1657525319InsertDefaultFolder extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1657525319;
    }

    /**
     * @throws Exception
     */
    public function update(Connection $connection): void
    {
        $defaultFoderId = Uuid::randomBytes();
        $defaultFolder = [
            'id' => $defaultFoderId,
            'association_fields' => "[]",
            'entity' => ImageBackgroundUploadDefinition::ENTITY_NAME,
            'custom_fields' => null,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ];

        $connection->insert(MediaDefaultFolderDefinition::ENTITY_NAME, $defaultFolder);

        $mediaFolderId = Uuid::randomBytes();
        $folderName = str_replace("_", " ", ImageBackgroundUploadDefinition::ENTITY_NAME);
        $folderName = ucwords($folderName);
        $mediaFolder = [
            'id' => $mediaFolderId,
            'default_folder_id' => $defaultFoderId,
            'name' => $folderName,
            'media_folder_configuration_id' => null,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ];

        $connection->insert(MediaFolderDefinition::ENTITY_NAME, $mediaFolder);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
