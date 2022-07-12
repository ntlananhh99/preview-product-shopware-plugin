<?php declare(strict_types=1);

namespace Wns\WnsArShopware\Storefront\Controller;

use Shopware\Core\Content\Media\File\FileSaver;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Util\Random;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Core\Content\Media\File\MediaFile;
use Shopware\Core\Content\Media\File\FileNameProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Wns\WnsArShopware\Storefront\Page\ImageBackground\ImageBackgroundPageLoader;

/**
 * @RouteScope(scopes={"storefront"})
 */
class ImageBackgroundController extends StorefrontController
{
    private const FOLDER_UPLOAD_DEFAULT = "Wns Background User";
    private const LEFT_IMG_POSITION_DEAFULT = 20;
    private const TOP_IMG_POSITION_DEAFULT = 20;
    private const BG_WIDTH_DEAFULT = 100;
    private const BYTE_TO_MB_COEFFICIENT = 1048576;
    private const DEFAULT_LIMIT = 10;

    private EntityRepositoryInterface $mediaFolderRepository;
    private EntityRepositoryInterface $mediaRepository;
    private EntityRepositoryInterface $imgBgUploadRepository;
    private EntityRepositoryInterface $imgBgUploadSalesChannelRepository;
    private FileSaver $mediaUpdater;
    private FileNameProvider $fileNameProvider;
    private SystemConfigService $systemConfigService;
    private ImageBackgroundPageLoader $backgroundPageLoader;

    /**
     * @param EntityRepositoryInterface $mediaFolderRepository
     * @param EntityRepositoryInterface $mediaRepository
     * @param EntityRepositoryInterface $imgBgUploadRepository
     * @param EntityRepositoryInterface $imgBgUploadSalesChannelRepository
     * @param FileSaver $mediaUpdater
     * @param FileNameProvider $fileNameProvider
     * @param SystemConfigService $systemConfigService
     * @param ImageBackgroundPageLoader $backgroundPageLoader
     */
    public function __construct(
        EntityRepositoryInterface $mediaFolderRepository,
        EntityRepositoryInterface $mediaRepository,
        EntityRepositoryInterface $imgBgUploadRepository,
        EntityRepositoryInterface $imgBgUploadSalesChannelRepository,
        FileSaver $mediaUpdater,
        FileNameProvider $fileNameProvider,
        SystemConfigService $systemConfigService,
        ImageBackgroundPageLoader $backgroundPageLoader
    ){
        $this->mediaFolderRepository = $mediaFolderRepository;
        $this->mediaRepository = $mediaRepository;
        $this->imgBgUploadRepository = $imgBgUploadRepository;
        $this->imgBgUploadSalesChannelRepository = $imgBgUploadSalesChannelRepository;
        $this->mediaUpdater = $mediaUpdater;
        $this->fileNameProvider = $fileNameProvider;
        $this->systemConfigService = $systemConfigService;
        $this->backgroundPageLoader = $backgroundPageLoader;
    }

    /**
     * @Route("/product/preview", name="frontend.product.preview", methods={"GET"}, defaults={"XmlHttpRequest"=true})
     * @param Request $request
     * @param SalesChannelContext $salesChannelContext
     * @return Response
     */
    public function listBacground(Request $request, SalesChannelContext $salesChannelContext) : Response
    {
        $dataRequest = $request->query->all();
        $limitBgConfig = $this->systemConfigService->get('WnsArShopware.config.limitBgDisplay') ?? null;
        $dataRequest['limit'] = is_null($limitBgConfig) ? self::DEFAULT_LIMIT : $limitBgConfig;

        $page = $this->backgroundPageLoader->load($request, $salesChannelContext);

        return $this->renderStorefront('@WnsArShopware/storefront/page/preview-product.html.twig', [
            'queryParams' => $dataRequest,
            'page' => $page
        ]);
    }

    /**
     * @Route("/product/preview/upload", name="frontend.product.preview.upload", methods={"GET"}, defaults={"XmlHttpRequest"=true})
     * @return Response
     */
    public function uploadBackground() : Response
    {
        return $this->renderStorefront('@WnsArShopware/storefront/page/form-upload-background.html.twig', []);
    }

