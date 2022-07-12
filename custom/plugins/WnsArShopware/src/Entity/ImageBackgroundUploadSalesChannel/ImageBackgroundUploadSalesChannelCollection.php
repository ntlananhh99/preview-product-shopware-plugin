<?php  declare(strict_types=1);
namespace Wns\WnsArShopware\Entity\ImageBackgroundUploadSalesChannel;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void              add(ImageBackgroundUploadSalesChannelEntity $entity)
 * @method void              set(string $key, ImageBackgroundUploadSalesChannelEntity $entity)
 * @method ImageBackgroundUploadSalesChannelEntity[]    getIterator()
 * @method ImageBackgroundUploadSalesChannelEntity[]    getElements()
 * @method ImageBackgroundUploadSalesChannelEntity|null get(string $key)
 * @method ImageBackgroundUploadSalesChannelEntity|null first()
 * @method ImageBackgroundUploadSalesChannelEntity|null last()
 */
class ImageBackgroundUploadSalesChannelCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ImageBackgroundUploadSalesChannelEntity::class;
    }
}
