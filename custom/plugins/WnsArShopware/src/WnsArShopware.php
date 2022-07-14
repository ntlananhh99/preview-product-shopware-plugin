<?php declare(strict_types=1);

namespace Wns\WnsArShopware;

use Doctrine\DBAL\Connection;
use Shopware\Core\Content\Media\Aggregate\MediaDefaultFolder\MediaDefaultFolderDefinition;
use Shopware\Core\Content\Media\Aggregate\MediaFolder\MediaFolderDefinition;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Wns\WnsArShopware\Entity\ImageBackgroundUpload\ImageBackgroundUploadDefinition;
use Wns\WnsArShopware\Entity\ImageBackgroundUploadSalesChannel\ImageBackgroundUploadSalesChannelDefinition;

class WnsArShopware extends Plugin
{
    /**
     * @param UninstallContext $uninstallContext
     * @return void
     */
    public function uninstall(UninstallContext $uninstallContext): void {
        parent::uninstall($uninstallContext);

        if ($uninstallContext->keepUserData()) {
            return;
        }
        $connection = $this->container->get(Connection::class);
        $connection->executeStatement('DROP TABLE IF EXISTS `' . ImageBackgroundUploadSalesChannelDefinition::ENTITY_NAME . '`');
        $connection->executeStatement('DROP TABLE IF EXISTS `' . ImageBackgroundUploadDefinition::ENTITY_NAME . '`');

        $folderName = ucwords(str_replace("_", " ", ImageBackgroundUploadDefinition::ENTITY_NAME));
        $connection->executeStatement('DELETE FROM `' . MediaDefaultFolderDefinition::ENTITY_NAME . '` WHERE entity LIKE "' . ImageBackgroundUploadDefinition::ENTITY_NAME . '"');
        $connection->executeStatement('DELETE FROM `' . MediaFolderDefinition::ENTITY_NAME . '` WHERE name LIKE "'. $folderName .'"');

    }
}
