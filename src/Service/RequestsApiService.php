<?php

namespace App\Service;

use OpenAPI\Server\Api\RequestsApiInterface;
use OpenAPI\Server\Model\RequestsPostRequest;
use OpenAPI\Server\Model\RequestsIdPatchRequest;
use App\Entity\Request as DbRequest;
use App\Entity\User;
use App\Entity\Lot;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class RequestsApiService implements RequestsApiInterface
{
    private string $bearerToken = '';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security
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
        /** @var User|null $currentUser */
        $currentUser = $this->security->getUser();

        if (!$currentUser) {
            $responseCode = 401;
            return;
        }

        $dbRequest = new DbRequest();
        $dbRequest->setAccount($currentUser);
        $dbRequest->setCarName($requestsPostRequest->getCarName());

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

        $isAdmin = $currentUser->getRole() && $currentUser->getRole()->getName() === 'Admin';
        if ($dbRequest->getAccount()->getId() !== $currentUser->getId() && !$isAdmin) {
            $responseCode = 403;
            return;
        }

        if ($requestsIdPatchRequest === null) {
            $responseCode = 200;
            return;
        }

        if ($requestsIdPatchRequest->getComment() !== null) {
            $dbRequest->setComment($requestsIdPatchRequest->getComment());
        }

        if ($requestsIdPatchRequest->getIsSolved() !== null) {
            $dbRequest->setStatus($requestsIdPatchRequest->getIsSolved() ? 'Решена' : 'Новая');
        }

        if ($requestsIdPatchRequest->getLotId() !== null) {
            $lot = $this->entityManager->getRepository(Lot::class)->find($requestsIdPatchRequest->getLotId());
            if ($lot) {
                if (method_exists($dbRequest, 'setLot')) {
                    $dbRequest->setLot($lot);
                }
            }
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

        $isAdmin = $currentUser->getRole() && $currentUser->getRole()->getName() === 'Admin';
        if ($dbRequest->getAccount()->getId() !== $currentUser->getId() && !$isAdmin) {
            $responseCode = 403;
            return;
        }

        $this->entityManager->remove($dbRequest);
        $this->entityManager->flush();

        $responseCode = 204;
    }
}
