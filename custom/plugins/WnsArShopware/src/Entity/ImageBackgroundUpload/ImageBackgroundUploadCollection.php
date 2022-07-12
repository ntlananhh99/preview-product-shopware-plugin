<?php  declare(strict_types=1);
namespace Wns\WnsArShopware\Entity\ImageBackgroundUpload;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void              add(ImageBackgroundUploadEntity $entity)
 * @method void              set(string $key, ImageBackgroundUploadEntity $entity)
 * @method ImageBackgroundUploadEntity[]    getIterator()
 * @method ImageBackgroundUploadEntity[]    getElements()
 * @method ImageBackgroundUploadEntity|null get(string $key)
 * @method ImageBackgroundUploadEntity|null first()
 * @method ImageBackgroundUploadEntity|null last()
 */
class ImageBackgroundUploadCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ImageBackgroundUploadEntity::class;
    }
}
