<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use App\Entity\Banner;
use App\Entity\Feature;
use App\Entity\SearchBanner;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class BannerFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 2000; ++$i) {
            $tag = new Tag();
            $tag->setId($i);
            $tag->setName("$i");
            $manager->persist($tag);
        }

        for ($i = 1; $i <= 2000; ++$i) {
            $feature = new Feature();
            $feature->setId($i);
            $feature->setName("$i");
            $manager->persist($feature);
        }

        for ($i = 1; $i <= 2000; ++$i) {
            $banner = new Banner();
            $banner->setContent("{\"mmm\": \"some_title\", \"text\": \"some_text\", \"url\": \"some_url\"}")
                ->setIsActive(true)
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTimeImmutable());

            $manager->persist($banner);

            for ($j = 6; $j <= 10; ++$j) {
                $searchBanner = new SearchBanner();

                $searchBanner->setBanner($banner)
                    ->setTagId($j)
                    ->setFeatureId($i);

                $manager->persist($searchBanner);
            }
        }

        $manager->flush();
    }
}
