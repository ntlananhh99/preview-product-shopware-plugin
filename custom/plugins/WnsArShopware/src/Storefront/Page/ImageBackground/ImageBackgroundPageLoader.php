<?php declare(strict_types=1);

namespace Wns\WnsArShopware\Storefront\Page\ImageBackground;

use Shopware\Core\Content\MailTemplate\MailTemplateEntity;
use Shopware\Core\Framework\Adapter\Twig\StringTemplateRenderer;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Page\GenericPageLoaderInterface;
use Symfony\Component\HttpFoundation\Request;

class ImageBackgroundPageLoader
{
    private const DEFAULT_LIMIT = 10;
    private const DEFAULT_PAGE = 1;

    private GenericPageLoaderInterface $genericPageLoader;
    private EntityRepositoryInterface $imgBgUploadRepository;
    private SystemConfigService $systemConfigService;
    private EntityRepositoryInterface $mailTypeRepository;
    private StringTemplateRenderer $templateRenderer;

    public function __construct(
        GenericPageLoaderInterface $genericPageLoader,
        EntityRepositoryInterface $imgBgUploadRepository,
        SystemConfigService $systemConfigService,
        EntityRepositoryInterface $mailTypeRepository,
        StringTemplateRenderer $templateRenderer
    )
    {
        $this->genericPageLoader = $genericPageLoader;
        $this->imgBgUploadRepository = $imgBgUploadRepository;
        $this->systemConfigService = $systemConfigService;
        $this->mailTypeRepository = $mailTypeRepository;
        $this->templateRenderer = $templateRenderer;
    }

    /**
     * @param Request $request
     * @param SalesChannelContext $context
     * @return object
     */
    public function load(Request $request, SalesChannelContext $context): object
    {
        $criteria = $this->createCriteria($request, $context);
        $data = $this->imgBgUploadRepository->search($criteria, $context->getContext());

        $page = $this->genericPageLoader->load($request, $context);
        $page = ImageBackgroundPage::createFrom($page);
        $page->setImageBackgroundData($data);

        $emailShareTemplate = $this->getEmailShareTemplate($context->getContext());

        if (!$emailShareTemplate) {
            return $page;
        }

        $encodeUrlEmailShareTemplate = $this->templateRenderer->render(
            $emailShareTemplate->getContentPlain(),
            [ 'queryParams' => $request->query->all() ],
            $context->getContext()
        );


        $page->setEmailShareTemplate($emailShareTemplate);
        $page->setEncodeUrlEmailShareTemplate($encodeUrlEmailShareTemplate);

        return $page;
    }

    /**
     * @param Request $request
     * @param SalesChannelContext $context
     * @return Criteria
     */
    public function createCriteria(Request $request, SalesChannelContext $context): Criteria
    {
        $bgUploadKey =  ($request->query->get('key')) ? $request->query->get('key') : null;

        $criteria  = new Criteria();
        $criteria->addAssociation('media');
        $criteria->addAssociation('imgBgUploadSalesChannels');

        $criteria->addFilter(new NotFilter(MultiFilter::CONNECTION_AND, [
            new EqualsFilter('media.id', null)
        ]));

        if ($bgUploadKey !== null) {
            $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, [
                new EqualsFilter('bgUploadKey', $bgUploadKey),
                new EqualsFilter('bgUploadKey', null),
            ]));
        }

        $criteria->addFilter(new EqualsFilter('active', true));
        $criteria->addFilter(new EqualsFilter('imgBgUploadSalesChannels.salesChannelId', $context->getSalesChannelId()));

        return $criteria
            ->addSorting(new FieldSorting('bgUploadKey', FieldSorting::DESCENDING))
            ->addSorting(new FieldSorting('media.createdAt', FieldSorting::DESCENDING))
            ->setTotalCountMode(Criteria::TOTAL_COUNT_MODE_EXACT);
    }

    private function getEmailShareTemplate(Context $context): ?MailTemplateEntity {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('technicalName', 'wns_ar_shopware_share_email'))
            ->addAssociation('mailTemplates')
            ->setLimit(1);

        $emailTemplateType = $this->mailTypeRepository->search($criteria, $context)->getEntities()->first();

        if (!$emailTemplateType) {
            return null;
        }

        return $emailTemplateType->getMailTemplates()->first();
    }
}
