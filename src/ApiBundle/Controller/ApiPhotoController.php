<?php

namespace ApiBundle\Controller;

use ApiBundle\Entity\Tag;
use ApiBundle\Entity\Photo;
use ApiBundle\Form\ApiPhotoType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Api Photo controller.
 */
class ApiPhotoController extends Controller
{
    /**
     * Lists all Photo entities.
     *
     * @Route("/api/photo", name="api_photo_list")
     * @Method("GET")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        $limit = $request->query->getInt('limit', 10);
        $page = $request->query->getInt('page', 1);
        $offset = $limit * ($page - 1);
        $tags = $request->query->get('tags', []);

        $queryBuilder = $this->getDoctrine()->getRepository('ApiBundle:Photo')
            ->createQueryBuilder('p');

        if ($tags) {
            $queryBuilder
                ->join('p.tags', 't');

            foreach ($tags as $key => $tag) {
                $queryBuilder
                    ->orWhere('t.name = :tag'.$key)
                    ->setParameter('tag'.$key, $tag);
            }
        }

        $countQueryBuilder = clone $queryBuilder;
        $count = $countQueryBuilder
            ->select('COUNT(p)')
            ->getQuery()
            ->getSingleScalarResult();

        $entities = $queryBuilder
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->execute();

        return $this->get('app.json_response.handler')->createEntitiesResponse($entities, $count);
    }

    /**
     * Add new Photo entity.
     *
     * @Route("/api/photo", name="api_photo_add")
     * @Method("POST")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function addAction(Request $request)
    {
        $photo = new Photo();

        $form = $this->createForm(ApiPhotoType::class, $photo, ['allow_extra_fields' => true]);

        $form->handleRequest($request);
        if ($form->isValid()) {
            /** @var Photo $photo */
            $photo = $form->getData();

            $tagsArray = $request->get('tags', []);

            /** @var Tag[] $tagsEntities */
            $tagsEntities = $this->getDoctrine()->getRepository('ApiBundle:Tag')
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

                $this->getDoctrine()->getManager()->persist($newTag);
            }

            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $file = $form->get('file')->getData();
            $fileName = time() . '.' . $file->guessExtension();

            $file->move($this->getUploadDir(), $fileName);

            $photo->setName($fileName);
            $photo->setOriginalName($file->getClientOriginalName());

            $this->getDoctrine()->getManager()->persist($photo);
            $this->getDoctrine()->getManager()->flush();

            return $this->get('app.json_response.handler')->createEntityResponse($photo);
        }

        return $this->get('app.json_response.handler')->createFormErrorResponse($form);
    }

    /**
     * Get one Photo entity.
     *
     * @Route("/api/photo/{id}",
     *     name="api_photo_get",
     *     requirements={"id": "\d+"}
     * )
     * @Method("GET")
     *
     * @param Photo $photo
     *
     * @return JsonResponse
     */
    public function getAction(Photo $photo)
    {
        return $this->get('app.json_response.handler')->createEntityResponse($photo);
    }

    /**
     * Edit an existing Photo entity.
     *
     * @Route("/api/photo/{id}",
     *     name="api_photo_edit",
     *     requirements={"id": "\d+"}
     * )
     * @Method("PUT")
     *
     * @param Request $request
     * @param Photo     $photo
     *
     * @return JsonResponse
     */
    public function editAction(Request $request, Photo $photo)
    {
        try {
            $photo->getTags()->clear();

            $requestContent = json_decode($request->getContent(), true);

            $tagsArray = $requestContent['tags'] ?? [];

            /** @var Tag[] $tagsEntities */
            $tagsEntities = $this->getDoctrine()->getRepository('ApiBundle:Tag')
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

                $this->getDoctrine()->getManager()->persist($newTag);
            }

            $this->getDoctrine()->getManager()->flush();

            return $this->get('app.json_response.handler')->createEntityResponse($photo);
        } catch (\Exception $exception) {
            $this->get('logger')->error($exception);
            return $this->get('app.json_response.handler')->createErrorResponse('Error update photo');
        }
    }

    /**
     * Delete an existing Photo entity.
     *
     * @Route("/api/photo/{id}",
     *     name="api_photo_delete",
     *     requirements={"id": "\d+"}
     * )
     * @Method("DELETE")
     *
     * @param Photo $photo
     *
     * @return JsonResponse
     */
    public function deleteAction(Photo $photo)
    {
        $name = $photo->getName();

        try {
            $this->getDoctrine()->getManager()->remove($photo);
            $this->getDoctrine()->getManager()->flush();

            unlink($this->getUploadDir() . '/' . $name);

            return $this->get('app.json_response.handler')->createSuccessResponse();
        } catch (\Exception $exception) {
            return $this->get('app.json_response.handler')->createErrorResponse('Error deleting photo');
        }
    }

    /**
     * @return string
     */
    private function getUploadDir()
    {
        return $this->container->getParameter('kernel.root_dir') . '/../web/' . Photo::FILE_PHOTO_PATH;
    }
}
