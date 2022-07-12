<?php declare(strict_types=1);

namespace Wns\WnsArShopware\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;

class Migration1657507808addMailTemplate extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1657507808;
    }

    /**
     * @throws Exception
     */
    public function update(Connection $connection): void
    {
        $mailTemplateTypeId = $this->createMailTemplateType($connection);

        $this->createMailTemplate($connection, $mailTemplateTypeId);
    }

    public function updateDestructive(Connection $connection): void
    {
    }

    /**
     * @throws Exception
     */
    private function getLanguageIdByLocale(Connection $connection, string $locale): ?string
    {
        $sql = <<<SQL
            SELECT `language`.`id`
            FROM `language`
            INNER JOIN `locale` ON `locale`.`id` = `language`.`locale_id`
            WHERE `locale`.`code` = :code
        SQL;

        $languageId = $connection->executeQuery($sql, ['code' => $locale])->fetchColumn();
        if (!$languageId && $locale !== 'en-GB') {
            return null;
        }

        if (!$languageId) {
            return Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);
        }

        return $languageId;
    }

    /**
     * @throws Exception
     */
    private function createMailTemplateType(Connection $connection): string
    {
        $mailTemplateTypeId = Uuid::randomHex();

        $defaultLangId = $this->getLanguageIdByLocale($connection, 'en-GB');
        $deLangId = $this->getLanguageIdByLocale($connection, 'de-DE');

        $englishName = 'AR Shopware Share Email';
        $germanName = 'AR Shopware Share Email';

        $connection->insert('mail_template_type', [
            'id' => Uuid::fromHexToBytes($mailTemplateTypeId),
            'technical_name' => 'wns_ar_shopware_share_email',
            'available_entities' => json_encode(['salesChannel' => 'sales_channel']),
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        if ($defaultLangId !== $deLangId) {
            $connection->insert('mail_template_type_translation', [
                'mail_template_type_id' => Uuid::fromHexToBytes($mailTemplateTypeId),
                'language_id' => $defaultLangId,
                'name' => $englishName,
                'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]);
        }

        if ($defaultLangId !== Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM)) {
            $connection->insert('mail_template_type_translation', [
                'mail_template_type_id' => Uuid::fromHexToBytes($mailTemplateTypeId),
                'language_id' => Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM),
                'name' => $englishName,
                'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]);
        }

        if ($deLangId) {
            $connection->insert('mail_template_type_translation', [
                'mail_template_type_id' => Uuid::fromHexToBytes($mailTemplateTypeId),
                'language_id' => $deLangId,
                'name' => $germanName,
                'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]);
        }

        return $mailTemplateTypeId;
    }

    /**
     * @throws Exception
     */
    private function createMailTemplate(Connection $connection, string $mailTemplateTypeId): void
    {
        $mailTemplateId = Uuid::randomHex();

        $defaultLangId = $this->getLanguageIdByLocale($connection, 'en-GB');
        $deLangId = $this->getLanguageIdByLocale($connection, 'de-DE');

        $connection->insert('mail_template', [
            'id' => Uuid::fromHexToBytes($mailTemplateId),
            'mail_template_type_id' => Uuid::fromHexToBytes($mailTemplateTypeId),
            'system_default' => 0,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        if ($defaultLangId !== $deLangId) {
            $connection->insert('mail_template_translation', [
                'mail_template_id' => Uuid::fromHexToBytes($mailTemplateId),
                'language_id' => $defaultLangId,
                'sender_name' => '{{ salesChannel.name }}',
                'subject' => 'My artwork recommendation at ars mundi',
                'content_html' => $this->getContentHtmlEn(),
                'content_plain' => $this->getContentPlainEn(),
                'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]);
        }

        if ($defaultLangId !== Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM)) {
            $connection->insert('mail_template_translation', [
                'mail_template_id' => Uuid::fromHexToBytes($mailTemplateId),
                'language_id' => Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM),
                'sender_name' => '{{ salesChannel.name }}',
                'subject' => 'My artwork recommendation at around shopware',
                'content_html' => $this->getContentHtmlEn(),
                'content_plain' => $this->getContentPlainEn(),
                'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]);
        }

        if ($deLangId) {
            $connection->insert('mail_template_translation', [
                'mail_template_id' => Uuid::fromHexToBytes($mailTemplateId),
                'language_id' => $deLangId,
                'sender_name' => '{{ salesChannel.name }}',
                'subject' => 'Meine Artwork-Empfehlung bei around shopware',
                'content_html' => $this->getContentHtmlDe(),
                'content_plain' => $this->getContentPlainDe(),
                'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]);
        }
    }

    private function getContentHtmlEn(): string
    {
        return <<<MAIL
            <div style="font-family:arial,sans-serif; font-size:14px;">
                <p>HI</p>
                <p>I like this artwork from AR Shopware Tool very much.</p>
                <p>Below you will find the link for the preview:</p>

                <span><strong>Product link:</strong>{{ seoUrl('frontend.detail.page', {'productId': queryParams.product_id}) }}<span>
                <span><strong>Iframe link:</strong>{ url('frontend.product.preview', queryParams) }}<span>
            </div>
        MAIL;
    }

    private function getContentPlainEn(): string
    {
        return <<<MAIL
            Hi,

            I like this artwork from AR Shopware Tool very much.

            Below you will find the link for the preview:

            Product link: {{ seoUrl('frontend.detail.page', {'productId': queryParams.product_id}) }}

            Iframe link: {{ url('frontend.product.preview', queryParams) }}
        MAIL;
    }

    private function getContentHtmlDe(): string
    {
        return <<<MAIL
            <div style="font-family:arial,sans-serif; font-size:14px;">
                <p>Hallo</p>
                <p>Dieses Artwork aus dem Shopware Tool gefällt mir sehr gut.</p>
                <p>Hier der Link zur Vorschau:</p>

                <span><strong>Produktlink:</strong>{{ seoUrl('frontend.detail.page', {'productId': queryParams.product_id}) }}<span>
                <span><strong>Iframe-Link:</strong>{ url('frontend.product.preview', queryParams) }}<span>
            </div>
        MAIL;
    }

    private function getContentPlainDe(): string
    {
        return <<<MAIL
            Hallo,

            Dieses Artwork aus dem Shopware Tool gefällt mir sehr gut.

            Hier der Link zur Vorschau:

            Produktlink: {{ seoUrl('frontend.detail.page', {'productId': queryParams.product_id}) }}

            Iframe-Link: {{ url('frontend.product.preview', queryParams) }}
        MAIL;
    }
}
