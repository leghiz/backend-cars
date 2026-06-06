<?php

namespace App\Service;

use OpenAPI\Server\Api\AuthApiInterface;
use OpenAPI\Server\Model\AuthRegisterPostRequest;
use OpenAPI\Server\Model\AuthVerifyPostRequest;
use OpenAPI\Server\Model\AuthLoginPostRequest;
use OpenAPI\Server\Model\AuthLoginPost200Response;
use OpenAPI\Server\Model\AuthChangePasswordPostRequest;
use OpenAPI\Server\Model\AuthForgotPasswordRequestPostRequest;
use OpenAPI\Server\Model\AuthForgotPasswordVerifyPostRequest;
use OpenAPI\Server\Model\AuthForgotPasswordVerifyPost200Response;
use OpenAPI\Server\Model\AuthForgotPasswordResetPostRequest;
use App\Entity\User;
use App\Entity\Profile;
use App\Entity\Role; // Добавили импорт сущности Role
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class AuthApiService implements AuthApiInterface
{
    private string $bearerToken = '';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
        private readonly JWTTokenManagerInterface $jwtManager
    ) {

    }

    public function setbearerAuth(?string $value): void
    {
        $this->bearerToken = $value ?? '';
    }
    public function authRegisterPost(AuthRegisterPostRequest $authRegisterPostRequest, int &$responseCode, array &$responseHeaders): void
    {
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $authRegisterPostRequest->getEmail()]);
        if ($existingUser) {
            $responseCode = 400;
            return;
        }

        $user = new User();
        $user->setEmail($authRegisterPostRequest->getEmail());

        $hashedPassword = password_hash($authRegisterPostRequest->getPassword(), PASSWORD_BCRYPT);
        $user->setPassword($hashedPassword);
        $user->setIsVerified(false);

        $roleRepository = $this->entityManager->getRepository(Role::class);
        $defaultRole = $roleRepository->findOneBy(['name' => 'ROLE_USER']);

        $user->setRole($defaultRole);

        $profile = new Profile();
        $profile->setFirstName($authRegisterPostRequest->getFirstName());
        $profile->setLastName($authRegisterPostRequest->getLastName());
        $profile->setPhoneNumber($authRegisterPostRequest->getPhoneNumber());

        $user->setProfile($profile);

        $this->entityManager->persist($user);
        $this->entityManager->persist($profile);
        $this->entityManager->flush();

        $responseCode = 201;
    }
    public function authVerifyPost(AuthVerifyPostRequest $authVerifyPostRequest, int &$responseCode, array &$responseHeaders): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $authVerifyPostRequest->getEmail()]);

        if (!$user) {
            $responseCode = 404;
            return;
        }

        if ($authVerifyPostRequest->getCode() !== '123456') {
            $responseCode = 400;
            return;
        }

        $user->setIsVerified(true);
        $this->entityManager->flush();

        $responseCode = 200;
    }
    public function authLoginPost(AuthLoginPostRequest $authLoginPostRequest, int &$responseCode, array &$responseHeaders): array|object|null
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $authLoginPostRequest->getEmail()]);

        if (!$user || !password_verify($authLoginPostRequest->getPassword(), $user->getPassword())) {
            $responseCode = 401;
            return null;
        }

        $token = $this->jwtManager->create($user);
        $isAdmin = $user->getRole() && $user->getRole()->getName() === 'ROLE_ADMIN';

        $responseCode = 200;

        return new


        AuthLoginPost200Response([
            'token' => $token,
            'is_admin' => $isAdmin
        ]);
    }

    public function authChangePasswordPost(AuthChangePasswordPostRequest $authChangePasswordPostRequest, int &$responseCode, array &$responseHeaders): void
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            $responseCode = 401;
            return;
        }

        if (!password_verify($authChangePasswordPostRequest->getOldPassword(), $user->getPassword())) {
            $responseCode = 400;
            return;
        }

        $newHashedPassword = password_hash($authChangePasswordPostRequest->getNewPassword(), PASSWORD_BCRYPT);
        $user->setPassword($newHashedPassword);
        $this->entityManager->flush();

        $responseCode = 200;
    }
    public function authForgotPasswordRequestPost(AuthForgotPasswordRequestPostRequest $authForgotPasswordRequestPostRequest, int &$responseCode, array &$responseHeaders): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $authForgotPasswordRequestPostRequest->getEmail()]);

        if (!$user) {
            $responseCode = 200;
            return;
        }

        $responseCode = 200;
    }
    public function authForgotPasswordVerifyPost(AuthForgotPasswordVerifyPostRequest $authForgotPasswordVerifyPostRequest, int &$responseCode, array &$responseHeaders): array|object|null
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $authForgotPasswordVerifyPostRequest->getEmail()]);

        if (!$user || $authForgotPasswordVerifyPostRequest->getCode() !== '111111') {
            $responseCode = 400;
            return null;
        }

        $resetToken = bin2hex(random_bytes(16));

        $responseCode = 200;

        return new AuthForgotPasswordVerifyPost200Response([
            'reset_token' => $resetToken
        ]);
    }
    public function authForgotPasswordResetPost(AuthForgotPasswordResetPostRequest $authForgotPasswordResetPostRequest, int &$responseCode, array &$responseHeaders): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([]);

        if (!$user) {
            $responseCode = 400;
            return;
        }

        $newPassword = password_hash($authForgotPasswordResetPostRequest->getNewPassword(), PASSWORD_BCRYPT);
        $user->setPassword($newPassword);
        $this->entityManager->flush();

        $responseCode = 200;
    }
}
