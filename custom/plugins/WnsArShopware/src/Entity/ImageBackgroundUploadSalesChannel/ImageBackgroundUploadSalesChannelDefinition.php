<?php declare(strict_types=1);

namespace Wns\WnsArShopware\Entity\ImageBackgroundUploadSalesChannel;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;
use Wns\WnsArShopware\Entity\ImageBackgroundUpload\ImageBackgroundUploadDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ImageBackgroundUploadSalesChannelDefinition extends EntityDefinition
{
    const ENTITY_NAME = 'wns_image_background_upload_sales_channel';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return ImageBackgroundUploadSalesChannelCollection::class;
    }

    public function getEntityClass(): string
    {
        return ImageBackgroundUploadSalesChannelEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('image_background_upload_id', 'imageBackgroundUploadId', ImageBackgroundUploadDefinition::class))->addFlags(new Required()),
            (new FkField('sales_channel_id', 'salesChannelId', SalesChannelDefinition::class))->addFlags(new Required()),
            (new ManyToOneAssociationField('imageBackgroundUpload', 'image_background_upload_id', ImageBackgroundUploadDefinition::class, 'id')),
            (new ManyToOneAssociationField('salesChannel', 'sales_channel_id', SalesChannelDefinition::class, 'id')),
            new CreatedAtField(),
            new UpdatedAtField()
        ]);
    }
}
