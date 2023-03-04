<?php

namespace App\Command;

use App\Entity\Resource;
use App\Service\ResourceService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'generate-resource-stats',
    description: 'Generate resource stats',
)]
class GenerateResourceStatsCommand extends Command
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ResourceService        $resourceService,
    )
    {
        parent::__construct();
    }
    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $resources = $this->entityManager->getRepository(Resource::class)->findAll();

        foreach ($resources as $resource) {
            $this->resourceService->generateStats($resource);
        }

        return Command::SUCCESS;
    }
}
