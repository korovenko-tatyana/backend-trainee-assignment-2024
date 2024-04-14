<?php

namespace App\DataFixtures;

use App\Entity\Feature;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class FeatureFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 500; ++$i) {
            $feature = new Feature();
            $feature->setId($i);
            $feature->setName("$i");
            $manager->persist($feature);
        }

        $manager->flush();
    }
}
