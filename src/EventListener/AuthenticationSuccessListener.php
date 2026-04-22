<?php

namespace App\EventListener;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\Serializer\SerializerInterface;

class AuthenticationSuccessListener
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }

        $data = $event->getData();
        $userJson = $this->serializer->serialize($user, 'json', ['groups' => 'user:read']);
        $userData = json_decode($userJson, true);

        $data['user'] = $userData;

        $event->setData($data);
    }
}
