<?php declare(strict_types=1);

namespace Wns\WnsArShopware\Storefront\Page\ImageBackground;

use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Storefront\Page\Page;

class ImageBackgroundPage extends Page
{
    protected EntitySearchResult $imageBackgroundData;

    /**
     * @return EntitySearchResult
     */
    public function getImageBackgroundData(): EntitySearchResult
    {
        return $this->imageBackgroundData;
    }

    /**
     * @param EntitySearchResult $imageBackgroundData
     * @return void
     */
    public function setImageBackgroundData(EntitySearchResult $imageBackgroundData): void
    {
        $this->imageBackgroundData = $imageBackgroundData;
    }
}
