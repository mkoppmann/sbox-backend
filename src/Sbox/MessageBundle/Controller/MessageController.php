<?php

namespace Sbox\MessageBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use http\Exception\InvalidArgumentException;
use Sbox\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sbox\MessageBundle\Entity\Message;
use Sbox\MessageBundle\Manager\MessageManagerInterface;
use DateTime;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MessageController extends FOSRestController
{
    /**
     * @Rest\Get("messages")
     * @return Response
     */
    public function getMessagesAction(): Response
    {
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $messages = $user->getMessages($user);
        $view = $this->view($messages);
        return $this->handleView($view);
    }

    /**
     * @Route("messages",methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function postMessageAction(Request $request): Response
    {
        /**
         * @var bool
         */
        $error = false;
        /**
         * @var View
         */
        $view = $this->view("empty");
        $messageManager = $this->container->get('sbox_message.message_manager');
        $logger = $this->get('logger');
        $logger->info('################');
        $logger->info('Hallo postMessageAction');



        try {
            $logger->info("Hallo try");

            if (!$request->request->has('subject')) {
                throw new \Exception('no subject given');
            }
            $logger->info('has subject');

            $subject = $request->request->get('subject');
            $sender = $this->container->get('security.token_storage')->getToken()->getUser();

            if (!$request->request->has('recipients')) {
                throw new \Exception('no recipients given');
            }

            $logger->info('has recipients');

            $recipientsRequest = $request->request->get('recipients');

            if (!is_array($recipientsRequest)) {
                throw new \Exception('recipients must be an array');
            }

            $logger->info('recipients is array');

            $recipients = array();
            foreach ($recipientsRequest as $recipient) {
                $logger->info('hallo foreach');
                $logger->info($recipient);
                $userManager = $this->container->get('sbox_user.user_manager');
                $user = $userManager->findUserBy(['id' => $recipient]);
                if (!$user) {
                    throw new \Exception('Invalid Recipient User ID');
                } else {
                    array_push($recipients, $user);
                }
            }
                $logger->info('recipients size: '.sizeof($recipients));
            if (sizeof($recipients) == 0) {
                $logger->warning("No Recipient found, throwing Bad Request");
                throw new \Exception('No Recipient');
            }


                $date = new DateTime(); //don't trust the client

            if (!$request->request->has('message')) {
                throw new \Exception('no message body given');
            }
                $messageBody = $request->request->get('message');

                $logger->info("Everythings ok, creating message");
                $message = $messageManager->createMessage($subject, $sender, $recipients, $date, $messageBody);

            if (!$message) {
                throw new \Exception('Message Error');
            }
                $view = $this->view($message);
        } catch (\Exception $e) {
            $error = ["message" => $e->getMessage(), "detail" => $e->getTraceAsString()];
            $view = $this->view($error, 400);
            $logger->warning($e);
        }

        $logger->info('returning Response');
        return $this->handleView($view);
    }

    /**
     * @Route("messages/{id}",methods={"GET"})
     * @param string $id
     * @return Response
     */
    public function getMessageByIdAction(string $id): Response
    {
        $messageManager = $this->container->get('sbox_message.message_manager');
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        $message = $messageManager->findMessageById($id, $user);
        if (!$message) {
            throw new NotFoundHttpException('Message id not found');
        }

        $view = $this->view($message);
        return $this->handleView($view);
    }

    /**
     * @Route("messages/{id}",methods={"DELETE"})
     * @param string $id
     * @return Response
     */
    public function deleteMessageByIdAction(string $id): Response
    {
        $messageManager = $this->container->get('sbox_message.message_manager');
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        $message = $messageManager->findMessageById($id, $user);
        if (!$message) {
            throw new NotFoundHttpException('Message id not found');
        }

        if (!$message->getSender() == $user) {
            throw new AccessDeniedHttpException('Not allowed to delete messaged');
        }

        $messageId = $message->getId();
        $messageManager->deleteMessage($message);

        
        $responseMessage = ['message' => 'Message ' . $messageId . ' was deleted'];
        $view = $this->view($responseMessage);
        return $this->handleView($view);
    }
}
