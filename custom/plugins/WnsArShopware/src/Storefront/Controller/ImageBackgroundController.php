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
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;

/**
 * @RouteScope(scopes={"storefront"})
 */
class ImageBackgroundController extends StorefrontController
{
    private const FOLDER_UPLOAD_DEFAULT = "Wns Background User";
    private const LEFT_IMG_POSITION_DEFAULT = 20;
    private const TOP_IMG_POSITION_DEFAULT = 20;
    private const BG_WIDTH_DEFAULT = 100;
    private const BYTE_TO_MB_COEFFICIENT = 1048576;

    private EntityRepositoryInterface $mediaFolderRepository;
    private EntityRepositoryInterface $mediaRepository;
    private EntityRepositoryInterface $imgBgUploadRepository;
    private EntityRepositoryInterface $imgBgUploadSalesChannelRepository;
    private FileSaver $mediaUpdater;
    private FileNameProvider $fileNameProvider;
    private SystemConfigService $systemConfigService;

    /**
     * @param EntityRepositoryInterface $mediaFolderRepository
     * @param EntityRepositoryInterface $mediaRepository
     * @param EntityRepositoryInterface $imgBgUploadRepository
     * @param EntityRepositoryInterface $imgBgUploadSalesChannelRepository
     * @param FileSaver $mediaUpdater
     * @param FileNameProvider $fileNameProvider
     * @param SystemConfigService $systemConfigService
     */
    public function __construct(
        EntityRepositoryInterface $mediaFolderRepository,
        EntityRepositoryInterface $mediaRepository,
        EntityRepositoryInterface $imgBgUploadRepository,
        EntityRepositoryInterface $imgBgUploadSalesChannelRepository,
        FileSaver $mediaUpdater,
        FileNameProvider $fileNameProvider,
        SystemConfigService $systemConfigService
    ){
        $this->mediaFolderRepository = $mediaFolderRepository;
        $this->mediaRepository = $mediaRepository;
        $this->imgBgUploadRepository = $imgBgUploadRepository;
        $this->imgBgUploadSalesChannelRepository = $imgBgUploadSalesChannelRepository;
        $this->mediaUpdater = $mediaUpdater;
        $this->fileNameProvider = $fileNameProvider;
        $this->systemConfigService = $systemConfigService;
    }

    /**
     * @Route("/product/preview", name="frontend.product.preview", methods={"GET"}, defaults={"XmlHttpRequest"=true})
     * @param Request $request
     * @param SalesChannelContext $salesChannelContext
     * @return Response
     */
    public function showImageBackgroundList(Request $request, SalesChannelContext $salesChannelContext) : Response
    {
        dd('Show background list');
    }

    /**
     * @Route("/product/preview/upload", name="frontend.product.preview.upload", methods={"GET"}, defaults={"XmlHttpRequest"=true})
     * @return Response
     */
    public function uploadBackground() : Response
    {
        return $this->renderStorefront('@WnsArShopware/storefront/page/TestPlugin/test-form-image-background-user-upload.html.twig', []);
    }

