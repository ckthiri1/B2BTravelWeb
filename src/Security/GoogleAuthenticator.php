<?php
namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use League\OAuth2\Client\Provider\GoogleUser; // Ensure this is the correct namespace for GoogleUser
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class GoogleAuthenticator extends OAuth2Authenticator
{
    use TargetPathTrait;

    public function __construct(
        private ClientRegistry $clientRegistry,
        private EntityManagerInterface $entityManager,
        private RouterInterface $router,
        private UserPasswordHasherInterface $passwordHasher,
        private HttpKernelInterface $httpKernel
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('google');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function() use ($accessToken, $client) {
                /** @var GoogleUser $googleUser */
                $googleUser = $client->fetchUserFromToken($accessToken);
                $email = $googleUser->getEmail();

                $existingUser = $this->entityManager->getRepository(User::class)
                    ->findOneBy(['googleId' => $googleUser->getId()]);

                if ($existingUser) {
                    return $existingUser;
                }

                $user = $this->entityManager->getRepository(User::class)
                    ->findOneBy(['email' => $email]);

                if ($user) {
                    $user->setGoogleId($googleUser->getId());
                } else {
                    $user = new User();
                    $user->setGoogleId($googleUser->getId());
                    $user->setEmail($email);
                    
                    // Split name into first/last names
                    $nameParts = explode(' ', $googleUser->getName(), 2);
                    $user->setPrenom($nameParts[0]);
                    $user->setNom($nameParts[1] ?? '');
                    
                    // Generate random password
                    $password = bin2hex(random_bytes(16));
                    $user->setHash($this->passwordHasher->hashPassword($user, $password));
                    
                    $user->setRole('user');
                    $user->setIsVerified(true);
                    $user->setImageUrl($googleUser->getAvatar());
                }

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $targetPath = $this->getTargetPath($request->getSession(), $firewallName);
        
        if (!$targetPath) {
            $targetPath = $this->router->generate('profile');
        }

        return new RedirectResponse($targetPath);
    }


    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new RedirectResponse($this->router->generate('app_login'));
    }
}