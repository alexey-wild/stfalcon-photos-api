<?php

namespace ApiBundle\Manager;

use ApiBundle\Entity\Photo;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

/**
 * Photo manager
 */
class PhotoManager
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var string
     */
    protected $rootDir;

    /**
     * PhotoManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param string                 $rootDir
     */
    public function __construct(EntityManagerInterface $entityManager, $rootDir = __DIR__)
    {
        $this->entityManager = $entityManager;
        $this->rootDir = $rootDir;
    }

    /**
     * @param Request $request
     *
     * @return QueryBuilder
     */
    public function getPhotosQueryBuilder(Request $request)
    {
        $tags = $request->query->get('tags', []);

        /** @var \Doctrine\ORM\EntityRepository $repository */
        $repository = $this->entityManager->getRepository('ApiBundle:Photo');

        $queryBuilder = $repository->createQueryBuilder('p');

        if ($tags) {
            $queryBuilder->join('p.tags', 't');

            foreach ($tags as $key => $tag) {
                $queryBuilder
                    ->orWhere('t.name = :tag'.$key)
                    ->setParameter('tag'.$key, $tag);
            }
        }

        return $queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param Request      $request
     *
     * @return object[]|Photo[]
     */
    public function getPhotos(QueryBuilder $queryBuilder, Request $request)
    {
        $limit = $request->query->getInt('limit', 10);
        $page = $request->query->getInt('page', 1);
        $offset = $limit * ($page - 1);

        return $queryBuilder
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->execute();
    }

    /**
     * @param QueryBuilder $queryBuilder
     *
     * @return int
     */
    public function getPhotosCount(QueryBuilder $queryBuilder)
    {
        return $queryBuilder
            ->select('COUNT(p)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param UploadedFile $file
     * @param Photo        $photo
     */
    public function uploadPhoto(UploadedFile $file, Photo &$photo)
    {
        $fileName = time().'.'.$file->guessExtension();
        $file->move($this->getUploadDir(), $fileName);
        $photo->setName($fileName);
        $photo->setOriginalName($file->getClientOriginalName());
    }

    /**
     * @return string
     */
    private function getUploadDir()
    {
        return $this->rootDir.'/../web/'.Photo::FILE_PHOTO_PATH;
    }
}
