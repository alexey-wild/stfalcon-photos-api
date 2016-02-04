<?php

namespace ApiBundle\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\ClassMetadata;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Handler for generate JSON responses
 */
class JsonResponseHandler
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * Constructor
     */
    public function __construct()
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));

        $encoder = new JsonEncoder();
        $normalizer = new ObjectNormalizer($classMetadataFactory);

        $normalizer->setCircularReferenceHandler(function (ClassMetadata $object) {
            return $object->getName();
        });

        $this->serializer = new Serializer([$normalizer], [$encoder]);
    }

    /**
     * Create JSON response for entities
     *
     * @param object[] $entities
     * @param int      $total
     *
     * @return JsonResponse
     */
    public function createEntitiesResponse($entities, $total)
    {
        $objects = $this->serializer->normalize($entities, 'json', ['groups' => ['list']]);

        return new JsonResponse(['objects' => $objects, 'total' => $total]);
    }

    /**
     * Create JSON response for entity
     *
     * @param object $entity
     *
     * @return JsonResponse
     */
    public function createEntityResponse($entity)
    {
        $object = $this->serializer->normalize($entity, 'json', ['groups' => ['one']]);

        return new JsonResponse(['object' => $object]);
    }

    /**
     * Create success JSON response
     *
     * @return JsonResponse
     */
    public function createSuccessResponse()
    {
        return new JsonResponse(['success' => true]);
    }


    /**
     * Create JSON response for error
     *
     * @param string $message
     * @param int $code
     *
     * @return JsonResponse
     */
    public function createErrorResponse($message, $code = 500)
    {
        return new JsonResponse(['error' => ['code' => $code, 'message' => $message]]);
    }

    /**
     * Create JSON response for entity form error
     *
     * @param FormInterface $form
     *
     * @return JsonResponse
     */
    public function createFormErrorResponse(FormInterface $form)
    {
        $error = [];
        if ($form->getErrors()->count()) {
            $error = [
                'code' => 400,
                'message' => $form->getErrors()->current()->getMessage()
            ];
        }

        return new JsonResponse(['error' => $error]);
    }
}
