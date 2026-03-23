<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Setup\SampleData\Installer;

use Amasty\Gdpr\Api\PolicyRepositoryInterface;
use Amasty\Gdpr\Model\Policy as PolicyModel;
use Amasty\Gdpr\Model\PolicyFactory;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Setup;
use Magento\Framework\Setup\SampleData\FixtureManager;
use Psr\Log\LoggerInterface;

class Policy implements Setup\SampleData\InstallerInterface
{
    private const POLICY_FIXTURE_PATH = 'Amasty_Gdpr::fixtures/policy.csv';
    private const POLICY_FIXTURE_ENCLOSURE = '|';

    /**
     * @var FixtureManager
     */
    private $fixtureManager;

    /**
     * @var Csv
     */
    private $csvReader;

    /**
     * @var PolicyRepositoryInterface
     */
    private $policyRepository;

    /**
     * @var PolicyFactory
     */
    private $policyFactory;

    /**
     * @var File
     */
    private $file;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Setup\SampleData\Context $sampleDataContext,
        PolicyRepositoryInterface $policyRepository,
        PolicyFactory $policyFactory,
        File $file,
        LoggerInterface $logger
    ) {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = clone $sampleDataContext->getCsvReader();
        $this->csvReader->setEnclosure(self::POLICY_FIXTURE_ENCLOSURE);
        $this->policyRepository = $policyRepository;
        $this->policyFactory = $policyFactory;
        $this->file = $file;
        $this->logger = $logger;
    }

    public function install()
    {
        if (!$this->policyRepository->getCurrentPolicy()) {
            try {
                $fixtureFilePath = $this->fixtureManager->getFixture(self::POLICY_FIXTURE_PATH);

                if ($this->file->isExists($fixtureFilePath) && $this->file->isFile($fixtureFilePath)) {
                    $fixtureRows = $this->csvReader->getData($fixtureFilePath);
                    $fixtureHeader = array_shift($fixtureRows);
                    $policyData = array_combine(
                        array_values($fixtureHeader),
                        array_values(reset($fixtureRows))
                    );
                    /** @var Policy $policyModel */
                    $policyModel = $this->policyFactory->create();
                    $policyModel->addData($policyData);
                    $policyModel->setLastEditedBy(null);
                    $policyModel->setStatus(PolicyModel::STATUS_ENABLED);
                    $this->policyRepository->save($policyModel);
                }
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }
    }
}
