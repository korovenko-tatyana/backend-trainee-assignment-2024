<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TagFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 500; ++$i) {
            $tag = new Tag();
            $tag->setId($i);
            $tag->setName("$i");
            $manager->persist($tag);
        }

        $manager->flush();
    }
}
