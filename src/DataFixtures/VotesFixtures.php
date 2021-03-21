<?php

namespace App\DataFixtures;

use App\Entity\Suggestion;
use App\Entity\Vote;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class VotesFixtures extends Fixture implements DependentFixtureInterface
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i < 30; $i++) {
            if ($this->faker->randomElement([true, false]) === true) {
                continue;
            }
            for ($x = 1; $x < 20; $x++) {
                if ($this->faker->randomElement([true, false]) === true) {
                    continue;
                }
                $vote = new Vote();
                $vote->setUser($this->getReference(sprintf('user_%s', $x)));
                $vote->setSuggestion($this->getReference(sprintf('suggestion_%s', $i)));
                $vote->setVoteValue($this->faker->randomElement([true, false]));

                $manager->persist($vote);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UsersFixtures::class,
            SuggestionsFixtures::class,
        ];
    }
}
