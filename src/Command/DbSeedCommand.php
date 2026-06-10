<?php

namespace App\Command;

use App\Entity\{Manufacturer, CarModel, Color, EngineVolume, Role};
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:db-seed',
    description: 'Заполнение базы',
)]
class DbSeedCommand extends Command
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
        $io = new SymfonyStyle($input, $output);
        $io->title('Запуск процесса наполнения БД');

        $this->seedStaticData($io);
        $this->seedNhtsaData($io);

        $io->success('База данных успешно заполнена.');
        return Command::SUCCESS;
    }

    private function seedStaticData(SymfonyStyle $io): void
    {
        $io->section('Инициализация справочников');

        $colors = ['Чёрный', 'Белый', 'Серый', 'Серебристый', 'Красный', 'Синий', 'Зеленый', 'Коричневый', 'Желтый', 'Оранжевый', 'Фиолетовый'];
        foreach ($colors as $name) {
            $this->upsert(Color::class, ['name' => $name], ['name' => $name]);
        }

        foreach (['ROLE_USER', 'ROLE_ADMIN'] as $roleName) {
            $this->upsert(Role::class, ['name' => $roleName], ['name' => $roleName]);
        }

        $volumes = [1.0, 1.2, 1.4, 1.6, 1.8, 2.0, 2.5, 3.0, 3.5, 4.0, 5.0];
        foreach ($volumes as $val) {
            $this->upsert(EngineVolume::class, ['volume' => $val], ['volume' => $val]);
        }

        $this->entityManager->flush();
    }

    private function seedNhtsaData(SymfonyStyle $io): void
    {
        $io->section('Загрузка автопроизводителей');

        try {
            $response = $this->httpClient->request('GET', self::MAKES_URL)->toArray();
            $makes = $response['Results'] ?? [];
        } catch (\Exception $e) {
            return;
        }

        $io->progressStart(count($makes));

        foreach ($makes as $make) {
            $brandName = mb_substr(trim($make['MakeName']), 0, 50);

            $manufacturer = $this->entityManager->getRepository(Manufacturer::class)
                ->findOneBy(['name' => $brandName]) ?? new Manufacturer();

            $manufacturer->setName($brandName);
            $this->entityManager->persist($manufacturer);
            $this->entityManager->flush();

            $this->loadModelsForMake($manufacturer, $make['MakeName']);

            $io->progressAdvance();
            usleep(50000);
        }

        $io->progressFinish();
        $this->entityManager->clear();
    }

    private function loadModelsForMake(Manufacturer $manufacturer, string $makeName): void
    {
        try {
            $url = sprintf(self::MODELS_URL, rawurlencode($makeName));
            $data = $this->httpClient->request('GET', $url)->toArray();

            foreach ($data['Results'] ?? [] as $item) {
                $modelName = mb_substr(trim($item['Model_Name']), 0, 100);

                $exists = $this->entityManager->getRepository(CarModel::class)->findOneBy([
                    'name' => $modelName,
                    'manufacturer' => $manufacturer
                ]);

                if (!$exists) {
                    $model = new CarModel();
                    $model->setName($modelName);
                    $model->setManufacturer($manufacturer);
                    $this->entityManager->persist($model);
                }
            }
            $this->entityManager->flush();
            $this->entityManager->clear();
        } catch (\Exception) {
        }
    }

    private function upsert(string $entityClass, array $criteria, array $data): void
    {
        $repo = $this->entityManager->getRepository($entityClass);
        if (!$repo->findOneBy($criteria)) {
            $entity = new $entityClass();
            foreach ($data as $key => $value) {
                $setter = 'set' . ucfirst($key);
                $entity->$setter($value);
            }
            $this->entityManager->persist($entity);
        }
    }
}