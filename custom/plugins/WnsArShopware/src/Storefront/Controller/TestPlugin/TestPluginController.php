<?php declare(strict_types=1);

namespace Wns\WnsArShopware\Storefront\Controller\TestPlugin;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;

/**
 * @RouteScope(scopes={"storefront"})
 */
class TestPluginController extends StorefrontController
{
    private EntityRepositoryInterface $imgBgUploadRepository;
    private EntityRepositoryInterface $imgBgUploadSalesChannelRepository;

    /**
     * @param EntityRepositoryInterface $imgBgUploadRepository
     * @param EntityRepositoryInterface $imgBgUploadSalesChannelRepository
     */
    public function __construct(
        EntityRepositoryInterface $imgBgUploadRepository,
        EntityRepositoryInterface $imgBgUploadSalesChannelRepository
    ){
        $this->imgBgUploadRepository = $imgBgUploadRepository;
        $this->imgBgUploadSalesChannelRepository = $imgBgUploadSalesChannelRepository;
    }

    /**
     * @Route("test/plugin/product/preview", name="frontend.test.plugin.product.preview", methods={"GET"}, defaults={"XmlHttpRequest"=true})
     * @param SalesChannelContext $salesChannelContext
     * @return Response
     */
    public function testGetFunction(SalesChannelContext $salesChannelContext) : Response
    {
        $backgrounds = $this->imgBgUploadRepository->search(new Criteria(), $salesChannelContext->getContext())->first();
        return $this->renderStorefront('@WnsArShopware/storefront/page/TestPlugin/test-plugin-product-preview.html.twig', [
            'backgrounds' => $backgrounds
        ]);
    }

    /**
     * @Route("test/plugin/product/preview/insert", name="frontend.test.plugin.product.preview.insert", methods={"GET"}, defaults={"XmlHttpRequest"=true})
     * @param SalesChannelContext $salesChannelContext
     * @return Response
     */
    public function testSetFunction(SalesChannelContext $salesChannelContext) : Response
    {
        $dataInsert = [
            'id' => Uuid::randomHex(),
            'bgUploadKey' => 'Background '.Uuid::randomHex(),
            'bgWidth' => rand(20,30),
            'leftImgPosition' => rand(10,50),
            'topImgPosition' => rand(10,50),
            'mediaId' => Uuid::randomHex(),
            'active' => true
        ];

        $this->imgBgUploadRepository->create([$dataInsert], Context::createDefaultContext());

        return $this->redirectToRoute('frontend.test.plugin.product.preview');
    }
}
