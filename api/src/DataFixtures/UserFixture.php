<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('test@test.com');
        $user->setPassword(password_hash('1234', \PASSWORD_DEFAULT));
        $user->setRoles(['ROLE_USER']);
        $manager->persist($user);
        $manager->flush();

        $user = new User();
        $user->setEmail('admin@test.com');
        $user->setPassword(password_hash('12341234', \PASSWORD_DEFAULT));
        $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
        $manager->persist($user);
        $manager->flush();
    }
}
