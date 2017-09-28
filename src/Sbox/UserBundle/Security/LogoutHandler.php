<?php

namespace Sbox\UserBundle\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class LogoutHandler implements LogoutSuccessHandlerInterface
{
    /**
     * Creates a Response object to send upon a successful logout.
     *
     * @param Request $request
     *
     * @return Response never null
     */
    public function onLogoutSuccess(Request $request): Response
    {
        $data = [
            'message' => 'Logout successful.'
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }
}
