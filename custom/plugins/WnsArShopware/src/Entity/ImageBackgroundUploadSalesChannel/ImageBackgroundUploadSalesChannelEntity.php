<?php declare(strict_types=1);
namespace Wns\WnsArShopware\Entity\ImageBackgroundUploadSalesChannel;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ImageBackgroundUploadSalesChannelEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string
     */
    protected $imageBackgroundUploadId;

    /**
     * @var string
     */
    protected $salesChannelId;

    /**
     * @return string
     */
    public function getImageBackgroundUploadId(): string
    {
        return $this->imageBackgroundUploadId;
    }

    /**
     * @param string $imageBackgroundUploadId
     */
    public function setImageBackgroundUploadId(string $imageBackgroundUploadId)
    {
        $this->imageBackgroundUploadId = $imageBackgroundUploadId;
    }

    /**
     * @return string
     */
    public function getSalesChannelId(): string
    {
        return $this->salesChannelId;
    }

    /**
     * @param string $salesChannelId
     */
    public function setSalesChannelId(string $salesChannelId)
    {
        $this->salesChannelId = $salesChannelId;
    }
}
