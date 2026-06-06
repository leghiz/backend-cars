<?php

namespace App\Service;

use OpenAPI\Server\Api\RequestsApiInterface;
use OpenAPI\Server\Model\RequestsPostRequest;
use OpenAPI\Server\Model\RequestsIdPatchRequest;
use App\Entity\Request as DbRequest;
use App\Entity\User;
use App\Entity\Lot;
use App\Entity\CarModel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestsApiService implements RequestsApiInterface
{
    private string $bearerToken = '';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
        private readonly RequestStack $requestStack
    ) {
    }

    public function setbearerAuth(?string $value): void
    {
        $this->bearerToken = $value ?? '';
    }

    public function requestsPost(
        RequestsPostRequest $requestsPostRequest,
        int &$responseCode,
        array &$responseHeaders
    ): void {
        $currentUser = $this->security->getUser();
        if (!$currentUser) {
            $responseCode = 401;
            return;
        }

        $dbRequest = new DbRequest();
        $dbRequest->setAccount($currentUser);
        $dbRequest->setCarName($requestsPostRequest->getCarName() ?? '');

        if ($requestsPostRequest->getCallTime()) {
            try {
                $dbRequest->setCallTime(new \DateTime($requestsPostRequest->getCallTime()));
            } catch (\Exception $e) {
                $dbRequest->setCallTime(null);
            }
        }

        $dbRequest->setComment($requestsPostRequest->getComment());
        $dbRequest->setStatus('Новая');
        $dbRequest->setCreatedAt(new \DateTimeImmutable());

        $rawPayload = json_decode(file_get_contents('php://input'), true) ?? [];
        $lotId = $rawPayload['lot_id'] ?? $rawPayload['lotId'] ?? null;

        if ($lotId !== null) {
            $carModelReference = $this->entityManager->getReference(CarModel::class, (int)$lotId);
            $dbRequest->setCarModel($carModelReference);
        }

        $this->entityManager->persist($dbRequest);
        $this->entityManager->flush();

        $responseCode = 201;
    }

    public function requestsIdPatch(
        int $id,
        ?RequestsIdPatchRequest $requestsIdPatchRequest,
        int &$responseCode,
        array &$responseHeaders
    ): void {
        $currentUser = $this->security->getUser();
        if (!$currentUser) {
            $responseCode = 401;
            return;
        }

        /** @var DbRequest|null $dbRequest */
        $dbRequest = $this->entityManager->getRepository(DbRequest::class)->find($id);

        if (!$dbRequest) {
            $responseCode = 404;
            return;
        }

        $isAdmin = $currentUser->getRole() && $currentUser->getRole()->getName() === 'ROLE_ADMIN';
        if ($dbRequest->getAccount()->getId() !== $currentUser->getId() && !$isAdmin) {
            $responseCode = 403;
            return;
        }

        $rawPayload = json_decode(file_get_contents('php://input'), true) ?? [];

        if ($requestsIdPatchRequest?->getComment() !== null) {
            $dbRequest->setComment($requestsIdPatchRequest->getComment());
        }

        if ($requestsIdPatchRequest?->isIsSolved() !== null) {
            $dbRequest->setStatus($requestsIdPatchRequest->isIsSolved() ? 'Решена' : 'Новая');
        }

        $lotId = $requestsIdPatchRequest?->getLotId() ?? $rawPayload['lot_id'] ?? $rawPayload['lotId'] ?? null;

        if ($lotId !== null) {
            $carModelReference = $this->entityManager->getReference(CarModel::class, (int)$lotId);
            $dbRequest->setCarModel($carModelReference);
        }

        $this->entityManager->flush();
        $responseCode = 200;
    }
    public function requestsIdDelete(
        int $id,
        int &$responseCode,
        array &$responseHeaders
    ): void {
        /** @var User|null $currentUser */
        $currentUser = $this->security->getUser();

        if (!$currentUser) {
            $responseCode = 401;
            return;
        }

        $dbRequest = $this->entityManager->getRepository(DbRequest::class)->find($id);

        if (!$dbRequest) {
            $responseCode = 404;
            return;
        }

        $isAdmin = $currentUser->getRole() && $currentUser->getRole()->getName() === 'ROLE_ADMIN';
        if ($dbRequest->getAccount()->getId() !== $currentUser->getId() && !$isAdmin) {
            $responseCode = 403;
            return;
        }

        $this->entityManager->remove($dbRequest);
        $this->entityManager->flush();

        $responseCode = 204;
    }
}