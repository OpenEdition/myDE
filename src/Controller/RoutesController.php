<?php

namespace MyDigitalEnvironment\MyDigitalEnvironmentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route as RouteComponent;
use Symfony\Component\Routing\RouterInterface;

class RoutesController extends AbstractController
{
    private const ROUTE_NAME = 'my_digital_environment_routes_list';

    public function __construct(private readonly RouterInterface $router)
    {
    }

    public function list(): Response
    {
        $blacklist = ['_preview_error', self::ROUTE_NAME];
        $regxBlacklist = [];

        /** @var RouteComponent[] $routes */
        $routes = [...$this->router->getRouteCollection()];
        // todo : implement regex black list
        // todo : could implement a twig extension to test a route path via a try/catch
        $routes = array_filter($routes, function ($name) use ($blacklist) {
            return $name[0] !== '_' && !in_array($name, $blacklist);
        }, ARRAY_FILTER_USE_KEY);

        return $this->render('@MyDigitalEnvironment/routes.html.twig', [
            'routes' => $routes,
        ]);
    }
}
