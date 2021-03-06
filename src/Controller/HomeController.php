<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Movie;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;
use Slim\Interfaces\RouteCollectorInterface;
use Twig\Environment;

/**
 * Class HomeController.
 */
class HomeController
{
    /**
     * @var RouteCollectorInterface
     */
    private $routeCollector;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * HomeController constructor.
     *
     * @param RouteCollectorInterface $routeCollector
     * @param Environment             $twig
     * @param EntityManagerInterface  $em
     */
    public function __construct(RouteCollectorInterface $routeCollector, Environment $twig, EntityManagerInterface $em)
    {
        $this->routeCollector = $routeCollector;
        $this->twig = $twig;
        $this->em = $em;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @return ResponseInterface
     *
     * @throws HttpBadRequestException
     */
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = $this->twig->render('home/index.html.twig', [
                'trailers' => $this->fetchData(),
                'source' => __METHOD__
            ]);
        } catch (\Exception $e) {
            throw new HttpBadRequestException($request, $e->getMessage(), $e);
        }

        $response->getBody()->write($data);

        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @return ResponseInterface
     *
     * @throws HttpBadRequestException|HttpNotFoundException
     */
    public function show(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $id = (int) $request->getAttribute('id');
        if ($id) {
            try {
                $data = $this->twig->render('home/show.html.twig', [
                    'trailer' =>  $this->em->getRepository(Movie::class)
                                      ->find($id),
                ]);
            } catch (\Exception $e) {
                throw new HttpBadRequestException($request, $e->getMessage(), $e);
            }
        } else {
            throw new HttpNotFoundException($request);
        }

        $response->getBody()->write($data);

        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @return ResponseInterface
     *
     * @throws HttpBadRequestException|HttpNotFoundException
     */
    public function like(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $postArr  = $request->getParsedBody();
        $id = (int) $postArr['id'] ?? 0;
        if ($id) {
            try {
                $trailer = $this->em->getRepository(Movie::class)->find($id);
                if ($trailer) {
                    $trailer->addLike();
                    $this->em->persist($trailer);
                    $this->em->flush();
                    $data = ['count' => $trailer->getLikes()];
                    $response->getBody()->write(json_encode(($data)));
                    $response->withHeader('Content-Type', 'application/json');
                }
            } catch (\Exception $e) {
                throw new HttpBadRequestException($request, $e->getMessage(), $e);
            }
        } else {
            throw new HttpNotFoundException($request);
        }

        return $response;
    }

    /**
     * @return Collection
     */
    protected function fetchData(): Collection
    {
        $data = $this->em->getRepository(Movie::class)
            ->findAll();

        return new ArrayCollection($data);
    }
}