    /**
     * @Route("/product/preview/handle/upload", name="frontend.product.preview.handle.upload", defaults={"csrf_protected"=false})
     * @param Request $request
     * @param SalesChannelContext $salesChannelContext
     * @return JsonResponse
     */
    public function HandleUploadBackground(Request $request, SalesChannelContext $salesChannelContext) : JsonResponse
    {
        $payload = $request->request->all();
        $backgroundSaleChannel = $this->findImgBgUserUploadBySalesChannel($salesChannelContext);
        if($backgroundSaleChannel != null) {
            $payload['key'] = $backgroundSaleChannel->imageBackgroundUpload->getBgUploadKey();
        }

        $context = $salesChannelContext->getContext();
        $folderId = $this->findOrCreateBgUploadFolder($salesChannelContext);

        $dataFile = $request->files;
        $fileName = null;
        $file = null;
        $mediaId = null;
        foreach ($dataFile as $file) {
            $error = $this->validateFileUpload($file);
            if(count($error) > 0) {
                return new JsonResponse($error);
            }

            $fileName = $file->getClientOriginalName();
            $fileName = ImageBackgroundController . phpRandom::getInteger(100, 1000) . $fileName;

            $existMedia = $this->findMediaByFileName($fileName, $salesChannelContext);
            if($existMedia === null) {
                $mediaId = Uuid::randomHex();
                $this->addMediaAction($mediaId, 'Background' . time(), $fileName, $file->getClientMimeType(), $file->guessExtension(), $folderId);
            } else {
                $mediaId = $existMedia->getId();
            }
        }

        $dataCreate = $this->getImgBgData($payload, $mediaId, $salesChannelContext);
        $this->imgBgUploadRepository->upsert([$dataCreate['imgBgData']], $context);
        if ($dataCreate['imgBgUploadSalesChannelData']) {
            $this->imgBgUploadSalesChannelRepository->create($dataCreate['imgBgUploadSalesChannelData'], $context);
        }

        try {
            $this->uploadImage($file, $fileName, $mediaId, $context);
            $result = [
                'statusCode' => '201',
                'message' => $this->trans('uploadSuccess')
            ];
            return new JsonResponse($result);
        } catch (\Exception $exception) {
            $result = [
                'statusCode' => '400',
                'message' => $this->trans('uploadFail')
            ];
        }
        return new JsonResponse($result);
    }
    /**
     * @Route("/product/preview/update", name="frontend.product.preview.update", methods={"GET"}, defaults={"XmlHttpRequest"=true})
     * @return Response
     */
    public function updateBackground() : Response
    {
        return $this->renderStorefront('@WnsArShopware/storefront/page/test-update-background-config.html.twig', []);
    }

    /**
     * @Route("/product/preview/handle/update", name="frontend.product.preview.handle.update", defaults={"csrf_protected"=false})
     * @param Request $request
     * @param SalesChannelContext $salesChannelContext
     * @return void
     */
    public function updateBackgroundConfig(Request $request, SalesChannelContext $salesChannelContext)
    {
        dd($request->request->all());
    }

    /**
     * @param SalesChannelContext $salesChannelContext
     * @return string|null
     */
    private function findOrCreateBgUploadFolder(SalesChannelContext $salesChannelContext): ?string
    {
        $context = $salesChannelContext->getContext();

        $criteria = new Criteria();
        $criteria->setLimit(1);
        $criteria->addFilter(new EqualsFilter('name', self::FOLDER_UPLOAD_DEFAULT));
        $defaultFolder = $this->mediaFolderRepository->search($criteria, $context);

        if ($defaultFolder->count() === 1) {
            $folderId = $defaultFolder->first()->getId();
        } else {
            $folderId = Uuid::randomHex();
            $this->mediaFolderRepository->create([
                [
                    'id' => $folderId,
                    'name' => self::FOLDER_UPLOAD_DEFAULT,
                    'useParentConfiguration' => false,
                    'configuration' => [],
                ],
            ], $context);
        }

        return $folderId;
    }

