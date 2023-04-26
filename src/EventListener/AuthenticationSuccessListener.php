<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticationSuccessListener {

public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
/**
 * @param AuthenticationSuccessEvent $event
 */
{
    $data = $event->getData();
    $user = $event->getUser();

    if (!$user instanceof UserInterface) {
        return;
    }

    $data['data'] = array(
        'roles' => $user->getRoles(),
    );

    $event->setData($data);
}
}
