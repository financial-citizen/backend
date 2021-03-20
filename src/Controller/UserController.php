<?php

namespace App\Controller;

use App\Entity\Users;
use App\Repository\UsersRepository;
use App\Security\Voter\UserVoter;
use DateTime;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints;
use App\Service\CustomSerializer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/users')]
class UserController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function index(
        UsersRepository $usersRepository,
        CustomSerializer $serializer
    ): Response {
        $users = $usersRepository->findAll();
        $this->denyAccessUnlessGranted(UserVoter::VIEW_LIST, $users[0] ?? null);

        return new JsonResponse(
            $serializer->serializer->serialize($users, 'json', ['groups' => 'user']),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/me', methods: ['GET'])]
    public function currentUser(CustomSerializer $serializer): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserVoter::VIEW_ME, $this->getUser());

        return new JsonResponse(
            $serializer->serializer->serialize($this->getUser(), 'json', ['groups' => ['currentUser', 'user']]),
            Response::HTTP_OK,
            [],
            true,
        );
    }

    #[Route('/register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        UserPasswordEncoderInterface $passwordEncoder
    ):JsonResponse {
        $content = json_decode($request->getContent(), true);
        $birthday = DateTime::createFromFormat ('Y-m-d H:i:s', $content['birthday']);
        $user = new Users();
        $user->setEmail($content['email']);
        $user->setName($content['name']);
        $user->setPhone($content['phone']);
        $user->setAhv($content['ahv']);
        $user->setBirthday($birthday);
        $user->setPassword($content['password']);
//        $user->birthday($birthday);
//        $user->birthday($birthday);
//        $user = $serializer->deserialize($request->getContent(), Users::class, 'json');
        $errors = $validator->validate($user, null, 'register');

        if ($errors->count() !== 0) {
            return new JsonResponse(
                $serializer->serialize($errors, 'json'),
                Response::HTTP_UNPROCESSABLE_ENTITY,
                [],
                true,
            );
        }

        $user->setPassword($passwordEncoder->encodePassword($user, $user->getPassword()));

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(
            $serializer->serialize($user, 'json', ['groups' => ['user', 'currentUser']]),
            Response::HTTP_CREATED,
            [],
            true,
        );    }

    /** @Route("/{id}/change_password", methods={"PUT"}) */
    public function changePassword(
        Request $request,
        ValidatorInterface $validator,
        UsersRepository $userRepository,
        CustomSerializer $serializer,
        UserPasswordEncoderInterface $passwordEncoder,
        string $id
    ): JsonResponse {
        $groups = ['user', 'currentUser', 'userPicture'];

        $user = $userRepository->find($id);

        if ($user === null) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted(UserVoter::EDIT_PASSWORD, $user);

        $requestData = $serializer->serializer->decode($request->getContent(), 'json');

        if (!$passwordEncoder->isPasswordValid($user, $requestData['oldPassword'])) {
            throw $this->createAccessDeniedException();
        }

        $violations = $validator->validate(
            $requestData,
            new Constraints\Collection(
                [
                    'oldPassword' => [
                        new NotBlank(),
                        new Type(['type' => 'string']),
                        new Length(['min' => 6, 'max' => 50]),
                    ],
                    'password' => [
                        new NotBlank(),
                        new Type(['type' => 'string']),
                        new Length(['min' => 6, 'max' => 50]),
                    ],
                    'confirmPassword' => [
                        new NotBlank(),
                        new Type(['type' => 'string']),
                        new Length(['min' => 6, 'max' => 50]),
                        new EqualTo(['propertyPath' => 'password']),
                    ],
                ],
            ),
        );

        if ($violations->count() > 0) {
            return $this->json($violations, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->setPassword($passwordEncoder->encodePassword($user, $requestData['password']));
        $this->getDoctrine()->getManager()->persist($user);
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse(
            $serializer->serializer->serialize($user, 'json', ['groups' => $groups]),
            Response::HTTP_OK,
            [],
            true,
        );
    }

    /** @Route("/{id}", methods={"DELETE"}) */
    public function deleteUser(
        EntityManagerInterface $entityManager,
        UsersRepository $userRepository,
        CustomSerializer $serializer,
        string $id
    ): JsonResponse {
        $groups = ['user'];

        $user = $userRepository->find($id);

        if ($user === null) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted(UserVoter::DELETE_USER, $user);

        $entityManager->remove($user);
        $entityManager->flush();

        return new JsonResponse(
            $serializer->serializer->serialize($user, 'json', ['groups' => $groups]),
            Response::HTTP_OK,
            [],
            true,
        );
    }
}
