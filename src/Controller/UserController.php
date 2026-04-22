<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/user')]
#[OA\Tag(name: 'Users')]
class UserController extends AbstractController
{
    #[Route('', name: 'user_index', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of users',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: User::class, groups: ['user:list']))
        )
    )]
    public function index(EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        $users = $em->getRepository(User::class)->findAll();
        $json = $serializer->serialize($users, 'json', ['groups' => 'user:list']);
        
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'user_show', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single user',
        content: new OA\JsonContent(ref: new Model(type: User::class, groups: ['user:read']))
    )]
    public function show(User $user, SerializerInterface $serializer): JsonResponse
    {
        $json = $serializer->serialize($user, 'json', ['groups' => 'user:read']);
        
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'user_create', methods: ['POST'])]
    #[OA\RequestBody(
        content: new OA\JsonContent(ref: new Model(type: User::class, groups: ['user:write']))
    )]
    #[OA\Response(
        response: 201,
        description: 'User created successfully',
        content: new OA\JsonContent(ref: new Model(type: User::class, groups: ['user:read']))
    )]
    public function create(
        Request $request, 
        EntityManagerInterface $em, 
        SerializerInterface $serializer, 
        UserPasswordHasherInterface $hasher,
        ValidatorInterface $validator
    ): JsonResponse {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json', ['groups' => ['user:write']]);
        
        // Handle roles from raw data if needed, or default to CLIENT
        $data = json_decode($request->getContent(), true);
        if (isset($data['roles']) && is_array($data['roles'])) {
            $user->setRoles($data['roles']);
        } else {
            $user->addRole(UserRole::CLIENT);
        }

        // Hash password
        if (isset($data['password'])) {
            $user->setPassword($hasher->hashPassword($user, $data['password']));
        }

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($user);
        $em->flush();

        $json = $serializer->serialize($user, 'json', ['groups' => 'user:read']);
        return new JsonResponse($json, Response::HTTP_CREATED, [], true);
    }

    #[Route('/{id}', name: 'user_update', methods: ['PUT'])]
    #[OA\RequestBody(
        content: new OA\JsonContent(ref: new Model(type: User::class, groups: ['user:write']))
    )]
    #[OA\Response(
        response: 200,
        description: 'User updated successfully',
        content: new OA\JsonContent(ref: new Model(type: User::class, groups: ['user:read']))
    )]
    public function update(
        Request $request, 
        User $user, 
        EntityManagerInterface $em, 
        SerializerInterface $serializer,
        UserPasswordHasherInterface $hasher,
        ValidatorInterface $validator
    ): JsonResponse {
        $serializer->deserialize($request->getContent(), User::class, 'json', [
            'object_to_populate' => $user,
            'groups' => ['user:write']
        ]);

        $data = json_decode($request->getContent(), true);
        if (isset($data['password'])) {
            $user->setPassword($hasher->hashPassword($user, $data['password']));
        }

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $em->flush();

        $json = $serializer->serialize($user, 'json', ['groups' => 'user:read']);
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'user_delete', methods: ['DELETE'])]
    #[OA\Response(
        response: 204,
        description: 'User deleted successfully'
    )]
    public function delete(User $user, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($user);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
