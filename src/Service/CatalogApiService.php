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
use App\Entity\Modification;
use App\Entity\Background;
use App\Entity\CarMedia;
use App\Entity\Manufacturer;
use App\Entity\CarModel;
use App\Entity\Color;
use App\Entity\EngineVolume;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CatalogApiService implements CatalogApiInterface
{
    private string $bearerToken = '';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
        private readonly ParameterBagInterface $parameterBag,
        private readonly RequestStack $requestStack
    ) {}

    public function setbearerAuth(?string $value): void
    {
        $this->bearerToken = $value ?? '';
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
        try {
            $request = $this->requestStack->getCurrentRequest();
            if ($request) {
                $manufacturerId = $manufacturerId ?? ($request->query->get('manufacturer_id') !== null ? (int)$request->query->get('manufacturer_id') : null);
                $modelId = $modelId ?? ($request->query->get('model_id') !== null ? (int)$request->query->get('model_id') : null);
                $colorId = $colorId ?? ($request->query->get('color_id') !== null ? (int)$request->query->get('color_id') : null);
                $priceFrom = $priceFrom ?? ($request->query->get('price_from') !== null ? (float)$request->query->get('price_from') : null);
                $priceTo = $priceTo ?? ($request->query->get('price_to') !== null ? (float)$request->query->get('price_to') : null);
                $mileageFrom = $mileageFrom ?? ($request->query->get('mileage_from') !== null ? (int)$request->query->get('mileage_from') : null);
                $mileageTo = $mileageTo ?? ($request->query->get('mileage_to') !== null ? (int)$request->query->get('mileage_to') : null);
                $engineVolumeId = $engineVolumeId ?? ($request->query->get('engine_volume_id') !== null ? (int)$request->query->get('engine_volume_id') : null);
                $isSold = $isSold ?? ($request->query->get('is_sold') !== null ? filter_var($request->query->get('is_sold'), FILTER_VALIDATE_BOOLEAN) : null);
            }

            $qb = $this->entityManager->getRepository(Lot::class)->createQueryBuilder('l');
            $qb->leftJoin('l.modification', 'm')
                ->leftJoin('m.model', 'cm')
                ->leftJoin('cm.manufacturer', 'man')
                ->leftJoin('m.engine_volume', 'ev');

            if ($priceFrom !== null) {
                $qb->andWhere('l.price >= :priceFrom')->setParameter('priceFrom', $priceFrom);
            }
            if ($priceTo !== null) {
                $qb->andWhere('l.price <= :priceTo')->setParameter('priceTo', $priceTo);
            }

            if ($isSold !== null) {
                if ($isSold) {
                    $qb->andWhere('l.sold_date IS NOT NULL');
                } else {
                    $qb->andWhere('l.sold_date IS NULL');
                }
            }

            if ($manufacturerId !== null) {
                $qb->andWhere('man.id = :manufacturerId')->setParameter('manufacturerId', $manufacturerId);
            }
            if ($modelId !== null) {
                $qb->andWhere('cm.id = :modelId')->setParameter('modelId', $modelId);
            }
            if ($colorId !== null) {
                $qb->leftJoin('l.background', 'b')
                    ->leftJoin('b.color', 'col')
                    ->andWhere('col.id = :colorId')->setParameter('colorId', $colorId);
            }
            if ($engineVolumeId !== null) {
                $qb->andWhere('ev.id = :engineVolumeId')->setParameter('engineVolumeId', $engineVolumeId);
            }
            if ($transmission !== null) {
                $qb->andWhere('m.transmission = :transmission')->setParameter('transmission', $transmission);
            }
            if ($drive !== null) {
                $qb->andWhere('m.drive = :drive')->setParameter('drive', $drive);
            }

            $qb->setFirstResult(($page - 1) * $limit)->setMaxResults($limit);

            $lots = $qb->getQuery()->getResult();
            $apiLots = [];

            foreach ($lots as $lot) {
                $modification = $lot->getModification();
                $model = $modification?->getModel();
                $manufacturer = $model?->getManufacturer();
                $engineVolumeEntity = $modification?->getEngineVolume();

                $firstMedia = $this->entityManager->getRepository(CarMedia::class)->findOneBy(['lot' => $lot]);
                $images = $firstMedia ? [$firstMedia->getFilePath()] : ['/DefaultImage.png'];

                $lotYear = 2020;
                if ($modification && $modification->getProductionYear() instanceof \DateTimeInterface) {
                    $lotYear = (int)$modification->getProductionYear()->format('Y');
                }

                $apiLots[] = new LotListItem([
                    'id' => $lot->getId(),
                    'manufacturer' => $manufacturer ? $manufacturer->getName() : 'Не указан',
                    'model' => $model ? $model->getName() : 'Не указан',
                    'price' => (float)($lot->getPrice() ?? 0.0),
                    'year' => $lotYear,
                    'engineVolume' => $engineVolumeEntity ? (float)$engineVolumeEntity->getVolume() : 0.0,
                    'images' => $images,
                    'isSold' => $lot->getSoldDate() !== null
                ]);
            }

            $responseCode = 200;
            return $apiLots;
        } catch (\Throwable $e) {
            error_log("getCatalog Error: " . $e->getMessage());
            $responseCode = 500;
            return null;
        }
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

    public function catalogPost(
        ?string $manufacturer,
        ?string $model,
        ?int $year,
        ?float $price,
        ?int $mileage,
        ?float $engineVolume,
        ?string $color,
        ?string $transmission,
        ?string $drive,
        ?string $bodyNumber,
        ?array $images,
        int &$responseCode,
        array &$responseHeaders
    ): array|object|null {
        try {
            $request = $this->requestStack->getCurrentRequest();
            if ($request) {
                $manufacturer = $manufacturer ?? $request->request->get('manufacturer');
                $model = $model ?? $request->request->get('model');
                $year = $year ?? ($request->request->get('year') !== null ? (int)$request->request->get('year') : null);
                $price = $price ?? ($request->request->get('price') !== null ? (float)$request->request->get('price') : null);
                $mileage = $mileage ?? ($request->request->get('mileage') !== null ? (int)$request->request->get('mileage') : null);

                $engineVolumeVal = $engineVolume ?? $request->request->get('engine_volume');
                $engineVolume = $engineVolumeVal !== null ? (float)$engineVolumeVal : null;

                $color = $color ?? $request->request->get('color');
                $transmission = $transmission ?? $request->request->get('transmission');
                $drive = $drive ?? $request->request->get('drive');

                $bodyNumber = $bodyNumber ?? $request->request->get('body_number');
                $images = $images ?? $request->files->get('images');
            }

            /** @var User|null $currentUser */
            $currentUser = $this->security->getUser();
            if (!$currentUser) {
                $responseCode = 403;
                return null;
            }

            $isAdmin = false;
            if (method_exists($currentUser, 'getRole') && $currentUser->getRole()) {
                $isAdmin = $currentUser->getRole()->getName() === 'ROLE_ADMIN';
            } elseif (method_exists($currentUser, 'getRoles')) {
                $isAdmin = in_array('ROLE_ADMIN', $currentUser->getRoles(), true);
            }

            if (!$isAdmin) {
                $responseCode = 403;
                return null;
            }

            $manufacturerInput = trim($manufacturer ?? '');
            if ($manufacturerInput === '') {
                $responseCode = 400;
                return null;
            }

            if (is_numeric($manufacturerInput)) {
                $manufacturerEntity = $this->entityManager->getRepository(Manufacturer::class)->find((int)$manufacturerInput);
            } else {
                $manufacturerEntity = $this->entityManager->getRepository(Manufacturer::class)
                    ->createQueryBuilder('m')
                    ->where('LOWER(m.name) = LOWER(:name)')
                    ->setParameter('name', $manufacturerInput)
                    ->getQuery()
                    ->getOneOrNullResult();
            }

            if (!$manufacturerEntity) {
                $responseCode = 400;
                return null;
            }

            $modelInput = trim($model ?? '');
            if ($modelInput === '') {
                $responseCode = 400;
                return null;
            }

            if (is_numeric($modelInput)) {
                $modelEntity = $this->entityManager->getRepository(CarModel::class)->find((int)$modelInput);
            } else {
                $modelEntity = $this->entityManager->getRepository(CarModel::class)
                    ->createQueryBuilder('cm')
                    ->where('LOWER(cm.name) = LOWER(:name)')
                    ->andWhere('cm.manufacturer = :manufacturer')
                    ->setParameter('name', $modelInput)
                    ->setParameter('manufacturer', $manufacturerEntity)
                    ->getQuery()
                    ->getOneOrNullResult();
            }

            if (!$modelEntity) {
                $responseCode = 400;
                return null;
            }

            $colorInput = trim($color ?? '');
            if ($colorInput === '') {
                $responseCode = 400;
                return null;
            }

            if (is_numeric($colorInput)) {
                $colorEntity = $this->entityManager->getRepository(Color::class)->find((int)$colorInput);
            } else {
                $colorEntity = $this->entityManager->getRepository(Color::class)
                    ->createQueryBuilder('c')
                    ->where('LOWER(c.name) = LOWER(:name)')
                    ->setParameter('name', $colorInput)
                    ->getQuery()
                    ->getOneOrNullResult();
            }

            if (!$colorEntity) {
                $responseCode = 400;
                return null;
            }

            if ($engineVolume === null) {
                $responseCode = 400;
                return null;
            }

            $volumeFloat = (float)$engineVolume;
            $volumeFormatted = number_format($volumeFloat, 1, '.', '');

            $volumeEntity = $this->entityManager->getRepository(EngineVolume::class)->findOneBy(['volume' => $volumeFormatted]);
            if (!$volumeEntity) {
                $volumeEntity = $this->entityManager->getRepository(EngineVolume::class)->findOneBy(['volume' => $volumeFloat]);
            }

            if (!$volumeEntity) {
                $responseCode = 400;
                return null;
            }

            $yearInput = $year ? (int)$year : 2020;
            $productionYearDate = \DateTime::createFromFormat('Y-m-d', $yearInput . '-01-01');
            if (!$productionYearDate) {
                $productionYearDate = new \DateTime($yearInput . '-01-01');
            }

            $modification = $this->entityManager->getRepository(Modification::class)->findOneBy([
                'model' => $modelEntity,
                'engine_volume' => $volumeEntity,
                'production_year' => $productionYearDate
            ]);

            if ($modification) {
                if ($modification->getDrive() !== $drive || $modification->getTransmission() !== $transmission) {
                    $modification = null;
                }
            }

            if (!$modification) {
                $modification = new Modification();
                $modification->setModel($modelEntity);
                $modification->setEngineVolume($volumeEntity);
                $modification->setProductionYear($productionYearDate);
                $modification->setDrive($drive ?? 'Передний');
                $modification->setTransmission($transmission ?? 'Автомат');

                $this->entityManager->persist($modification);
            }

            $lot = new Lot();
            $lot->setModification($modification);
            $lot->setPrice((string)($price ?? '0.00'));
            $lot->setBodyNumber((string)($bodyNumber ?? 'WBA0000000'));
            $lot->setArrivalDate(new \DateTime());
            $lot->setCreatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($lot);

            $background = new Background();
            $background->setLot($lot);
            $background->setMileage((int)($mileage ?? 0));
            $background->setColor($colorEntity);
            $background->setIsRepainted(false);

            $this->entityManager->persist($background);

            $savedImagesPaths = [];
            if (!empty($images)) {
                $uploadDir = $this->parameterBag->get('kernel.project_dir') . '/public/uploads/lots';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0775, true);
                }

                foreach ($images as $file) {
                    if ($file instanceof UploadedFile && $file->isValid()) {
                        $newFilename = uniqid() . '.' . $file->guessExtension();

                        try {
                            $file->move($uploadDir, $newFilename);
                            $filePath = '/uploads/lots/' . $newFilename;

                            $carMedia = new CarMedia();
                            $carMedia->setLot($lot);
                            $carMedia->setFilePath($filePath);

                            $this->entityManager->persist($carMedia);
                            $savedImagesPaths[] = $filePath;
                        } catch (\Exception $e) {
                        }
                    }
                }
            }

            if (empty($savedImagesPaths)) {
                $savedImagesPaths = ['/DefaultImage.png'];
            }

            $connection = $this->entityManager->getConnection();
            try {
                $connection->executeStatement("SELECT setval('modifications_id_seq', COALESCE((SELECT MAX(id) FROM modifications), 0) + 1, false);");
                $connection->executeStatement("SELECT setval('lots_id_seq', COALESCE((SELECT MAX(id) FROM lots), 0) + 1, false);");
                $connection->executeStatement("SELECT setval('background_id_seq', COALESCE((SELECT MAX(id) FROM background), 0) + 1, false);");
                $connection->executeStatement("SELECT setval('car_media_id_seq', COALESCE((SELECT MAX(id) FROM car_media), 0) + 1, false);");
            } catch (\Throwable $seqEx) {
            }

            $this->entityManager->flush();
            $responseCode = 201;

            return new LotDetail([
                'id' => $lot->getId(),
                'manufacturer' => $manufacturerEntity->getName(),
                'model' => $modelEntity->getName(),
                'year' => $yearInput,
                'price' => (float)$lot->getPrice(),
                'mileage' => $background->getMileage(),
                'engineVolume' => (float)$volumeEntity->getVolume(),
                'color' => $colorEntity->getName(),
                'transmission' => $modification->getTransmission(),
                'drive' => $modification->getDrive(),
                'bodyNumber' => $lot->getBodyNumber(),
                'isSold' => false,
                'soldDate' => null,
                'images' => $savedImagesPaths
            ]);

        } catch (\Throwable $e) {
            error_log("catalogPost Exception: " . $e->getMessage());
            $responseCode = 500;
            return null;
        }
    }

    public function catalogIdGet(int $id, int &$responseCode, array &$responseHeaders): array|object|null
    {
        $lot = $this->entityManager->getRepository(Lot::class)->find($id);

        if (!$lot) {
            $responseCode = 404;
            return null;
        }

        $modification = $lot->getModification();
        $model = $modification?->getModel();
        $manufacturer = $model?->getManufacturer();
        $engineVolumeEntity = $modification?->getEngineVolume();
        $background = $this->entityManager->getRepository(Background::class)->findOneBy(['lot' => $lot]);

        $mediaList = $this->entityManager->getRepository(CarMedia::class)->findBy(['lot' => $lot]);
        $images = [];
        foreach ($mediaList as $media) {
            $images[] = $media->getFilePath();
        }

        if (empty($images)) {
            $images = ['/DefaultImage.png'];
        }

        $responseCode = 200;

        $lotYear = 2020;
        if ($modification && $modification->getProductionYear() instanceof \DateTimeInterface) {
            $lotYear = (int)$modification->getProductionYear()->format('Y');
        }

        return new LotDetail([
            'id' => $lot->getId(),
            'manufacturer' => $manufacturer ? $manufacturer->getName() : 'Не указан',
            'model' => $model ? $model->getName() : 'Не указан',
            'year' => $lotYear,
            'price' => (float)$lot->getPrice(),
            'mileage' => $background ? $background->getMileage() : 0,
            'engineVolume' => $engineVolumeEntity ? (float)$engineVolumeEntity->getVolume() : 0.0,
            'color' => $background && $background->getColor() ? $background->getColor()->getName() : 'Не указан',
            'transmission' => $modification ? $modification->getTransmission() : 'Не указана',
            'drive' => $modification ? $modification->getDrive() : 'Не указан',
            'bodyNumber' => $lot->getBodyNumber(),
            'isSold' => $lot->getSoldDate() !== null,
            'soldDate' => $lot->getSoldDate(),
            'images' => $images
        ]);
    }

    public function catalogIdDelete(int $id, int &$responseCode, array &$responseHeaders): void
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
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

    public function catalogIdPost(
        int $id,
        ?string $manufacturer,
        ?string $model,
        ?int $year,
        ?float $price,
        ?int $mileage,
        ?float $engineVolume,
        ?string $color,
        ?string $transmission,
        ?string $drive,
        ?string $bodyNumber,
        ?array $deletedImages,
        ?array $newImages,
        int &$responseCode,
        array &$responseHeaders
    ): array|object|null {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            $responseCode = 403;
            return null;
        }

        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $manufacturer = $manufacturer ?? $request->request->get('manufacturer');
            $model = $model ?? $request->request->get('model');
            $year = $year ?? ($request->request->get('year') !== null ? (int)$request->request->get('year') : null);
            $price = $price ?? ($request->request->get('price') !== null ? (float)$request->request->get('price') : null);
            $mileage = $mileage ?? ($request->request->get('mileage') !== null ? (int)$request->request->get('mileage') : null);

            $engineVolumeVal = $engineVolume ?? $request->request->get('engine_volume');
            $engineVolume = $engineVolumeVal !== null ? (float)$engineVolumeVal : null;

            $color = $color ?? $request->request->get('color');
            $transmission = $transmission ?? $request->request->get('transmission');
            $drive = $drive ?? $request->request->get('drive');

            $bodyNumber = $bodyNumber ?? $request->request->get('body_number');

            $deletedImagesVal = $deletedImages ?? $request->request->get('deleted_images');
            if ($deletedImagesVal !== null) {
                $deletedImages = is_string($deletedImagesVal) ? str_getcsv($deletedImagesVal) : $deletedImagesVal;
            }

            $newImages = $newImages ?? $request->files->get('new_images');
        }

        $lot = $this->entityManager->getRepository(Lot::class)->find($id);

        if (!$lot) {
            $responseCode = 404;
            return null;
        }

        $modification = $lot->getModification();
        $background = $this->entityManager->getRepository(Background::class)->findOneBy(['lot' => $lot]);

        if ($price !== null) {
            $lot->setPrice((string)$price);
        }

        if ($bodyNumber !== null) {
            $lot->setBodyNumber($bodyNumber);
        }

        if ($background) {
            if ($mileage !== null) {
                $background->setMileage($mileage);
            }
            if ($color !== null) {
                $colorEntity = $this->entityManager->getRepository(Color::class)->findOneBy(['name' => $color]);
                if (!$colorEntity && is_numeric($color)) {
                    $colorEntity = $this->entityManager->getRepository(Color::class)->find((int)$color);
                }
                if ($colorEntity) {
                    $background->setColor($colorEntity);
                }
            }
        }

        if ($modification) {
            $currentModel = $modification->getModel();
            $currentVolume = $modification->getEngineVolume();

            $lotYear = 2020;
            if ($modification->getProductionYear() instanceof \DateTimeInterface) {
                $lotYear = (int)$modification->getProductionYear()->format('Y');
            }

            $newYear = $year ?? $lotYear;
            $newTransmission = $transmission ?? $modification->getTransmission();
            $newDrive = $drive ?? $modification->getDrive();

            $newModelEntity = $currentModel;
            if ($model !== null) {
                $mEntity = $currentModel?->getManufacturer();
                if ($manufacturer !== null) {
                    $mEntity = $this->entityManager->getRepository(Manufacturer::class)->findOneBy(['name' => $manufacturer]);
                }
                if ($mEntity) {
                    $newModelEntity = $this->entityManager->getRepository(CarModel::class)->findOneBy([
                        'name' => $model,
                        'manufacturer' => $mEntity
                    ]);
                }
            }

            $newVolumeEntity = $currentVolume;
            if ($engineVolume !== null) {
                $volumeFloat = (float)$engineVolume;
                $volumeFormatted = number_format($volumeFloat, 1, '.', '');
                $newVolumeEntity = $this->entityManager->getRepository(EngineVolume::class)->findOneBy(['volume' => $volumeFormatted]);
                if (!$newVolumeEntity) {
                    $newVolumeEntity = $this->entityManager->getRepository(EngineVolume::class)->findOneBy(['volume' => $volumeFloat]);
                }
            }

            $productionYearDate = \DateTime::createFromFormat('Y-m-d', $newYear . '-01-01');

            if (
                $newModelEntity !== $currentModel ||
                $newVolumeEntity !== $currentVolume ||
                $newYear !== $lotYear ||
                $newTransmission !== $modification->getTransmission() ||
                $newDrive !== $modification->getDrive()
            ) {
                $existingMod = $this->entityManager->getRepository(Modification::class)->findOneBy([
                    'model' => $newModelEntity,
                    'engine_volume' => $newVolumeEntity,
                    'production_year' => $productionYearDate,
                    'transmission' => $newTransmission,
                    'drive' => $newDrive
                ]);

                if (!$existingMod) {
                    $existingMod = new Modification();
                    $existingMod->setModel($newModelEntity);
                    $existingMod->setEngineVolume($newVolumeEntity);
                    $existingMod->setProductionYear($productionYearDate);
                    $existingMod->setTransmission($newTransmission);
                    $existingMod->setDrive($newDrive);
                    $this->entityManager->persist($existingMod);
                }
                $lot->setModification($existingMod);
                $modification = $existingMod;
            }
        }

        if (!empty($deletedImages)) {
            foreach ($deletedImages as $imgUrl) {
                if (empty($imgUrl)) {
                    continue;
                }
                $media = $this->entityManager->getRepository(CarMedia::class)->findOneBy([
                    'lot' => $lot,
                    'filePath' => $imgUrl
                ]);
                if ($media) {
                    $this->entityManager->remove($media);
                }
            }
        }

        if (!empty($newImages)) {
            $uploadDir = $this->parameterBag->get('kernel.project_dir') . '/public/uploads/lots';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            foreach ($newImages as $file) {
                if ($file instanceof UploadedFile && $file->isValid()) {
                    $newFilename = uniqid() . '.' . $file->guessExtension();
                    try {
                        $file->move($uploadDir, $newFilename);
                        $carMedia = new CarMedia();
                        $carMedia->setLot($lot);
                        $carMedia->setFilePath('/uploads/lots/' . $newFilename);
                        $this->entityManager->persist($carMedia);
                    } catch (\Exception $e) {
                    }
                }
            }
        }

        $this->entityManager->flush();

        $mediaList = $this->entityManager->getRepository(CarMedia::class)->findBy(['lot' => $lot]);
        $imagesList = array_map(fn($m) => $m->getFilePath(), $mediaList);
        if (empty($imagesList)) {
            $imagesList = ['/DefaultImage.png'];
        }

        $finalYear = 2020;
        if ($modification && $modification->getProductionYear() instanceof \DateTimeInterface) {
            $finalYear = (int)$modification->getProductionYear()->format('Y');
        }

        $responseCode = 200;

        return new LotDetail([
            'id' => $lot->getId(),
            'manufacturer' => $modification?->getModel()?->getManufacturer() ? $modification->getModel()->getManufacturer()->getName() : 'Не указан',
            'model' => $modification?->getModel() ? $modification->getModel()->getName() : 'Не указан',
            'year' => $finalYear,
            'price' => (float)$lot->getPrice(),
            'mileage' => $background ? $background->getMileage() : 0,
            'engineVolume' => $modification?->getEngineVolume() ? (float)$modification->getEngineVolume()->getVolume() : 0.0,
            'color' => $background && $background->getColor() ? $background->getColor()->getName() : 'Не указан',
            'transmission' => $modification ? $modification->getTransmission() : 'Не указана',
            'drive' => $modification ? $modification->getDrive() : 'Не указан',
            'bodyNumber' => $lot->getBodyNumber(),
            'isSold' => $lot->getSoldDate() !== null,
            'soldDate' => $lot->getSoldDate(),
            'images' => $imagesList
        ]);
    }
}