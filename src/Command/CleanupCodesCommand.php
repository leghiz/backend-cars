<?php

namespace App\Command;

use App\Repository\VerificationCodeRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:cleanup-codes',
    description: 'удаление просроченных кодов верификации',
)]
class CleanupCodesCommand extends Command
{
    public function __construct(
        private VerificationCodeRepository $verificationCodeRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $deletedRows = $this->verificationCodeRepository->deleteExpiredCodes();

        $io->success(sprintf('устаревшие коды верификации удалены'));

        return Command::SUCCESS;
    }
}