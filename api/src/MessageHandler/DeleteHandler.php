<?php

namespace App\MessageHandler;

use App\Message\DeleteNotification;
use App\Repository\BannerRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SearchBannerRepository;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class DeleteHandler implements MessageHandlerInterface
{
    private $bannerRepository;
    private $searchBannerRepository;
    private $em;

    public function __construct(BannerRepository $bannerRepository, SearchBannerRepository $searchBannerRepository, EntityManagerInterface $em)
    {
        $this->bannerRepository = $bannerRepository;
        $this->searchBannerRepository = $searchBannerRepository;
        $this->em = $em;
    }

    public function __invoke(DeleteNotification $message)
    {
        $feature = (int)$message->getContent();
        $banners = $this->bannerRepository->searchByFeature($feature);

        $this->searchBannerRepository->deleteMany($feature);

        foreach ($banners as $value) {
            $this->em->remove($value);
            $this->em->flush();
        }
    }
}
