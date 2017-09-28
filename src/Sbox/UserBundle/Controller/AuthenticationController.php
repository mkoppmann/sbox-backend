<?php

namespace Sbox\UserBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swagger\Annotations as SWG;

class AuthenticationController extends FOSRestController
{
    /**
     * @Route("/login", methods={"POST"})
     * @SWG\Post(
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="The credentials object.",
     *         type="json",
     *         @SWG\Schema(
     *             type="object",
     *             required={"username", "password"},
     *             @SWG\Property(
     *                 type="string",
     *                 property="username",
     *                 example="testuser"
     *             ),
     *             @SWG\Property(
     *                 type="string",
     *                 property="password",
     *                 example="P@ssw0rd123!"
     *             )
     *         )
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Returns that the authentication was successful.",
     *         @SWG\Schema(
     *             type="object",
     *             required={"message"},
     *             @SWG\Property(
     *                 type="string",
     *                 property="message",
     *                 example="Authentication successful."
     *             ),
     *         )
     *     ),
     *     @SWG\Response(
     *         response=401,
     *         description="Returns that the authentication was not successful.",
     *         @SWG\Schema(
     *             type="object",
     *             required={"message"},
     *             @SWG\Property(
     *                 type="string",
     *                 property="message",
     *                 example="Invalid credentials."
     *             ),
     *         )
     *     )
     * )
     * @return void
     */
    public function loginAction(): void
    {
        // This is never called directly. See UserAuthenticator class.
    }

    /**
     * @Route("/logout", methods={"POST"})
     * @SWG\Post(
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="Returns that the logout was successful.",
     *         @SWG\Schema(
     *             type="object",
     *             required={"message"},
     *             @SWG\Property(
     *                 type="string",
     *                 property="message",
     *                 example="Logout successful."
     *             ),
     *         )
     *     )
     * )
     * @return void
     */
    public function logoutAction(): void
    {
        // This is never called directly. See UserAuthenticator class.
    }
}
