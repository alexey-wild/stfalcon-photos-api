<?php

namespace ApiBundle\Manager;

use ApiBundle\Entity\Photo;
use ApiBundle\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Tag manager
 */
class TagManager
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * PhotoManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Photo $photo
     * @param array $tagsArray
     */
    public function updateTags(Photo &$photo, array $tagsArray = [])
    {
        /** @var \Doctrine\ORM\EntityRepository $repository */
        $repository = $this->entityManager->getRepository('ApiBundle:Tag');

        /** @var Tag[] $tagsEntities */
        $tagsEntities = $repository
            ->createQueryBuilder('t')
            ->where('t.name IN (:tags)')
            ->setParameter('tags', $tagsArray)
            ->getQuery()
            ->execute();

        foreach ($tagsEntities as $tagEntity) {
            $photo->addTag($tagEntity);
            $keyOnArray = array_search($tagEntity->getName(), $tagsArray);
            unset($tagsArray[$keyOnArray]);
        }

        foreach ($tagsArray as $tag) {
            $newTag = new Tag();
            $newTag->setName($tag);
            $photo->addTag($newTag);
            $this->entityManager->persist($newTag);
        }
    }
}
