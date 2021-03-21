<?php

namespace App\Controller;

use App\Entity\Suggestion;
use App\Entity\Vote;
use App\Repository\SuggestionRepository;
use App\Repository\VoteRepository;
use App\Security\Voter\SuggestionVoter;
use App\Security\Voter\VoteVoter;
use App\Service\CustomSerializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/suggestions')]
class VoteController extends AbstractController
{
    #[Route('/{id}/vote_up', methods: ['GET'])]
    public function voteUp(
        VoteRepository $voteRepository,
        SuggestionRepository $suggestionRepository,
        CustomSerializer $serializer,
        string $id,
    ): JsonResponse {
        $suggestions = $suggestionRepository->find($id);

        if ($suggestions === null) {
            throw $this->createNotFoundException();
        }

        $vote = $voteRepository->findByUserAndSuggestion($suggestions, $this->getUser());

        if ($vote === null) {
            $vote = new Vote();
            $vote->setSuggestion($suggestions)->setUser($this->getUser());
        }

        $this->denyAccessUnlessGranted(VoteVoter::VOTE, $vote);

        $vote->setVoteValue(true);

        $this->getDoctrine()->getManager()->persist($vote);
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse(
            $serializer->serializer->serialize($vote, 'json', ['groups' => ['vote']]),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/{id}/vote_down', methods: ['GET'])]
    public function voteDown(
        VoteRepository $voteRepository,
        SuggestionRepository $suggestionRepository,
        CustomSerializer $serializer,
        string $id,
    ): JsonResponse {
        $suggestions = $suggestionRepository->find($id);

        if ($suggestions === null) {
            throw $this->createNotFoundException();
        }

        $vote = $voteRepository->findByUserAndSuggestion($suggestions, $this->getUser());

        if ($vote === null) {
            $vote = new Vote();
            $vote->setSuggestion($suggestions)->setUser($this->getUser());
        }

        $this->denyAccessUnlessGranted(VoteVoter::VOTE, $vote);

        $vote->setVoteValue(false);

        $this->getDoctrine()->getManager()->persist($vote);
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse(
            $serializer->serializer->serialize($vote, 'json', ['groups' => ['vote']]),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/{id}/vote_remove', methods: ['GET'])]
    public function voteRemove(
        VoteRepository $voteRepository,
        SuggestionRepository $suggestionRepository,
        CustomSerializer $serializer,
        string $id,
    ): JsonResponse {
        $suggestions = $suggestionRepository->find($id);

        if ($suggestions === null) {
            throw $this->createNotFoundException();
        }

        $vote = $voteRepository->findByUserAndSuggestion($suggestions, $this->getUser());

        $this->denyAccessUnlessGranted(VoteVoter::VOTE, $vote);

        if ($vote === null) {
            return new JsonResponse(
                [],
                Response::HTTP_NO_CONTENT
            );
        }

        $this->getDoctrine()->getManager()->remove($vote);
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse(
            [],
            Response::HTTP_NO_CONTENT
        );
    }
}
