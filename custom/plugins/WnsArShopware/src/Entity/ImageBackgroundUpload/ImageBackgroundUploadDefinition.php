<?php declare(strict_types=1);

namespace Wns\WnsArShopware\Entity\ImageBackgroundUpload;

use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Wns\WnsArShopware\Entity\ImageBackgroundUploadSalesChannel\ImageBackgroundUploadSalesChannelDefinition;

class ImageBackgroundUploadDefinition extends EntityDefinition
{
    const ENTITY_NAME = 'wns_image_background_upload';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return ImageBackgroundUploadCollection::class;
    }

    public function getEntityClass(): string
    {
        return ImageBackgroundUploadEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new StringField('bg_upload_key', 'bgUploadKey')),
            (new IntField('bg_width', 'bgWidth'))->addFlags(new Required()),
            (new IntField('left_img_position', 'leftImgPosition'))->addFlags(new Required()),
            (new IntField('top_img_position', 'topImgPosition'))->addFlags(new Required()),
            (new FkField('media_id', 'mediaId', MediaDefinition::class))->addFlags(new Required()),
            new BoolField('active', 'active'),
            (new OneToOneAssociationField('media', 'media_id', 'id', MediaDefinition::class, false)),
            (new OneToManyAssociationField('imgBgUploadSalesChannels', ImageBackgroundUploadSalesChannelDefinition::class, 'image_background_upload_id', 'id')),
            new CreatedAtField(),
            new UpdatedAtField()
        ]);
    }
}
