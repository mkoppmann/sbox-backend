<?php

namespace Sbox\UserBundle\Controller;

use Sbox\UserBundle\Entity\User;
use Sbox\UserBundle\Manager\UserManagerInterface;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;

class UserController extends FOSRestController
{

    /**
     * @Route("", methods={"GET"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function userAction(): Response
    {
        $userManager = $this->container->get('sbox_user.user_manager');
        $users = $userManager->getUsers();

        $view = $this->view($users);
        return $this->handleView($view);
    }

    /**
     * @Route("/me", methods={"GET"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function userMeAction(): Response
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $view = $this->view($user);
        return $this->handleView($view);
    }

    /**
     * @Route("/{id}",methods={"GET"})
     * @param string $id
     * @return Response
     */
    public function userByIdAction(string $id): Response
    {
        $userManager = $this->container->get('sbox_user.user_manager');
        $user = $userManager->findUserBy(["id" => $id]);
        if ($user instanceof User) {
            $view = $this->view($user);
        } else {
            $view = $this->view(null);
        }
        return $this->handleView($view);
    }
}
