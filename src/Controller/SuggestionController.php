<?php

namespace App\Controller;

use App\Entity\Suggestion;
use App\Repository\SuggestionRepository;
use App\Security\Voter\SuggestionVoter;
use App\Service\CustomSerializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/suggestions')]
class SuggestionController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function index(
        SuggestionRepository $suggestionRepository,
        CustomSerializer $serializer
    ): Response {
        $suggestions = $suggestionRepository->findAll();
        $this->denyAccessUnlessGranted(SuggestionVoter::VIEW_LIST, $suggestions[0] ?? null);

        return new JsonResponse(
            $serializer->serializer->serialize($suggestions, 'json', ['groups' => ['suggestion', 'user']]),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('', methods: ['POST'])]
    public function createItem(
        SuggestionRepository $suggestionRepository,
        CustomSerializer $serializer,
        ValidatorInterface $validator,
        Request $request,
    ): JsonResponse {
        $this->denyAccessUnlessGranted(SuggestionVoter::CREATE);

        $suggestion = $serializer->serializer->deserialize($request->getContent(), Suggestion::class, 'json');
        assert($suggestion instanceof Suggestion);

        $suggestion->setUser($this->getUser());

        $errors = $validator->validate($suggestion, null, 'edit');

        if ($errors->count() !== 0) {
            return new JsonResponse(
                $serializer->serializer->serialize($errors, 'json'),
                Response::HTTP_UNPROCESSABLE_ENTITY,
                [],
                true,
            );
        }

        $this->getDoctrine()->getManager()->persist($suggestion);
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse(
            $serializer->serializer->serialize($suggestion, 'json', ['groups' => ['suggestion', 'user']]),
            Response::HTTP_CREATED,
            [],
            true
        );
    }

    #[Route('/{id}', methods: ['GET'])]
    public function getItem(
        SuggestionRepository $suggestionRepository,
        CustomSerializer $serializer,
        string $id,
    ): JsonResponse {
        $suggestions = $suggestionRepository->find($id);

        if ($suggestions === null) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted(SuggestionVoter::VIEW_ONE, $suggestions);

        return new JsonResponse(
            $serializer->serializer->serialize($suggestions, 'json', ['groups' => ['suggestion', 'user']]),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function editItem(
        SuggestionRepository $suggestionRepository,
        CustomSerializer $serializer,
        ValidatorInterface $validator,
        Request $request,
        string $id,
    ): JsonResponse {
        $oldSuggestions = $suggestionRepository->find($id);

        if ($oldSuggestions === null) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted(SuggestionVoter::EDIT, $oldSuggestions);

        $suggestion = $serializer->serializer->deserialize($request->getContent(), Suggestion::class, 'json');
        assert($suggestion instanceof Suggestion);
        $oldSuggestions->setName($suggestion->getName());
        $oldSuggestions->setDescription($suggestion->getDescription());
        $oldSuggestions->setStatus($suggestion->getStatus());
        $oldSuggestions->setCategory($suggestion->getCategory());
        $oldSuggestions->setDeprecated($suggestion->isDeprecated());

        $errors = $validator->validate($oldSuggestions, null, 'edit');

        if ($errors->count() !== 0) {
            return new JsonResponse(
                $serializer->serializer->serialize($errors, 'json'),
                Response::HTTP_UNPROCESSABLE_ENTITY,
                [],
                true,
            );
        }

        $this->getDoctrine()->getManager()->persist($oldSuggestions);
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse(
            $serializer->serializer->serialize($oldSuggestions, 'json', ['groups' => ['suggestion', 'user']]),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(
        SuggestionRepository $suggestionRepository,
        Request $request,
        string $id,
    ): JsonResponse {
        $suggestion = $suggestionRepository->find($id);

        if ($suggestion === null) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted(SuggestionVoter::DELETE, $suggestion);

        $this->getDoctrine()->getManager()->remove($suggestion);
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse(
            [],
            Response::HTTP_NO_CONTENT
        );
    }
}
