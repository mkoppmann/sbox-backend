<?php

namespace Sbox\UserBundle\Security;

use Sbox\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class UserAuthenticator extends AbstractGuardAuthenticator
{
    const AUTHENTICATION_UNSUCCESSFUL_MESSAGE = 'Invalid credentials.';
    const SALT = "";
    /**
     * @var UserProvider
     */
    protected $userProvider;
    /** @var EncoderFactoryInterface */
    protected $encoderFactory;
    /** @var PasswordEncoderInterface */
    protected $passwordEncoder;

    public function __construct(UserProvider $userProvider, EncoderFactoryInterface $encoderFactory)
    {
        $this->userProvider = $userProvider;
        $this->encoderFactory = $encoderFactory;
        $this->passwordEncoder = $this->encoderFactory->getEncoder(User::class);
    }

    /**
     * Returns a response that directs the user to authenticate.
     *
     * This is called when an anonymous request accesses a resource that
     * requires authentication. The job of this method is to return some
     * response that "helps" the user start into the authentication process.
     *
     * Examples:
     *  A) For a form login, you might redirect to the login page
     *      return new RedirectResponse('/login');
     *  B) For an API token authentication system, you return a 401 response
     *      return new Response('Auth header required', 401);
     *
     * @param Request $request The request that resulted in an
     * AuthenticationException
     * @param AuthenticationException $authException The exception that started
     * the authentication process
     *
     * @return Response
     */
    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        $data = [
            // you might translate this message
            'message' => 'Authentication required.'
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Get the authentication credentials from the request and return them
     * as any type (e.g. an associate array). If you return null, authentication
     * will be skipped.
     *
     * Whatever value you return here will be passed to getUser() and
     * checkCredentials()
     *
     * For example, for a form login, you might:
     *
     *      if ($request->request->has('_username')) {
     *          return array(
     *              'username' => $request->request->get('_username'),
     *              'password' => $request->request->get('_password'),
     *          );
     *      } else {
     *          return;
     *      }
     *
     * Or for an API token that's on a header, you might use:
     *
     *      return array('api_key' => $request->headers->get('X-API-TOKEN'));
     *
     * @param Request $request
     *
     * @return array|null
     */
    public function getCredentials(Request $request): ?array
    {
        return $request->request->has('username') ? [
            'username' => $request->request->get('username'),
            'password' => $request->request->get('password'),
        ] : null;
    }

    /**
     * Return a UserInterface object based on the credentials.
     *
     * The *credentials* are the return value from getCredentials()
     *
     * You may throw an AuthenticationException if you wish. If you return
     * null, then a UsernameNotFoundException is thrown for you.
     *
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     *
     * @throws AuthenticationException
     *
     * @return User
     */
    public function getUser($credentials, UserProviderInterface $userProvider): User
    {
        try {
            /** @var User $user */
            $user = $this
                ->userProvider
                ->loadUserByUsername($credentials['username']);
        } catch (UsernameNotFoundException $e) {
            // We want to avoid user enumeration.
            throw new AuthenticationException(
                $this::AUTHENTICATION_UNSUCCESSFUL_MESSAGE
            );
        }

        return $user;
    }

    /**
     * Returns true if the credentials are valid.
     *
     * If any value other than true is returned, authentication will
     * fail. You may also throw an AuthenticationException if you wish
     * to cause authentication to fail.
     *
     * The *credentials* are the return value from getCredentials()
     *
     * @param mixed $credentials
     * @param UserInterface $user
     *
     * @return bool
     *
     * @throws AuthenticationException
     */
    public function checkCredentials($credentials, UserInterface $user): bool
    {
        try {
            /** @var User $user */
            $user = $this
                ->userProvider
                ->loadUserByUsername($credentials['username']);
            $userPassword = $user->getPassword();
        } catch (UsernameNotFoundException $e) {
            /**
             * We generate a hash of the user provided password, so that this
             * path takes approximately the same time, as the isPasswordValid()
             * path. This prevents timing attacks and therefore prevents user
             * enumeration.
             */
            $this
                ->passwordEncoder
                ->encodePassword($credentials['password'], $this::SALT);
            throw new AuthenticationException(
                $this::AUTHENTICATION_UNSUCCESSFUL_MESSAGE
            );
        }

        if (!$this->passwordEncoder->isPasswordValid($userPassword, $credentials['password'], $this::SALT)) {
            throw new AuthenticationException(
                $this::AUTHENTICATION_UNSUCCESSFUL_MESSAGE
            );
        }

        return true;
    }

    /**
     * Called when authentication executed, but failed (e.g. wrong username
     * password).
     *
     * This should return the Response sent back to the user, like a
     * RedirectResponse to the login page or a 403 response.
     *
     * If you return null, the request will continue, but the user will
     * not be authenticated. This is probably not what you want to do.
     *
     * @param Request $request
     * @param AuthenticationException $exception
     *
     * @return JsonResponse
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): JsonResponse
    {
        $data = [
            'message' => $exception->getMessage()

            // or to translate this message
            // $this
            //   ->translator
            //   ->trans($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Called when authentication executed and was successful!
     *
     * This should return the Response sent back to the user, like a
     * RedirectResponse to the last page they visited.
     *
     * If you return null, the current request will continue, and the user
     * will be authenticated. This makes sense, for example, with an API.
     *
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey The provider (i.e. firewall) key
     *
     * @return JsonResponse
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): JsonResponse
    {
        $data = [
            // you might translate this message
            'message' => 'Authentication successful.'
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * Does this method support remember me cookies?
     *
     * Remember me cookie will be set if *all* of the following are met:
     *  A) This method returns true
     *  B) The remember_me key under your firewall is configured
     *  C) The "remember me" functionality is activated. This is usually
     *      done by having a _remember_me checkbox in your form, but
     *      can be configured by the "always_remember_me" and "remember_me_parameter"
     *      parameters under the "remember_me" firewall key
     *
     * @return bool
     */
    public function supportsRememberMe(): bool
    {
        return false;
    }
}
