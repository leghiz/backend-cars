<?php

namespace App\Service;

use OpenAPI\Server\Api\ReviewsApiInterface;
use OpenAPI\Server\Model\Review as OpenApiReview;
use OpenAPI\Server\Model\ReviewsPostRequest;
use App\Entity\Review as DbReview;
use App\Entity\Lot;
use App\Entity\User;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class ReviewsApiService implements ReviewsApiInterface
{
    private string $bearerToken = '';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ReviewRepository $reviewRepository,
        private readonly Security $security,
        private readonly RequestStack $requestStack
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

        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $dateOrder = $request->query->get('date_order', $dateOrder);
            $ratingOrder = $request->query->get('rating_order', $ratingOrder);
        }

        $dbReviews = $this->reviewRepository->findPaginatedAndSorted($page, $limit, $dateOrder, $ratingOrder);

        $apiReviews = [];

        foreach ($dbReviews as $review) {
            $user = $review->getAccount();
            $profile = $user ? $user->getProfile() : null;
            $lot = $review->getLot();
            $modification = $lot?->getModification();
            $model = $modification?->getModel();
            $manufacturer = $model?->getManufacturer();
            $year = $modification ? $modification->getProductionYear() : null;

            $apiReviews[] = new OpenApiReview([
                'id' => $review->getId(),
                'lotId' => $lot ? $lot->getId() : null,
                'manufacturer' => $manufacturer ? $manufacturer->getName() : 'Не указан',
                'model' => $model ? $model->getName() : 'Не указан',
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
            $responseCode = 404;
            return;
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