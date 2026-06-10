<?php

namespace App\Command;

use App\Repository\VerificationCodeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:cleanup-codes',
    description: 'Удаление просроченных кодов верификации и рефреш-токенов',
)]
class CleanupCodesCommand extends Command
{
    public function __construct(
        private VerificationCodeRepository $verificationCodeRepository,
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->verificationCodeRepository->deleteExpiredCodes();
        $deletedTokensCount = $this->entityManager->createQuery(
            'DELETE FROM App\Entity\RefreshToken rt WHERE rt.valid < :now'
        )
            ->setParameter('now', new \DateTime())
            ->execute();

        $io->success(sprintf('Устаревшие коды верификации и просроченные рефреш-токены успешно удалены.'));

        return Command::SUCCESS;
    }
}