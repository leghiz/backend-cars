<?php

namespace App\Command;

use App\Entity\Manufacturer;
use App\Entity\CarModel;
use App\Entity\Color;
use App\Entity\EngineVolume;
use App\Entity\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:import-nhtsa',
    description: 'куку',
)]
class ImportNhtsaCommand extends Command
{
    private const MAKES_URL = 'https://vpic.nhtsa.dot.gov/api/vehicles/GetMakesForVehicleType/car?format=json';
    private const MODELS_URL = 'https://vpic.nhtsa.dot.gov/api/vehicles/GetModelsForMake/%s?format=json';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly HttpClientInterface $httpClient
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        set_time_limit(0);

        $io = new SymfonyStyle($input, $output);
        $io->title('ПОЛЕТЕЛИ');

        try {
            $connection = $this->entityManager->getConnection();
            $connection->executeStatement("SELECT setval('manufacturer_id_seq', COALESCE((SELECT MAX(id) FROM manufacturer), 1))");
            $connection->executeStatement("SELECT setval('car_model_id_seq', COALESCE((SELECT MAX(id) FROM car_model), 1))");
            $connection->executeStatement("SELECT setval('color_id_seq', COALESCE((SELECT MAX(id) FROM color), 1))");
            $connection->executeStatement("SELECT setval('engine_volume_id_seq', COALESCE((SELECT MAX(id) FROM engine_volume), 1))");
            $connection->executeStatement("SELECT setval('role_id_seq', COALESCE((SELECT MAX(id) FROM role), 1))");
        } catch (\Exception $e) {
        }

        $colors = ['Чёрный', 'Белый', 'Серый', 'Серебристый', 'Красный', 'Синий', 'Зеленый', 'Коричневый'];
        foreach ($colors as $colorName) {
            $existingColor = $this->entityManager->getRepository(Color::class)->findOneBy(['name' => $colorName]);
            if (!$existingColor) {
                $color = new Color();
                $color->setName($colorName);
                $this->entityManager->persist($color);
            }
        }
        $this->entityManager->flush();
        $io->success('цвета есть');

        $roleNames = ['ROLE_USER', 'ROLE_ADMIN'];
        foreach ($roleNames as $roleName) {
            $existingRole = $this->entityManager->getRepository(Role::class)->findOneBy(['name' => $roleName]);
            if (!$existingRole) {
                $role = new Role();
                $role->setName($roleName);
                $this->entityManager->persist($role);
            }
        }
        $this->entityManager->flush();
        $io->success('Роли ROLE_USER и ROLE_ADMIN инициализированы.');

        $volumes = [1.0, 1.2, 1.4, 1.5, 1.6, 1.8, 2.0, 2.2, 2.4, 2.5, 3.0, 3.5, 4.0, 4.4, 5.0];
        foreach ($volumes as $volumeVal) {
            $volumeStr = number_format($volumeVal, 1, '.', '');
            $existingVolume = $this->entityManager->getRepository(EngineVolume::class)->findOneBy(['volume' => $volumeStr]);
            if (!$existingVolume) {
                $volume = new EngineVolume();
                $volume->setVolume((float)$volumeStr);
                $this->entityManager->persist($volume);
            }
        }
        $this->entityManager->flush();
        $io->success('объем ест');


        try {
            $io->section('погнали');
            $response = $this->httpClient->request('GET', self::MAKES_URL);
            $data = $response->toArray();
        } catch (\Exception $e) {
            return Command::FAILURE;
        }

        $makes = $data['Results'] ?? [];
        if (empty($makes)) {
            return Command::SUCCESS;
        }

        $totalMakes = count($makes);
        $io->progressStart($totalMakes);

        foreach ($makes as $make) {
            $brandName = $this->formatCarName($make['MakeName']);
            if (empty($brandName)) {
                $io->progressAdvance();
                continue;
            }

            $brandName = mb_substr($brandName, 0, 50, "UTF-8");

            $manufacturer = $this->entityManager->getRepository(Manufacturer::class)->findOneBy(['name' => $brandName]);
            if (!$manufacturer) {
                $manufacturer = new Manufacturer();
                $manufacturer->setName($brandName);
                $this->entityManager->persist($manufacturer);
                $this->entityManager->flush();
            }

            try {
                $encodedBrand = rawurlencode(strtolower($make['MakeName']));
                $modelsResponse = $this->httpClient->request('GET', sprintf(self::MODELS_URL, $encodedBrand));
                $modelsData = $modelsResponse->toArray();
                $modelsList = $modelsData['Results'] ?? [];

                foreach ($modelsList as $model) {
                    $modelName = $this->formatCarName($model['Model_Name']);
                    if (empty($modelName)) {
                        continue;
                    }

                    $modelName = mb_substr($modelName, 0, 100, "UTF-8");

                    $existingModel = $this->entityManager->getRepository(CarModel::class)->findOneBy([
                        'name' => $modelName,
                        'manufacturer' => $manufacturer
                    ]);

                    if (!$existingModel) {
                        $carModel = new CarModel();
                        $carModel->setName($modelName);
                        $carModel->setManufacturer($manufacturer);
                        $this->entityManager->persist($carModel);
                    }
                }

                $this->entityManager->flush();
                $this->entityManager->clear();

            } catch (\Exception $e) {
                $this->entityManager->clear();
            }

            $io->progressAdvance();
            usleep(50000);
        }

        $io->progressFinish();
        $io->success('заполнились');

        return Command::SUCCESS;
    }

    private function formatCarName(string $name): string
    {
        $trimmed = trim($name);

        if (empty($trimmed)) {
            return '';
        }

        if (strlen($trimmed) <= 3) {
            return strtoupper($trimmed);
        }

        return mb_convert_case($trimmed, MB_CASE_TITLE, "UTF-8");
    }
}
