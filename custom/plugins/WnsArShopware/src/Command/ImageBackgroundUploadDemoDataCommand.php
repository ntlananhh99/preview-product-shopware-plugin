<?php

namespace Wns\WnsArShopware\Command;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Context;

class ImageBackgroundUploadDemoDataCommand extends Command
{
    protected static $defaultName = 'wns:image-background-upload:demo-data';

    protected EntityRepositoryInterface $imgBgUploadRepository;
    private EntityRepositoryInterface $imgBgUploadSalesChannelRepository;
    private EntityRepositoryInterface $mediaRepository;

    public function __construct(
        EntityRepositoryInterface $imgBgUploadRepository,
        EntityRepositoryInterface $imgBgUploadSalesChannelRepository,
        EntityRepositoryInterface $mediaRepository
    ){
        parent::__construct('wns');
        $this->imgBgUploadRepository = $imgBgUploadRepository;
        $this->imgBgUploadSalesChannelRepository = $imgBgUploadSalesChannelRepository;
        $this->mediaRepository = $mediaRepository;
    }

    protected function configure(): void
    {
        $this
            ->setName('Demo data for Image Background Upload Table')
            ->setDescription('Create demo data for Image Background Upload Table');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Start creating data demo for Image Background Upload Table');

        for($i = 1; $i <= 5; $i++) {
            $imgBgUploadId = Uuid::randomHex();
            $imgBgUploadData = $this->prepareDataImgBgUpload($imgBgUploadId, $i);
            $this->imgBgUploadRepository->create([$imgBgUploadData], Context::createDefaultContext());
            $imgBgUploadSalesChannelData = $this->prepareDataImgBgUploadSalesChannel($imgBgUploadId);
            $this->imgBgUploadSalesChannelRepository->create($imgBgUploadSalesChannelData, Context::createDefaultContext());
        }

        $output->writeln('Successful');
        return 0;
    }

    /**
     * @param $id
     * @param $key
     * @return array
     */
    private function prepareDataImgBgUpload($id, $key): array
    {
        $active = true;
        if ( $key%3 == 0)
            $active = false;

        return [
            'id' => $id,
            'bgUploadKey' => null,
            'bgWidth' => rand(20,30),
            'leftImgPosition' => rand(10,50),
            'topImgPosition' => rand(10,50),
            'mediaId' => $this->mediaRepository->search(new Criteria(), Context::createDefaultContext())->first()->getId(),
            'active' => $active
        ];
    }

    /**
     * @param $imgBgUploadId
     * @return array
     */
    private function prepareDataImgBgUploadSalesChannel($imgBgUploadId): array
    {
        $data = [];

        for($i = 1; $i <= 1; $i++) {
            $data[] = [
                'id' => Uuid::randomHex(),
                'imageBackgroundUploadId' => $imgBgUploadId,
                'salesChannelId' => '98432def39fc4624b33213a56b8c944d'
            ];
        }
        return $data;
    }
}
