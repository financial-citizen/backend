<?php

namespace App\DataFixtures;

use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UsersFixtures extends Fixture
{
    private UserPasswordEncoderInterface $encoder;
    private Generator $faker;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i < 20; $i++) {
            $user = new Users();
            $user->setEmail(sprintf('user_%s@gallen.com', $i ));
            $user->setAHV($this->faker->ean8);
            $user->setName($this->faker->name);
            $user->setPhone($this->faker->phoneNumber);
            $user->setBirthday($this->faker->dateTimeBetween('- 90 years', '- 20 years'));
            $password = $this->encoder->encodePassword($user, 'password123');
            $user->setPassword($password);

            $manager->persist($user);
            $this->addReference(sprintf('user_%s', $i), $user);

        }

        $manager->flush();
    }
}
