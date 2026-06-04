<?php

namespace App\Service;

use OpenAPI\Server\Api\CatalogApiInterface;
use OpenAPI\Server\Model\FilterOptions;
use OpenAPI\Server\Model\PriceRange;
use OpenAPI\Server\Model\MileageRange;
use OpenAPI\Server\Model\Manufacturer as OpenApiManufacturer;
use OpenAPI\Server\Model\CarModel as OpenApiCarModel;
use OpenAPI\Server\Model\Color as OpenApiColor;
use OpenAPI\Server\Model\EngineVolume as OpenApiEngineVolume;
use OpenAPI\Server\Model\LotListItem;
use OpenAPI\Server\Model\LotDetail;
use App\Entity\Lot;
use App\Entity\Manufacturer;
use App\Entity\CarModel;
use App\Entity\Color;
use App\Entity\EngineVolume;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class CatalogApiService implements CatalogApiInterface
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
    public function getCatalogFilters(int &$responseCode, array &$responseHeaders): array|object|null
    {
        $manufacturers = $this->entityManager->getRepository(Manufacturer::class)->findAll();
        $models = $this->entityManager->getRepository(CarModel::class)->findAll();
        $colors = $this->entityManager->getRepository(Color::class)->findAll();
        $volumes = $this->entityManager->getRepository(EngineVolume::class)->findAll();

        $apiManufacturers = array_map(fn($m) => new OpenApiManufacturer([
            'id' => $m->getId(),
            'name' => $m->getName()
        ]), $manufacturers);

        $apiModels = array_map(fn($m) => new OpenApiCarModel([
            'id' => $m->getId(),
            'name' => $m->getName(),
            'manufacturerId' => $m->getManufacturer() ? $m->getManufacturer()->getId() : null
        ]), $models);

        $apiColors = array_map(fn($c) => new OpenApiColor([
            'id' => $c->getId(),
            'name' => $c->getName()
        ]), $colors);

        $apiVolumes = array_map(fn($v) => new OpenApiEngineVolume([
            'id' => $v->getId(),
            'volume' => (float)$v->getVolume()
        ]), $volumes);

        $responseCode = 200;

        return new FilterOptions([
            'manufacturers' => $apiManufacturers,
            'carModels' => $apiModels,
            'colors' => $apiColors,
            'engineVolumes' => $apiVolumes,
            'transmissions' => ["Автомат", "Механика", "Робот", "Вариатор"],
            'driveTypes' => ["Полный", "Передний", "Задний"],
            'years' => range(2000, (int)date('Y')),
            'priceRange' => new PriceRange([
                'min' => 100000.0,
                'max' => 15000000.0
            ]),
            'mileageRange' => new MileageRange([
                'min' => 0,
                'max' => 500000
            ])
        ]);
    }
    public function getCatalog(
        int $page = 1,
        int $limit = 10,
        ?string $search = null,
        ?int $manufacturerId = null,
        ?int $modelId = null,
        ?int $colorId = null,
        ?string $transmission = null,
        ?string $drive = null,
        ?int $year = null,
        ?float $priceFrom = null,
        ?float $priceTo = null,
        ?int $mileageFrom = null,
        ?int $mileageTo = null,
        ?int $engineVolumeId = null,
        ?bool $isSold = null,
        int &$responseCode = 0,
        array &$responseHeaders = []
    ): array|object|null {
        $qb = $this->entityManager->getRepository(Lot::class)->createQueryBuilder('l');

        if ($priceFrom) {
            $qb->andWhere('l.price >= :priceFrom')->setParameter('priceFrom', $priceFrom);
        }
        if ($priceTo) {
            $qb->andWhere('l.price <= :priceTo')->setParameter('priceTo', $priceTo);
        }
        if ($isSold !== null) {
            if ($isSold) {
                $qb->andWhere('l.sold_date IS NOT NULL');
            } else {
                $qb->andWhere('l.sold_date IS NULL');
            }
        }

        $qb->setFirstResult(($page - 1) * $limit)->setMaxResults($limit);

        $lots = $qb->getQuery()->getResult();
        $apiLots = [];

        foreach ($lots as $lot) {
            $apiLots[] = new LotListItem([
                'id' => $lot->getId(),
                'manufacturer' => 'Toyota', // Заглушка
                'model' => 'Camry', // Заглушка
                'price' => (float)($lot->getPrice() ?? 1500000.0),
                'year' => 2021, // Заглушка
                'engineVolume' => 2.5, // Заглушка
                'images' => [],
                'isSold' => $lot->getSoldDate() !== null
            ]);
        }

        $responseCode = 200;
        return $apiLots;
    }
    public function catalogPost(LotDetail $lotDetail, int &$responseCode, array &$responseHeaders): void
    {
        /** @var User|null $currentUser */
        $currentUser = $this->security->getUser();
        $isAdmin = $currentUser && $currentUser->getRole() && $currentUser->getRole()->getName() === 'Admin';

        if (!$isAdmin) {
            $responseCode = 403;
            return;
        }

        $lot = new Lot();
        $lot->setPrice((string)$lotDetail->getPrice());
        $lot->setBodyNumber($lotDetail->getBodyNumber() ?? 'WBA0000000');
        $lot->setCreatedAt(new \DateTimeImmutable());

        if ($lotDetail->getIsSold()) {
            $lot->setSoldDate(new \DateTime());
        }

        $this->entityManager->persist($lot);
        $this->entityManager->flush();

        $responseCode = 201;
    }
    public function catalogIdGet(int $id, int &$responseCode, array &$responseHeaders): array|object|null
    {
        $lot = $this->entityManager->getRepository(Lot::class)->find($id);

        if (!$lot) {
            $responseCode = 404;
            return null;
        }

        $responseCode = 200;

        return new LotDetail([
            'id' => $lot->getId(),
            'manufacturer' => 'Toyota',
            'model' => 'Camry',
            'year' => 2021,
            'price' => (float)$lot->getPrice(),
            'mileage' => 60000,
            'engineVolume' => 2.5,
            'color' => 'Черный',
            'transmission' => 'Автомат',
            'drive' => 'Передний',
            'bodyNumber' => $lot->getBodyNumber(),
            'isSold' => $lot->getSoldDate() !== null,
            'soldDate' => $lot->getSoldDate() ? $lot->getSoldDate()->format('Y-m-d') : null,
            'images' => []
        ]);
    }
    public function catalogIdDelete(int $id, int &$responseCode, array &$responseHeaders): void
    {
        /** @var User|null $currentUser */
        $currentUser = $this->security->getUser();
        $isAdmin = $currentUser && $currentUser->getRole() && $currentUser->getRole()->getName() === 'Admin';

        if (!$isAdmin) {
            $responseCode = 403;
            return;
        }

        $lot = $this->entityManager->getRepository(Lot::class)->find($id);

        if (!$lot) {
            $responseCode = 404;
            return;
        }

        $this->entityManager->remove($lot);
        $this->entityManager->flush();

        $responseCode = 204;
    }
    public function catalogIdPatch(int $id, LotDetail $lotDetail, int &$responseCode, array &$responseHeaders): void
    {
        /** @var User|null $currentUser */
        $currentUser = $this->security->getUser();
        $isAdmin = $currentUser && $currentUser->getRole() && $currentUser->getRole()->getName() === 'Admin';

        if (!$isAdmin) {
            $responseCode = 403;
            return;
        }

        $lot = $this->entityManager->getRepository(Lot::class)->find($id);

        if (!$lot) {
            $responseCode = 404;
            return;
        }

        if ($lotDetail->getPrice() !== null) {
            $lot->setPrice((string)$lotDetail->getPrice());
        }
        if ($lotDetail->getIsSold() !== null) {
            $lot->setSoldDate($lotDetail->getIsSold() ? new \DateTime() : null);
        }

        $this->entityManager->flush();

        $responseCode = 200;
    }
}