    /**
     * @param string $mediaId
     * @param string $name
     * @param string $fileName
     * @param string $mimeType
     * @param string $fileExtension
     * @param string $mediaFolderId
     * @return void
     */
    private function addMediaAction(string $mediaId, string $name, string $fileName, string $mimeType, string $fileExtension, string $mediaFolderId)
    {
        $media = [[
            'id' => $mediaId,
            'name' => $name,
            'fileName' => $fileName,
            'mimeType' => $mimeType,
            'fileExtension' => $fileExtension,
            'mediaFolderId' => $mediaFolderId
        ]];

        $this->mediaRepository->create($media, Context::createDefaultContext());
    }

    /**
     * @param $payload
     * @param string|null $mediaId
     * @param SalesChannelContext $salesChannelContext
     * @return array
     */
    private function getImgBgData($payload, ?string $mediaId, SalesChannelContext $salesChannelContext): array
    {
        $imgBgData['bgUploadKey'] = $payload['key'] ??  Uuid::randomHex();
        $imgBgData['bgWidth'] = empty($payload['width']) ?  self::BG_WIDTH_DEAFULT : (int)$payload['width'];
        $imgBgData['topImgPosition'] = empty($payload['top']) ?  self::TOP_IMG_POSITION_DEAFULT : (int)$payload['top'];
        $imgBgData['leftImgPosition'] = empty($payload['lelf']) ?  self::LEFT_IMG_POSITION_DEAFULT : (int)$payload['lelf'];

        $context = $salesChannelContext->getContext();
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('bgUploadKey', $imgBgData['bgUploadKey']));

        $imgBackground = $this->imgBgUploadRepository->search($criteria, $context)->first();

        $backgroundId = '';
        if (!is_null($imgBackground)) {
            $mediaId = $imgBackground->mediaId;
            $backgroundId = $imgBackground->getId();
            $this->mediaRepository->delete([['id' => $mediaId]], $context);
        }

        $imgBgData['id'] = $backgroundId;
        $imgBgData['mediaId']  = $mediaId;
        $imgBgData['updatedAt'] = (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT);

        $imgBgUploadSalesChannelData= [];
        if ($imgBgData['id'] == null) {
            $imgBgData['id'] = Uuid::randomHex();
            $imgBgData['createdAt'] = (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT);
            $imgBgUploadSalesChannelData = [[
                'imageBackgroundUploadId' => $imgBgData['id'],
                'salesChannelId' => $salesChannelContext->getSalesChannelId()
            ]];
        }
        return [
            'imgBgData' => $imgBgData,
            'imgBgUploadSalesChannelData' => $imgBgUploadSalesChannelData
        ];
    }

    /**
     * @param $file
     * @param $fileName
     * @param $mediaId
     * @param $context
     * @return void
     */
    public function uploadImage($file, $fileName, $mediaId, $context)
    {
        return $this->mediaUpdater->persistFileToMedia(
            new MediaFile(
                $file->getRealPath(),
                $file->getMimeType(),
                $file->guessExtension(),
                $file->getSize()
            ),
            $this->fileNameProvider->provide(
                $fileName,
                $file->getExtension(),
                $mediaId,
                $context
            ),
            $mediaId,
            $context
        );
    }

    private function findMediaByFileName(string $fileName, SalesChannelContext $salesChannelContext)
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('fileName', $fileName));

        return $this->mediaRepository->search($criteria, $salesChannelContext->getContext())->first();
    }

    public function validateFileUpload($file): array
    {
        $imgSupportedExtension = array('PNG', 'png', 'jpg', 'jpeg', 'JPG', 'JPEG');
        $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);

        $fileSize = number_format($file->getSize() / self::BYTE_TO_MB_COEFFICIENT, 2);
        $configMaxSizeFileUpload = $this->systemConfigService->get('WnsArShopware.config.maxSize');

        if (!in_array($ext, $imgSupportedExtension) || $fileSize > $configMaxSizeFileUpload){
            return [
                'message' => $this->trans('invalidFile')
            ];
        }
        return [];
    }

    private function findImgBgUserUploadBySalesChannel(SalesChannelContext $salesChannelContext)
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('salesChannelId', $salesChannelContext->getSalesChannelId()));
        $criteria->addAssociation('imageBackgroundUpload');
        $criteria->addAssociation('salesChannel');

        return $this->imgBgUploadSalesChannelRepository->search($criteria, $salesChannelContext->getContext())->first();
    }
}
