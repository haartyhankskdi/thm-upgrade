<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Console\Command;

use Amasty\Base\Model\Module\DependencyProvider;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Console\Cli;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\Status;
use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ModuleEnable extends Command
{
    private const ARG_MODULES = 'modules';

    private const FLAG_KEYS = [
        DependencyProvider::FLAG_AMASTY,
        DependencyProvider::FLAG_EXTERNAL,
        DependencyProvider::FLAG_ALL,
        DependencyProvider::FLAG_DEP
    ];

    /**
     * @var ObjectManager
     */
    private $objectManager;

    public function __construct(
        ObjectManagerInterface $objectManager,
        ?string $name = null
    ) {
        parent::__construct($name);
        $this->objectManager = $objectManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $selectedFlag = $this->findFlag($input);
        if (!$selectedFlag) {
            $output->writeln('<error>Please specify one of the flags: ' . implode(', ', self::FLAG_KEYS) . '</error>');
            return Command::FAILURE;
        }

        $modulesArg = $input->getArgument(self::ARG_MODULES);
        $modulesToCheck = $selectedFlag === DependencyProvider::FLAG_DEP
            ? $modulesArg
            : $this->getModuleList()->getNames();
        $filteredValues = $this->getDependencyProvider()
            ->getDependencies($modulesToCheck, $selectedFlag, true);

        $status = $this->getModuleStatus();
        $modulesToChange = $status->getModulesToChange(true, $filteredValues);
        if (!empty($modulesToChange)) {
            $status->setIsEnabled(true, $modulesToChange);
            $output->writeln('<info>The following modules have been enabled:</info>');
            foreach ($modulesToChange as $module) {
                $output->writeln("<info>- $module</info>");
            }
        } else {
            $output->writeln('<info>No modules were changed.</info>');
        }

        return Cli::RETURN_SUCCESS;
    }

    protected function configure(): void
    {
        $this->setName('amasty:module:enable')
            ->setDescription('Enable modules with additional Amasty filters')
            ->addArgument(self::ARG_MODULES, InputArgument::IS_ARRAY, 'Module names to enable')
            ->addOption(
                DependencyProvider::FLAG_AMASTY,
                null,
                InputOption::VALUE_NONE,
                'Enable all Amasty modules (except Base)'
            )
            ->addOption(
                DependencyProvider::FLAG_EXTERNAL,
                null,
                InputOption::VALUE_NONE,
                'Enable all third-party vendor modules'
            )
            ->addOption(
                DependencyProvider::FLAG_ALL,
                null,
                InputOption::VALUE_NONE,
                'Enable all third-party modules (except Magento/PayPal/Amasty Base)'
            )
            ->addOption(
                DependencyProvider::FLAG_DEP,
                null,
                InputOption::VALUE_NONE,
                'Enable provided modules and their dependencies'
            );
    }

    private function findFlag(InputInterface $input): ?string
    {
        $selectedFlag = null;
        foreach (self::FLAG_KEYS as $flag) {
            if ($input->getOption($flag)) {
                $selectedFlag = $flag;
                break;
            }
        }

        return $selectedFlag;
    }

    private function getModuleList(): FullModuleList
    {
        return $this->objectManager->get(FullModuleList::class);
    }

    private function getDependencyProvider(): DependencyProvider
    {
        return $this->objectManager->get(DependencyProvider::class);
    }

    private function getModuleStatus(): Status
    {
        return $this->objectManager->get(Status::class);
    }
}
