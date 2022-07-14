<?php

namespace Wns\WnsArShopware\Command;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
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

    public function __construct(
        EntityRepositoryInterface $imgBgUploadRepository,
        EntityRepositoryInterface $imgBgUploadSalesChannelRepository
    ){
        parent::__construct('wns');
        $this->imgBgUploadRepository = $imgBgUploadRepository;
        $this->imgBgUploadSalesChannelRepository = $imgBgUploadSalesChannelRepository;
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
        $active = false;
        if ( $key % 2 == 0)
            $active = true;

        return [
            'id' => $id,
            'bgUploadKey' => 'Background '.$key,
            'bgWidth' => rand(20,30),
            'leftImgPosition' => rand(10,50),
            'topImgPosition' => rand(10,50),
            'mediaId' => '9EE96B0D13E943B8833572B04604E41D',
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

        for($i = 1; $i <= 3; $i++) {
            $data[] = [
                'id' => Uuid::randomHex(),
                'imageBackgroundUploadId' => $imgBgUploadId,
                'salesChannelId' => '98432def39fc4624b33213a56b8c944d'
            ];
        }
        return $data;
    }
}
