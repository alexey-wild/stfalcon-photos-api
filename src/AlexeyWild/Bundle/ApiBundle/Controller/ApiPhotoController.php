<?php

namespace ApiBundle\Controller;

use ApiBundle\Entity\Photo;
use ApiBundle\Form\Type\ApiPhotoType;
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
        $queryBuilder = $this->get('app.photo.manager')->getPhotosQueryBuilder($request);
        $entities = $this->get('app.photo.manager')->getPhotos($queryBuilder, $request);

        $count = $this->get('app.photo.manager')->getPhotosCount($queryBuilder);

        return $this->get('app.json_response.handler')->createEntitiesResponse($entities, $count);
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
            $this->get('app.tag.manager')->updateTags($photo, $tagsArray);

            $file = $form->get('file')->getData();
            $this->get('app.photo.manager')->uploadPhoto($file, $photo);

            $this->getDoctrine()->getManager()->persist($photo);
            $this->getDoctrine()->getManager()->flush();

            return $this->get('app.json_response.handler')->createEntityResponse($photo);
        }

        return $this->get('app.json_response.handler')->createFormErrorResponse($form);
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
     * @param Photo   $photo
     *
     * @return JsonResponse
     */
    public function editAction(Request $request, Photo $photo)
    {
        try {
            $photo->getTags()->clear();
            $requestContent = json_decode($request->getContent(), true);

            $tagsArray = $requestContent['tags'] ?? [];
            $this->get('app.tag.manager')->updateTags($photo, $tagsArray);

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

            unlink($this->getUploadDir().'/'.$name);

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
        return $this->container->getParameter('kernel.root_dir').'/../web/'.Photo::FILE_PHOTO_PATH;
    }
}
