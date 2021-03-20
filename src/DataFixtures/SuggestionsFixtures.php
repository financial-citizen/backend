<?php

namespace App\DataFixtures;

use App\Entity\Suggestion;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class SuggestionsFixtures extends Fixture implements DependentFixtureInterface
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i < 30; $i++) {
            $suggestion = new Suggestion();
            $suggestion->setName($this->faker->realText(40));
            $suggestion->setDescription($this->faker->realText());
            $suggestion->setCategory($this->faker->randomElement(['infrastructure', 'sport', 'health']));
            $suggestion->setStatus($this->faker->randomElement(['new', 'phase1', 'phase2']));
            $suggestion->setDeprecated($this->faker->randomElement([true, false, true]));
            $suggestion->setUser($this->getReference(sprintf('user_%s', $this->faker->numberBetween(1, 19))));

            $manager->persist($suggestion);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UsersFixtures::class,
        ];
    }
}
