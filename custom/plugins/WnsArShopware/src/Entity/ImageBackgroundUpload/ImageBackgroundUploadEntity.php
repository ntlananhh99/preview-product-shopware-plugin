<?php declare(strict_types=1);

namespace Wns\WnsArShopware\Entity\ImageBackgroundUpload;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ImageBackgroundUploadEntity extends Entity
{
    use EntityIdTrait;
    /**
     * @var string
     */
    protected $bgUploadKey;

    /**
     * @var string
     */
    protected $mediaId;

    /**
     * @var int
     */
    protected $bgWidth;

    /**
     * @var int
     */
    protected $leftImgPosition;

    /**
     * @var int
     */
    protected $topImgPosition;

    /**
     * @var bool
     */
    protected $active;

    /**
     * @return string|null
     */
    public function getBgUploadKey(): ?string
    {
        return $this->bgUploadKey;
    }

    /**
     * @param string $bgUploadKey
     */
    public function setBgUploadKey(string $bgUploadKey)
    {
        $this->bgUploadKey = $bgUploadKey;
    }

    /**
     * @return string
     */
    public function getMediaId(): string
    {
        return $this->mediaId;
    }

    /**
     * @param string $mediaId
     */
    public function setMediaId(string $mediaId)
    {
        $this->mediaId = $mediaId;
    }

    /**
     * @return int
     */
    public function getBgWidth(): int
    {
        return $this->bgWidth;
    }

    /**
     * @param int $bgWidth
     */
    public function setBgWidth(int $bgWidth)
    {
        $this->bgWidth = $bgWidth;
    }

    /**
     * @return int
     */
    public function getLeftImgPosition(): int
    {
        return $this->leftImgPosition;
    }

    /**
     * @param int $leftImgPosition
     */
    public function setLeftImgPosition(int $leftImgPosition)
    {
        $this->leftImgPosition = $leftImgPosition;
    }

    /**
     * @return int
     */
    public function getTopImgPosition(): int
    {
        return $this->topImgPosition;
    }

    /**
     * @param int $topImgPosition
     */
    public function setTopImgPosition(int $topImgPosition)
    {
        $this->topImgPosition = $topImgPosition;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active)
    {
        $this->active = $active;
    }
}
