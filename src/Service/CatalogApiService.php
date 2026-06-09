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
use OpenAPI\Server\Model\LotDetailReview;
use App\Entity\Lot;
use App\Entity\Background;
use App\Entity\CarMedia;
use App\Entity\Review;
use App\Repository\LotRepository;
use App\Repository\ManufacturerRepository;
use App\Repository\CarModelRepository;
use App\Repository\ColorRepository;
use App\Repository\EngineVolumeRepository;
use App\Repository\ModificationRepository;
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
        private readonly RequestStack $requestStack,
        private readonly LotRepository $lotRepository,
        private readonly ManufacturerRepository $manufacturerRepository,
        private readonly CarModelRepository $carModelRepository,
        private readonly ColorRepository $colorRepository,
        private readonly EngineVolumeRepository $engineVolumeRepository,
        private readonly ModificationRepository $modificationRepository
    ) {}

    public function setbearerAuth(?string $value): void
    {
        $this->bearerToken = $value ?? '';
    }

    private function formatImagePath(string $path): string
    {
        return $path;
    }

    public function catalogGet(
        int $page,
        int $limit,
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

            $lots = $this->lotRepository->findByFilters(
                $page, $limit, $search, $manufacturerId, $modelId, $colorId,
                $transmission, $drive, $year, $priceFrom, $priceTo,
                $mileageFrom, $mileageTo, $engineVolumeId, $isSold
            );

            $apiLots = [];
            foreach ($lots as $lot) {
                $modification = $lot->getModification();
                $model = $modification?->getModel();
                $manufacturer = $model?->getManufacturer();
                $engineVolumeEntity = $modification?->getEngineVolume();

                $mediaList = $this->entityManager->getRepository(CarMedia::class)->findBy(['lot' => $lot]);
                $images = array_map(fn($media) => $this->formatImagePath($media->getFilePath()), $mediaList);

                if (empty($images)) {
                    $images = [$this->formatImagePath('/DefaultImage.png')];
                }

                $productionDate = $modification?->getProductionYear();
                $lotYear = $productionDate instanceof \DateTimeInterface
                    ? (int)$productionDate->format('Y')
                    : (int)date('Y');

                $apiLots[] = new LotListItem([
                    'id' => $lot->getId(),
                    'manufacturer' => $manufacturer ? $manufacturer->getName() : 'Не указан',
                    'model' => $model ? $model->getName() : 'Не указан',
                    'price' => (float)($lot->getPrice() ?? 0.0),
                    'year' => $lotYear,
                    'engineVolume' => $engineVolumeEntity ? (float)$engineVolumeEntity->getVolume() : 0.0,
                    'images' => $images,
                    'isSold' => $lot->isSold()
                ]);
            }

            $responseCode = 200;
            return $apiLots;
        } catch (\Throwable $e) {
            error_log("catalogGet Error: " . $e->getMessage());
            $responseCode = 500;
            return null;
        }
    }

    public function catalogFiltersGet(int &$responseCode, array &$responseHeaders): array|object|null
    {
        $manufacturers = $this->manufacturerRepository->findAll();
        $models = $this->carModelRepository->findAll();
        $colors = $this->colorRepository->findAll();
        $volumes = $this->engineVolumeRepository->findAll();

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
            'priceRange' => new PriceRange(['min' => 100000.0, 'max' => 15000000.0]),
            'mileageRange' => new MileageRange(['min' => 0, 'max' => 500000])
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
        ?bool $isSold,
        ?\DateTime $soldData,
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

                $isSoldRaw = $request->request->get('is_sold', $request->request->get('isSold'));
                if ($isSoldRaw !== null) {
                    $isSold = filter_var($isSoldRaw, FILTER_VALIDATE_BOOLEAN);
                }
            }

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

            $manufacturerEntity = $this->manufacturerRepository->findByIdOrName($manufacturerInput);
            if (!$manufacturerEntity) {
                $responseCode = 400;
                return null;
            }

            $modelInput = trim($model ?? '');
            if ($modelInput === '') {
                $responseCode = 400;
                return null;
            }

            $modelEntity = $this->carModelRepository->findByIdOrNameAndManufacturer($modelInput, $manufacturerEntity);
            if (!$modelEntity) {
                $responseCode = 400;
                return null;
            }

            $colorInput = trim($color ?? '');
            if ($colorInput === '') {
                $responseCode = 400;
                return null;
            }

            $colorEntity = $this->colorRepository->findByIdOrName($colorInput);
            if (!$colorEntity) {
                $responseCode = 400;
                return null;
            }

            if ($engineVolume === null) {
                $responseCode = 400;
                return null;
            }

            $volumeEntity = $this->engineVolumeRepository->findByVolumeValue((float)$engineVolume);
            if (!$volumeEntity) {
                $responseCode = 400;
                return null;
            }

            $yearInput = $year ? (int)$year : (int)date('Y');
            $productionYearDate = \DateTime::createFromFormat('Y-m-d', $yearInput . '-01-01') ?: new \DateTime($yearInput . '-01-01');

            $modification = $this->modificationRepository->findOrCreate(
                $modelEntity,
                $volumeEntity,
                $productionYearDate,
                $transmission ?? 'Автомат',
                $drive ?? 'Передний'
            );

            $lot = new Lot();
            $lot->setModification($modification);
            $lot->setPrice((string)($price ?? '0.00'));
            $lot->setBodyNumber((string)($bodyNumber ?? 'WBA0000000'));
            $lot->setArrivalDate(new \DateTime());
            $lot->setCreatedAt(new \DateTimeImmutable());
            $lot->setIsSold($isSold ?? false);
            $lot->setSoldDate($isSold ? ($soldData ?? new \DateTime()) : null);

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

                $filesArray = is_array($images) ? $images : [$images];
                foreach ($filesArray as $file) {
                    if ($file instanceof UploadedFile && $file->isValid()) {
                        $newFilename = uniqid() . '.' . $file->guessExtension();
                        try {
                            $file->move($uploadDir, $newFilename);
                            $filePath = '/uploads/lots/' . $newFilename;

                            $carMedia = new CarMedia();
                            $carMedia->setLot($lot);
                            $carMedia->setFilePath($filePath);

                            $this->entityManager->persist($carMedia);
                            $savedImagesPaths[] = $this->formatImagePath($filePath);
                        } catch (\Exception $e) {
                            error_log("catalogPost File Save Error: " . $e->getMessage());
                        }
                    }
                }
            }

            if (empty($savedImagesPaths)) {
                $savedImagesPaths = [$this->formatImagePath('/DefaultImage.png')];
            }

            try {
                $connection = $this->entityManager->getConnection();
                $connection->executeStatement("SELECT setval('modification_id_seq', COALESCE((SELECT MAX(id) FROM modification), 0) + 1, false);");
                $connection->executeStatement("SELECT setval('lot_id_seq', COALESCE((SELECT MAX(id) FROM lot), 0) + 1, false);");
                $connection->executeStatement("SELECT setval('background_id_seq', COALESCE((SELECT MAX(id) FROM background), 0) + 1, false);");
                $connection->executeStatement("SELECT setval('car_media_id_seq', COALESCE((SELECT MAX(id) FROM car_media), 0) + 1, false);");
            } catch (\Throwable $seqEx) {}

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
                'isSold' => $lot->isSold(),
                'soldDate' => $lot->getSoldDate(),
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
        $lot = $this->lotRepository->find($id);
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
        $images = array_map(fn($media) => $this->formatImagePath($media->getFilePath()), $mediaList);

        if (empty($images)) {
            $images = [$this->formatImagePath('/DefaultImage.png')];
        }

        $reviewEntity = $this->entityManager->getRepository(Review::class)->findOneBy(['lot' => $lot]);
        $apiReview = null;

        if ($reviewEntity) {
            $author = $reviewEntity->getAccount()->getProfile();
            $dbCreatedAt = method_exists($reviewEntity, 'getCreatedAt') ? $reviewEntity->getCreatedAt() : null;
            $createdAt = null;

            if ($dbCreatedAt instanceof \DateTimeImmutable) {
                $createdAt = \DateTime::createFromImmutable($dbCreatedAt);
            } elseif ($dbCreatedAt instanceof \DateTime) {
                $createdAt = $dbCreatedAt;
            }

            if (!$createdAt) {
                $createdAt = new \DateTime();
            }

            $apiReview = new LotDetailReview([
                'id' => $reviewEntity->getId(),
                'lotId' => $lot->getId(),
                'manufacturer' => $manufacturer ? $manufacturer->getName() : 'Не указан',
                'model' => $model ? $model->getName() : 'Не указан',
                'firstName' => $author ? $author->getFirstName() : 'Гость',
                'lastName' => $author ? $author->getLastName() : '',
                'avatarUrl' => $author ? $author->getAvatarUrl() : null,
                'rating' => method_exists($reviewEntity, 'getRating') ? (int)$reviewEntity->getRating() : 5,
                'comment' => method_exists($reviewEntity, 'getComment') ? $reviewEntity->getComment() : '',
                'createdAt' => $createdAt,
            ]);
        }

        $productionDate = $modification?->getProductionYear();
        $lotYear = $productionDate instanceof \DateTimeInterface ? (int)$productionDate->format('Y') : (int)date('Y');
        $responseCode = 200;

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
            'isSold' => $lot->isSold(),
            'soldDate' => $lot->getSoldDate(),
            'review' => $apiReview,
            'images' => $images
        ]);
    }

    public function catalogIdDelete(int $id, int &$responseCode, array &$responseHeaders): void
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            $responseCode = 403;
            return;
        }

        $lot = $this->lotRepository->find($id);
        if (!$lot) {
            $responseCode = 404;
            return;
        }

        $mediaList = $this->entityManager->getRepository(CarMedia::class)->findBy(['lot' => $lot]);
        $projectDir = $this->parameterBag->get('kernel.project_dir');

        foreach ($mediaList as $media) {
            $physicalPath = $projectDir . '/public' . $media->getFilePath();
            if (file_exists($physicalPath) && is_file($physicalPath)) {
                unlink($physicalPath);
            }
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
        ?bool $isSold,
        ?\DateTime $soldData,
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

            $isSoldRaw = $request->request->get('is_sold', $request->request->get('isSold'));
            if ($isSoldRaw !== null) {
                $isSold = filter_var($isSoldRaw, FILTER_VALIDATE_BOOLEAN);
            }
        }

        $lot = $this->lotRepository->find($id);
        if (!$lot) {
            $responseCode = 404;
            return null;
        }

        $modification = $lot->getModification();
        $background = $this->entityManager->getRepository(Background::class)->findOneBy(['lot' => $lot]);

        if ($price !== null) $lot->setPrice((string)$price);
        if ($bodyNumber !== null) $lot->setBodyNumber($bodyNumber);
        if ($isSold !== null) {
            $lot->setIsSold($isSold);
            $lot->setSoldDate($isSold ? ($soldData ?? ($lot->getSoldDate() ?? new \DateTime())) : null);
        }

        if ($background) {
            if ($mileage !== null) $background->setMileage($mileage);
            if ($color !== null) {
                $colorEntity = $this->colorRepository->findByIdOrName($color);
                if ($colorEntity) $background->setColor($colorEntity);
            }
        }

        if ($modification) {
            $currentModel = $modification->getModel();
            $currentVolume = $modification->getEngineVolume();
            $lotYear = (int)$modification->getProductionYear()->format('Y');
            $newYear = $year ?? $lotYear;
            $newTransmission = $transmission ?? $modification->getTransmission();
            $newDrive = $drive ?? $modification->getDrive();

            $newModelEntity = $currentModel;
            if ($model !== null) {
                $mEntity = ($manufacturer !== null) ? $this->manufacturerRepository->findOneBy(['name' => $manufacturer]) : $currentModel?->getManufacturer();
                if ($mEntity) $newModelEntity = $this->carModelRepository->findOneBy(['name' => $model, 'manufacturer' => $mEntity]);
            }
            $newVolumeEntity = ($engineVolume !== null) ? ($this->engineVolumeRepository->findByVolumeValue((float)$engineVolume) ?: $currentVolume) : $currentVolume;
            $productionYearDate = \DateTime::createFromFormat('Y-m-d', $newYear . '-01-01');

            if ($newModelEntity !== $currentModel || $newVolumeEntity !== $currentVolume || $newYear !== $lotYear || $newTransmission !== $modification->getTransmission() || $newDrive !== $modification->getDrive()) {
                $modification = $this->modificationRepository->findOrCreate($newModelEntity, $newVolumeEntity, $productionYearDate, $newTransmission, $newDrive);
                $lot->setModification($modification);
            }
        }

        if (!empty($deletedImages)) {
            $projectDir = $this->parameterBag->get('kernel.project_dir');
            foreach ($deletedImages as $imgUrl) {
                $relativePath = parse_url($imgUrl, PHP_URL_PATH);
                $media = $this->entityManager->getRepository(CarMedia::class)->findOneBy(['lot' => $lot, 'file_path' => $relativePath]);
                if ($media) {
                    if (file_exists($projectDir . '/public' . $media->getFilePath())) unlink($projectDir . '/public' . $media->getFilePath());
                    $this->entityManager->remove($media);
                }
            }
        }

        if (!empty($newImages)) {
            $uploadDir = $this->parameterBag->get('kernel.project_dir') . '/public/uploads/lots';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);
            foreach ((is_array($newImages) ? $newImages : [$newImages]) as $file) {
                if ($file instanceof UploadedFile && $file->isValid()) {
                    $newFilename = uniqid() . '.' . $file->guessExtension();
                    $file->move($uploadDir, $newFilename);
                    $carMedia = new CarMedia();
                    $carMedia->setLot($lot);
                    $carMedia->setFilePath('/uploads/lots/' . $newFilename);
                    $this->entityManager->persist($carMedia);
                }
            }
        }

        $this->entityManager->flush();

        $mediaList = $this->entityManager->getRepository(CarMedia::class)->findBy(['lot' => $lot]);
        $imagesList = array_map(fn($m) => $this->formatImagePath($m->getFilePath()), $mediaList);
        if (empty($imagesList)) $imagesList = [$this->formatImagePath('/DefaultImage.png')];

        $responseCode = 200;
        return new LotDetail([
            'id' => $lot->getId(),
            'manufacturer' => $modification->getModel()->getManufacturer()->getName(),
            'model' => $modification->getModel()->getName(),
            'year' => (int)$modification->getProductionYear()->format('Y'),
            'price' => (float)$lot->getPrice(),
            'mileage' => $background ? $background->getMileage() : 0,
            'engineVolume' => (float)$modification->getEngineVolume()->getVolume(),
            'color' => $background->getColor()->getName(),
            'transmission' => $modification->getTransmission(),
            'drive' => $modification->getDrive(),
            'bodyNumber' => $lot->getBodyNumber(),
            'isSold' => $lot->isSold(),
            'soldDate' => $lot->getSoldDate(),
            'images' => $imagesList
        ]);
    }
}