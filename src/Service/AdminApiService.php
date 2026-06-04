<?php

namespace App\Service;

use OpenAPI\Server\Api\AdminApiInterface;
use OpenAPI\Server\Model\UserListItem;
use OpenAPI\Server\Model\ProfileResponse;
use OpenAPI\Server\Model\Profile as OpenAPIProfile;
use OpenAPI\Server\Model\Request as OpenAPIRequest; // Добавляем импорт модели Request
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class AdminApiService implements AdminApiInterface
{
    private string $bearerToken = '';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security
    ) {}

    public function setbearerAuth(?string $value): void
    {
        $this->bearerToken = $value ?? '';
    }

    private function isAdmin(): bool
    {
        $user = $this->security->getUser();
        // Проверяем роль через вхождение в массив ролей
        return $user instanceof User && in_array('ROLE_ADMIN', $user->getRoles());
    }

    public function adminUsersGet(int $page = 1, int $limit = 10, int &$responseCode = 0, array &$responseHeaders = []): array|object|null
    {
        if (!$this->isAdmin()) {
            $responseCode = 403;
            return null;
        }

        $qb = $this->entityManager->getRepository(User::class)->createQueryBuilder('u');
        $qb->setFirstResult(($page - 1) * $limit)->setMaxResults($limit);

        $users = $qb->getQuery()->getResult();
        $result = [];

        foreach ($users as $user) {
            $profile = $user->getProfile();
            $result[] = new UserListItem([
                'id' => $user->getId(),
                'firstName' => $profile ? $profile->getFirstName() : '',
                'lastName' => $profile ? $profile->getLastName() : '',
                'phoneNumber' => $profile ? $profile->getPhoneNumber() : '',
                'avatarUrl' => $profile ? $profile->getAvatarUrl() : null,
                // Считаем только не решенные заявки или все? В ProfileApiService было фильтрованное.
                // Для консистентности используем ту же логику:
                'activeRequestsCount' => $user->getRequests()->filter(fn($r) => $r->getStatus() !== 'Решена')->count(),
                'isAdmin' => in_array('ROLE_ADMIN', $user->getRoles())
            ]);
        }

        $responseCode = 200;
        return $result;
    }

    public function adminUsersIdGet(int $id, int &$responseCode, array &$responseHeaders): array|object|null
    {
        if (!$this->isAdmin()) {
            $responseCode = 403;
            return null;
        }

        $user = $this->entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            $responseCode = 404;
            return null;
        }

        $dbProfile = $user->getProfile();

        $openApiRequests = [];
        foreach ($user->getRequests() as $dbRequest) {
            $openApiRequests[] = new OpenAPIRequest([
                'id' => $dbRequest->getId(),
                'carName' => $dbRequest->getCarName(),
                'lot' => null,
                'callTime' => $dbRequest->getCallTime()?->format('Y-m-d H:i:s'),
                'comment' => $dbRequest->getComment(),
                'isSolved' => $dbRequest->getStatus() === 'Решена',
                'createdAt' => $dbRequest->getCreatedAt() ? \DateTime::createFromImmutable($dbRequest->getCreatedAt()) : null
            ]);
        }

        $responseCode = 200;
        return new ProfileResponse([
            'user' => new OpenAPIProfile([
                'id' => $user->getId(),
                'firstName' => $dbProfile ? $dbProfile->getFirstName() : '',
                'lastName' => $dbProfile ? $dbProfile->getLastName() : '',
                'phoneNumber' => $dbProfile ? $dbProfile->getPhoneNumber() : '',
                'avatarUrl' => $dbProfile ? $dbProfile->getAvatarUrl() : null,
                'email' => $user->getEmail(),
                'isAdmin' => in_array('ROLE_ADMIN', $user->getRoles()),
                'activeRequestsCount' => $user->getRequests()->filter(fn($r) => $r->getStatus() !== 'Решена')->count()
            ]),
            'requests' => $openApiRequests
        ]);
    }

    public function adminUsersIdDelete(int $id, int &$responseCode, array &$responseHeaders): void
    {
        if (!$this->isAdmin()) {
            $responseCode = 403;
            return;
        }

        $user = $this->entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            $responseCode = 404;
            return;
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();
        $responseCode = 204;
    }
}
