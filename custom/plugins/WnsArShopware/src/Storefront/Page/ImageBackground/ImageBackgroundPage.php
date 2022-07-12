<?php declare(strict_types=1);

namespace Wns\WnsArShopware\Storefront\Page\ImageBackground;

use Shopware\Core\Content\MailTemplate\MailTemplateEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Storefront\Page\Page;
use Wns\WnsArShopware\Entity\ImageBackgroundUpload\ImageBackgroundUploadEntity;

class ImageBackgroundPage extends Page
{
    /**
     * @var EntitySearchResult
     */
    protected EntitySearchResult $imageBackgroundData;

    /**
     * @var MailTemplateEntity
     */
    protected MailTemplateEntity $emailShareTemplate;

    /**
     * @var string|null
     */
    protected ?string $encodeUrlEmailShareTemplate;

    /**
     * @return EntitySearchResult
     */
    public function getImageBackgroundData(): EntitySearchResult
    {
        return $this->imageBackgroundData;
    }

    /**
     * @return MailTemplateEntity|null
     */
    public function getEmailShareTemplate(): ?MailTemplateEntity
    {
        return $this->emailShareTemplate;
    }

    /**
     * @return string|null
     */
    public function getEncodeUrlEmailShareTemplate(): ?string
    {
        return $this->encodeUrlEmailShareTemplate;
    }

    /**
     * @param EntitySearchResult $imageBackgroundData
     * @return void
     */
    public function setImageBackgroundData(EntitySearchResult $imageBackgroundData): void
    {
        $this->imageBackgroundData = $imageBackgroundData;
    }

    /**
     * @param MailTemplateEntity|null $emailShareTemplate
     * @return void
     */
    public function setEmailShareTemplate(?MailTemplateEntity $emailShareTemplate)
    {
        $this->emailShareTemplate = $emailShareTemplate;
    }

    /**
     * @param string|null $encodeUrlEmailShareTemplate
     * @return void
     */
    public function setEncodeUrlEmailShareTemplate(?string $encodeUrlEmailShareTemplate): void
    {
        $this->encodeUrlEmailShareTemplate = $encodeUrlEmailShareTemplate;
    }
}
