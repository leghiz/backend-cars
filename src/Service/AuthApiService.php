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
use OpenAPI\Server\Model\AuthVerifyResendPostRequest;
use OpenAPI\Server\Model\AuthTokenRefreshPostRequest;
use OpenAPI\Server\Model\AuthTokenRefreshPost200Response;
use App\Entity\User;
use App\Entity\Profile;
use App\Entity\Role;
use App\Entity\RefreshToken;
use App\Entity\VerificationCode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Message\SendEmailMessage;

class AuthApiService implements AuthApiInterface
{
    private string $bearerToken = '';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly JWTEncoderInterface $jwtEncoder,
        private readonly MessageBusInterface $messageBus
    ) {
    }

    public function setbearerAuth(?string $value): void
    {
        $this->bearerToken = $value ?? '';
    }

    public function authRegisterPost(AuthRegisterPostRequest $authRegisterPostRequest, int &$responseCode, array &$responseHeaders): void
    {
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $authRegisterPostRequest->getEmail()]);

        // Почта занята ТОЛЬКО если аккаунт уже подтверждён.
        if ($existingUser && $existingUser->isVerified()) {
            $responseCode = 400;
            return;
        }

        if ($existingUser) {
            // Почта есть, но не подтверждена: обновляем данные этого аккаунта и переотправляем код,
            // фронт уводит на верификацию как при обычной регистрации (ответ 201).
            $user = $existingUser;

            // Старые коды больше не нужны — действителен будет только новый.
            $verificationRepo = $this->entityManager->getRepository(VerificationCode::class);
            foreach ($verificationRepo->findBy(['account' => $user]) as $oldCode) {
                $this->entityManager->remove($oldCode);
            }

            $profile = $user->getProfile() ?? new Profile();
        } else {
            $user = new User();
            $user->setEmail($authRegisterPostRequest->getEmail());
            $user->setIsVerified(false);

            $defaultRole = $this->entityManager->getRepository(Role::class)->findOneBy(['name' => 'ROLE_USER']);
            $user->setRole($defaultRole);

            $profile = new Profile();
        }

        $user->setPassword(password_hash($authRegisterPostRequest->getPassword(), PASSWORD_BCRYPT));

        $profile->setFirstName($authRegisterPostRequest->getFirstName());
        $profile->setLastName($authRegisterPostRequest->getLastName());
        $profile->setPhoneNumber($authRegisterPostRequest->getPhoneNumber());
        $user->setProfile($profile);

        $code = (string) random_int(100000, 999999);

        $verification = new VerificationCode();
        $verification->setAccount($user);
        $verification->setCode($code);
        $verification->setExpiresAt((new \DateTimeImmutable())->modify('+1 hour'));

        $this->entityManager->persist($user);
        $this->entityManager->persist($profile);
        $this->entityManager->persist($verification);
        $this->entityManager->flush();

        $this->messageBus->dispatch(new SendEmailMessage(
            $user->getEmail(),
            'Подтверждение регистрации',
            $code
        ));

        $responseCode = 201;
    }

    public function authVerifyPost(AuthVerifyPostRequest $authVerifyPostRequest, int &$responseCode, array &$responseHeaders): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $authVerifyPostRequest->getEmail()]);

        if (!$user) {
            $responseCode = 404;
            return;
        }

        $verificationRepo = $this->entityManager->getRepository(VerificationCode::class);
        $verification = $verificationRepo->findOneBy([
            'account' => $user,
            'code' => $authVerifyPostRequest->getCode()
        ]);

        if (!$verification) {
            $responseCode = 400;
            return;
        }

        if ($verification->getExpiresAt() < new \DateTime()) {
            $responseCode = 400;
            return;
        }

        $user->setIsVerified(true);

        $this->entityManager->remove($verification);
        $this->entityManager->flush();

        $responseCode = 200;
    }

    public function authVerifyResendPost(AuthVerifyResendPostRequest $authVerifyResendPostRequest, int &$responseCode, array &$responseHeaders): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $authVerifyResendPostRequest->getEmail()]);

        if (!$user) {
            $responseCode = 404;
            return;
        }

        if ($user->isVerified()) {
            $responseCode = 400;
            return;
        }

        $verificationRepo = $this->entityManager->getRepository(VerificationCode::class);
        $oldCodes = $verificationRepo->findBy(['account' => $user]);
        foreach ($oldCodes as $oldCode) {
            $this->entityManager->remove($oldCode);
        }
        $this->entityManager->flush();

        $code = (string) random_int(100000, 999999);

        $verification = new VerificationCode();
        $verification->setAccount($user);
        $verification->setCode($code);
        $verification->setExpiresAt((new \DateTimeImmutable())->modify('+1 hour'));

        $this->entityManager->persist($verification);
        $this->entityManager->flush();

        $this->messageBus->dispatch(new SendEmailMessage(
            $user->getEmail(),
            'Подтверждение регистрации',
            $code
        ));

        $responseCode = 200;
    }

    public function authLoginPost(AuthLoginPostRequest $authLoginPostRequest, int &$responseCode, array &$responseHeaders): array|object|null
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $authLoginPostRequest->getEmail()]);

        if (!$user || !password_verify($authLoginPostRequest->getPassword(), $user->getPassword())) {
            $responseCode = 401;
            return null;
        }

        if (!$user->isVerified()) {
            $responseCode = 403;
            return null;
        }

        $token = $this->jwtManager->create($user);
        $isAdmin = $user->getRole() && $user->getRole()->getName() === 'ROLE_ADMIN';

        $refreshTokenStr = bin2hex(random_bytes(32));

        $refreshToken = new RefreshToken();
        $refreshToken->setRefreshToken($refreshTokenStr);
        $refreshToken->setUsername($user->getEmail());
        $refreshToken->setValid((new \DateTime())->modify('+30 days'));

        $this->entityManager->persist($refreshToken);
        $this->entityManager->flush();

        $responseCode = 200;

        return new AuthLoginPost200Response([
            'token' => $token,
            'refreshToken' => $refreshTokenStr,
            'isAdmin' => $isAdmin
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

        if (!$user || !$user->isVerified()) {
            $responseCode = 400;
            return;
        }

        $verificationRepo = $this->entityManager->getRepository(VerificationCode::class);
        $oldCodes = $verificationRepo->findBy(['account' => $user]);
        foreach ($oldCodes as $oldCode) {
            $this->entityManager->remove($oldCode);
        }
        $this->entityManager->flush();

        $code = (string) random_int(100000, 999999);

        $resetCode = new VerificationCode();
        $resetCode->setAccount($user);
        $resetCode->setCode($code);
        $resetCode->setExpiresAt((new \DateTimeImmutable())->modify('+15 minutes'));

        $this->entityManager->persist($resetCode);
        $this->entityManager->flush();

        $this->messageBus->dispatch(new SendEmailMessage(
            $user->getEmail(),
            'Восстановление пароля',
            $code
        ));


        $responseCode = 200;
    }

    public function authForgotPasswordVerifyPost(AuthForgotPasswordVerifyPostRequest $authForgotPasswordVerifyPostRequest, int &$responseCode, array &$responseHeaders): array|object|null
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $authForgotPasswordVerifyPostRequest->getEmail()]);

        if (!$user) {
            $responseCode = 400;
            return null;
        }

        $verificationRepo = $this->entityManager->getRepository(VerificationCode::class);
        $verification = $verificationRepo->findOneBy([
            'account' => $user,
            'code' => $authForgotPasswordVerifyPostRequest->getCode()
        ]);

        if (!$verification || $verification->getExpiresAt() < new \DateTime()) {
            $responseCode = 400;
            return null;
        }

        $this->entityManager->remove($verification);
        $this->entityManager->flush();

        $resetToken = $this->jwtEncoder->encode([
            'email' => $user->getEmail(),
            'purpose' => 'password_reset',
            'exp' => time() + 900,
        ]);

        $responseCode = 200;

        return new AuthForgotPasswordVerifyPost200Response([
            'resetToken' => $resetToken
        ]);
    }

    public function authForgotPasswordResetPost(AuthForgotPasswordResetPostRequest $authForgotPasswordResetPostRequest, int &$responseCode, array &$responseHeaders): void
    {
        try {
            $payload = $this->jwtEncoder->decode($authForgotPasswordResetPostRequest->getResetToken());
        } catch (\Throwable $e) {
            $payload = null;
        }

        if (!$payload || ($payload['purpose'] ?? null) !== 'password_reset' || empty($payload['email'])) {
            $responseCode = 400;
            return;
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $payload['email']]);
        if (!$user) {
            $responseCode = 400;
            return;
        }

        $newPassword = password_hash($authForgotPasswordResetPostRequest->getNewPassword(), PASSWORD_BCRYPT);
        $user->setPassword($newPassword);
        $this->entityManager->flush();

        $responseCode = 200;
    }

    public function authTokenRefreshPost(AuthTokenRefreshPostRequest $authTokenRefreshPostRequest, int &$responseCode, array &$responseHeaders): array|object|null
    {
        $clientRefreshToken = $authTokenRefreshPostRequest->getRefreshToken();

        $refreshTokenRepo = $this->entityManager->getRepository(RefreshToken::class);
        $refreshToken = $refreshTokenRepo->findOneBy(['refreshToken' => $clientRefreshToken]);

        if (!$refreshToken || $refreshToken->getValid() < new \DateTime()) {
            $responseCode = 401;
            return null;
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $refreshToken->getUsername()]);
        if (!$user) {
            $responseCode = 401;
            return null;
        }

        $newAccessToken = $this->jwtManager->create($user);
        $refreshToken->setValid((new \DateTime())->modify('+30 days'));

        $this->entityManager->flush();

        $responseCode = 200;

        return new AuthTokenRefreshPost200Response([
            'token' => $newAccessToken,
            'refreshToken' => $refreshToken->getRefreshToken()
        ]);
    }
}