    /**
     * @Route("/product/preview/handle/upload", name="frontend.product.preview.handle.upload", methods={"POST"}, defaults={"XmlHttpRequest"=true})
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
            $payload['mediaId'] = $backgroundSaleChannel->imageBackgroundUpload->getMediaId();
        }

        $context = $salesChannelContext->getContext();
        $folderId = $this->findOrCreateBgUploadFolder($salesChannelContext);

        $dataFile = $request->files;
        $fileName = null;
        $file = null;
        $isNullFile = false;
        $mediaId = empty($payload['mediaId']) ? null : $payload['mediaId'];
        foreach ($dataFile as $file) {
            if(!is_null($file)) {
                $error = $this->validateFileUpload($file);
                if(count($error) > 0) {
                    return new JsonResponse($error);
                }

                $fileName = $file->getClientOriginalName();
                $fileName = Random::getInteger(100, 1000) . time() . $fileName;

                if($mediaId === null) {
                    $mediaId = Uuid::randomHex();
                    $this->addMediaAction($mediaId, 'Background' . time(), $fileName, $file->getClientMimeType(), $file->guessExtension(), $folderId);
                }
            } else {
                $isNullFile = true;
            }
        }

        $dataCreate = $this->getImgBgData($payload, $mediaId, $dataFile, $isNullFile, $salesChannelContext);
        $this->imgBgUploadRepository->upsert([$dataCreate['imgBgData']], $context);
        if ($dataCreate['imgBgUploadSalesChannelData']) {
            $this->imgBgUploadSalesChannelRepository->create($dataCreate['imgBgUploadSalesChannelData'], $context);
        }

        try {
            if(count($dataFile) > 0 && !$isNullFile) {
                $this->uploadImage($folderId, $file, $fileName, $mediaId, $context);
            }

            $background = $this->getBackgroundUpload($dataCreate['imgBgData']['bgUploadKey'], $salesChannelContext);
            $result = [
                'statusCode' => '201',
                'message' => $this->trans('uploadSuccess'),
                'background' => $background
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
     * @param SalesChannelContext $salesChannelContext
     * @return mixed|null
     */
    private function findImgBgUserUploadBySalesChannel(SalesChannelContext $salesChannelContext)
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('salesChannelId', $salesChannelContext->getSalesChannelId()));
        $criteria->addAssociation('imageBackgroundUpload');
        $criteria->addAssociation('salesChannel');

        return $this->imgBgUploadSalesChannelRepository->search($criteria, $salesChannelContext->getContext())->first();
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
     * @param UploadedFile $file
     * @return array
     */
    public function validateFileUpload(UploadedFile $file): array
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
     * @param array $payload
     * @param string|null $mediaId
     * @param FileBag $dataFile
     * @param bool $isNullFile
     * @param SalesChannelContext $salesChannelContext
     * @return array
     */
    private function getImgBgData(array $payload, ?string $mediaId, FileBag $dataFile, bool $isNullFile, SalesChannelContext $salesChannelContext): array
    {
        $imgBgData['bgUploadKey'] = $payload['key'] ??  Uuid::randomHex();
        $imgBgData['bgWidth'] = empty($payload['width']) ?  self::BG_WIDTH_DEFAULT : (int)$payload['width'];
        $imgBgData['topImgPosition'] = empty($payload['top']) ?  self::TOP_IMG_POSITION_DEFAULT : (int)$payload['top'];
        $imgBgData['leftImgPosition'] = empty($payload['left']) ?  self::LEFT_IMG_POSITION_DEFAULT : (int)$payload['left'];

        $context = $salesChannelContext->getContext();
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('bgUploadKey', $imgBgData['bgUploadKey']));
        $imgBackground = $this->imgBgUploadRepository->search($criteria, $context)->first();

        $backgroundId = null;
        if (!is_null($imgBackground)) {
            $mediaId = $imgBackground->mediaId;
            $backgroundId = $imgBackground->getId();
            if(count($dataFile) > 0 && !$isNullFile) {
                $this->mediaRepository->delete([['id' => $mediaId]], $context);
            }
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
     * @param string $folderId
     * @param UploadedFile $file
     * @param string $fileName
     * @param string $mediaId
     * @param Context $context
     * @return void
     */
    public function uploadImage(string $folderId, UploadedFile $file, string $fileName, string $mediaId, Context $context)
    {
        $this->updateMedia($file, $fileName, $mediaId, $folderId);
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

    /**
     * @param UploadedFile $file
     * @param string $fileName
     * @param string $mediaId
     * @param string $folderId
     * @return void
     */
    public function updateMedia(UploadedFile $file, string $fileName, string $mediaId, string $folderId)
    {
        $dataUpload = [
            'id' => $mediaId,
            'name' => 'Background' . time(),
            'fileName' => $fileName,
            'mimeType' => $file->getMimeType(),
            'fileExtension' => $file->guessExtension(),
            'mediaFolderId' => $folderId
        ];
        $this->mediaRepository->upsert([$dataUpload], Context::createDefaultContext());
    }

    /**
     * @param string $uploadKey
     * @param SalesChannelContext $salesChannelContext
     * @return mixed|null
     */
    public function getBackgroundUpload(string $uploadKey, SalesChannelContext $salesChannelContext)
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('bgUploadKey', $uploadKey));
        $criteria->addAssociation('media');
        $criteria->addAssociation('imgBgUploadSalesChannels');

        return $this->imgBgUploadRepository->search($criteria, $salesChannelContext->getContext())->first();
    }
}
