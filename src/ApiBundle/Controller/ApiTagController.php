<?php

namespace ApiBundle\Controller;

use ApiBundle\Entity\Tag;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Api Tag controller.
 */
class ApiTagController extends Controller
{
    /**
     * Lists all Tag entities.
     *
     * @Route("/api/tag", name="api_tag_list")
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

        $queryBuilder = $this->getDoctrine()->getRepository('ApiBundle:Tag')
            ->createQueryBuilder('e');

        $countQueryBuilder = clone $queryBuilder;
        $count = $countQueryBuilder
            ->select('COUNT(e)')
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
     * Delete an existing Tag entity.
     *
     * @Route("/api/tag/{id}",
     *     name="api_tag_delete",
     *     requirements={"id": "\d+"}
     * )
     * @Method("DELETE")
     *
     * @param Tag $tag
     *
     * @return JsonResponse
     */
    public function deleteAction(Tag $tag)
    {
        try {
            $this->getDoctrine()->getManager()->remove($tag);
            $this->getDoctrine()->getManager()->flush();

            return $this->get('app.json_response.handler')->createSuccessResponse();
        } catch (\Exception $exception) {
            return $this->get('app.json_response.handler')->createErrorResponse('Error deleting tag');
        }
    }
}
