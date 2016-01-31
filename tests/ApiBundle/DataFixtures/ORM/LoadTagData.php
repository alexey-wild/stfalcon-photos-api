<?php

namespace Tests\ApiBundle\DataFixtures\ORM;

use ApiBundle\Entity\Tag;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * {@inheritdoc}
 */
class LoadTagData implements FixtureInterface
{
    /**
     * @var array
     */
    static public $tags = [];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $now = time();

        $tag = new Tag();
        $tag->setName('Tag' . $now);

        $manager->persist($tag);
        $manager->flush();

        self::$tags[] = $tag;
    }
}