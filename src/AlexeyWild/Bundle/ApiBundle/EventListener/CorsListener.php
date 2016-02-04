<?php

namespace ApiBundle\EventListener;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Enable CORS for requests
 */
class CorsListener
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();
        if (!$request->headers->has('Origin') || $request->headers->get('Origin') === $request->getSchemeAndHttpHost()) {
            return;
        }

        if ('OPTIONS' === $request->getMethod()) {
            $response = new Response();
            $this->getPreflightResponse($request, $response);
            $event->setResponse($response);

            return;
        }

        $this->eventDispatcher->addListener('kernel.response', [$this, 'onKernelResponse']);
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $response = $event->getResponse();

        $this->getPreflightResponse($event->getRequest(), $response);

        $event->setResponse($response);
    }

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    protected function getPreflightResponse(Request $request, Response &$response)
    {
        $allowOrigin = $request->headers->get('Origin');

        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('P3P', 'CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Origin, Accept, X-Requested-With, X-Content-Type-Options');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Allow-Origin', $allowOrigin);
    }
}
