<?php

namespace App\Service;

use OpenAPI\Server\Api\ReviewsApiInterface;
use OpenAPI\Server\Model\Review as OpenApiReview;
use OpenAPI\Server\Model\ReviewsPostRequest;
use App\Entity\Review as DbReview;
use App\Entity\Lot;
use App\Entity\Modification;
use App\Entity\CarModel;
use App\Entity\Manufacturer;
use App\Entity\EngineVolume;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class ReviewsApiService implements ReviewsApiInterface
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
    public function reviewsGet(
        int $page = 1,
        int $limit = 10,
        string $dateOrder = 'desc',
        string $ratingOrder = 'desc',
        int &$responseCode = 0,
        array &$responseHeaders = []
    ): array|object|null {
        $qb = $this->entityManager->getRepository(DbReview::class)->createQueryBuilder('r');
        $qb->orderBy('r.rating', $ratingOrder)->addOrderBy('r.created_at', $dateOrder);
        $qb->setFirstResult(($page - 1) * $limit)->setMaxResults($limit);

        $dbReviews = $qb->getQuery()->getResult();
        $apiReviews = [];

        foreach ($dbReviews as $review) {
            $user = $review->getAccount();
            $profile = $user ? $user->getProfile() : null;

            $apiReviews[] = new OpenApiReview([
                'id' => $review->getId(),
                'lotId' => $review->getLot() ? $review->getLot()->getId() : null,
                'manufacturer' => 'Toyota',
                'model' => 'Camry',
                'firstName' => $profile ? $profile->getFirstName() : 'Покупатель',
                'lastName' => $profile ? $profile->getLastName() : '',
                'avatarUrl' => $profile ? $profile->getAvatarUrl() : null,
                'rating' => $review->getRating(),
                'comment' => $review->getComment(),
                'createdAt' => $review->getCreatedAt() ? \DateTime::createFromImmutable($review->getCreatedAt()) : null
            ]);
        }

        $responseCode = 200;
        return $apiReviews;
    }
    public function reviewsPost(
        ReviewsPostRequest $reviewsPostRequest,
        int &$responseCode,
        array &$responseHeaders
    ): void {
        /** @var User|null $currentUser */
        $currentUser = $this->security->getUser();

        if (!$currentUser) {
            $responseCode = 401;
            return;
        }

        $lot = $this->entityManager->getRepository(Lot::class)->find($reviewsPostRequest->getLotId());

        if (!$lot) {
            // 1. Производитель
            $manufacturer = $this->entityManager->getRepository(Manufacturer::class)->findOneBy([]) ?? new Manufacturer();
            if (!$manufacturer->getId()) {
                $manufacturer->setName('Toyota');
                $this->entityManager->persist($manufacturer);
            }

            $model = $this->entityManager->getRepository(CarModel::class)->findOneBy([]) ?? new CarModel();
            if (!$model->getId()) {
                $model->setName('Camry');
                $model->setManufacturer($manufacturer);
                $this->entityManager->persist($model);
            }

            $volume = $this->entityManager->getRepository(EngineVolume::class)->findOneBy([]) ?? new EngineVolume();
            if (!$volume->getId()) {
                $volume->setVolume(2.5);
                $this->entityManager->persist($volume);
            }

            $this->entityManager->flush();

            $modification = new Modification();
            $modification->setModel($model);
            $modification->setEngineVolume($volume);
            $modification->setProductionYear(new \DateTime());
            $modification->setDrive('Передний');
            $modification->setTransmission('Автомат');
            $this->entityManager->persist($modification);
            $this->entityManager->flush();

            $lot = new Lot();
            $lot->setModification($modification);
            $lot->setPrice("1500000");
            $lot->setBodyNumber("AUTO_" . rand(1000, 9999));
            $lot->setCreatedAt(new \DateTimeImmutable());
            $this->entityManager->persist($lot);
            $this->entityManager->flush();
        }

        $review = new DbReview();
        $review->setAccount($currentUser);
        $review->setLot($lot);
        $review->setRating($reviewsPostRequest->getRating());
        $review->setComment($reviewsPostRequest->getComment());
        $review->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($review);
        $this->entityManager->flush();

        $responseCode = 201;
    }
}
