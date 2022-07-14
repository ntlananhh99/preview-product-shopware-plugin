<?php declare(strict_types=1);

namespace Wns\WnsArShopware\Storefront\Page\ImageBackground;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\GenericPageLoaderInterface;
use Symfony\Component\HttpFoundation\Request;

class ImageBackgroundPageLoader
{
    private GenericPageLoaderInterface $genericPageLoader;
    private EntityRepositoryInterface $imgBgUploadRepository;

    public function __construct(
        GenericPageLoaderInterface $genericPageLoader,
        EntityRepositoryInterface $imgBgUploadRepository
    )
    {
        $this->genericPageLoader = $genericPageLoader;
        $this->imgBgUploadRepository = $imgBgUploadRepository;
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

        return $criteria
            ->addSorting(new FieldSorting('bgUploadKey', FieldSorting::DESCENDING))
            ->addSorting(new FieldSorting('media.createdAt', FieldSorting::DESCENDING))
            ->setTotalCountMode(Criteria::TOTAL_COUNT_MODE_EXACT);
    }
}
