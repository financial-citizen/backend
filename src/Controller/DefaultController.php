<?php

namespace App\Controller;

use App\Repository\UsersRepository;
use App\Service\CustomSerializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class DefaultController extends AbstractController
{
    #[Route('/default', name: 'default')]
    public function index(
        UsersRepository $usersRepository,
        CustomSerializer $serializer
    ): Response {
        $user = $usersRepository->findAll()[0];
        $this->denyAccessUnlessGranted('POST_EDIT', $user);

        return new JsonResponse($serializer->serializer->serialize($user, 'json', ['groups' => 'currentUser']), Response::HTTP_OK, [], true);
    }
}
