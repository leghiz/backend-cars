<?php

namespace App\Service;

use App\Repository\UserRepository;
use OpenAPI\Server\Api\AdminApiInterface;
use OpenAPI\Server\Model\{UserListItem, ProfileResponse, Profile as OpenAPIProfile, Request as OpenAPIRequest};
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class AdminApiService implements AdminApiInterface
{
    private string $bearerToken = '';

    public function __construct(
        private readonly UserRepository $userRepository,
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
        return $user instanceof User && in_array('ROLE_ADMIN', $user->getRoles());
    }

    public function adminUsersGet(int $page = 1, int $limit = 10, int &$responseCode = 0, array &$responseHeaders = []): array|object|null
    {
        if (!$this->isAdmin()) {
            $responseCode = 403;
            return null;
        }

        $users = $this->userRepository->findPaginated($page, $limit);
        $responseCode = 200;

        return array_map(fn(User $user) => new UserListItem([
            'id' => $user->getId(),
            'firstName' => $user->getProfile()?->getFirstName() ?? '',
            'lastName' => $user->getProfile()?->getLastName() ?? '',
            'phoneNumber' => $user->getProfile()?->getPhoneNumber() ?? '',
            'avatarUrl' => $user->getProfile()?->getAvatarUrl(),
            'activeRequestsCount' => $user->getRequests()->filter(fn($r) => $r->getStatus() !== 'Решена')->count(),
            'isAdmin' => in_array('ROLE_ADMIN', $user->getRoles())
        ]), $users);
    }

    public function adminUsersIdGet(int $id, int &$responseCode, array &$responseHeaders): array|object|null
    {
        if (!$this->isAdmin()) {
            $responseCode = 403;
            return null;
        }

        $user = $this->userRepository->findFullUserData($id);
        if (!$user) {
            $responseCode = 404;
            return null;
        }

        $responseCode = 200;
        return new ProfileResponse([
            'user' => new OpenAPIProfile([
                'id' => $user->getId(),
                'firstName' => $user->getProfile()?->getFirstName() ?? '',
                'lastName' => $user->getProfile()?->getLastName() ?? '',
                'phoneNumber' => $user->getProfile()?->getPhoneNumber() ?? '',
                'avatarUrl' => $user->getProfile()?->getAvatarUrl(),
                'email' => $user->getEmail(),
                'isAdmin' => in_array('ROLE_ADMIN', $user->getRoles()),
                'activeRequestsCount' => $user->getRequests()->filter(fn($r) => $r->getStatus() !== 'Решена')->count()
            ]),
            'requests' => array_map(fn($req) => new OpenAPIRequest([
                'id' => $req->getId(),
                'carName' => $req->getCarName(),
                'lot' => null,
                'callTime' => $req->getCallTime()?->format('Y-m-d H:i:s'),
                'comment' => $req->getComment(),
                'isSolved' => $req->getStatus() === 'Решена',
                'createdAt' => $req->getCreatedAt() ? \DateTime::createFromImmutable($req->getCreatedAt()) : null
            ]), $user->getRequests()->toArray())
        ]);
    }

    public function adminUsersIdDelete(int $id, int &$responseCode, array &$responseHeaders): void
    {
        if (!$this->isAdmin()) {
            $responseCode = 403;
            return;
        }

        $user = $this->userRepository->find($id);
        if (!$user) {
            $responseCode = 404;
            return;
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();
        $responseCode = 204;
    }
}