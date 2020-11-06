<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Cron;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Lof\Gdpr\Api\Data\EraseEntityInterface;
use Lof\Gdpr\Api\Data\EraseEntitySearchResultsInterface;
use Lof\Gdpr\Api\EraseEntityManagementInterface;
use Lof\Gdpr\Api\EraseEntityRepositoryInterface;
use Lof\Gdpr\Model\Config;
use Psr\Log\LoggerInterface;

/**
 * Process erase of all scheduled entities
 */
final class EraseEntity
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var EraseEntityManagementInterface
     */
    private $eraseManagement;

    /**
     * @var EraseEntityRepositoryInterface
     */
    private $eraseEntityRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(
        LoggerInterface $logger,
        Config $config,
        Registry $registry,
        EraseEntityManagementInterface $eraseManagement,
        EraseEntityRepositoryInterface $eraseEntityRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DateTime $dateTime
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->registry = $registry;
        $this->eraseManagement = $eraseManagement;
        $this->eraseEntityRepository = $eraseEntityRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->dateTime = $dateTime;
    }

    public function execute(): void
    {
        if ($this->config->isModuleEnabled() && $this->config->isErasureEnabled()) {
            $oldValue = $this->registry->registry('isSecureArea');
            $this->registry->register('isSecureArea', true, true);

            foreach ($this->retrieveEraseEntityList()->getItems() as $eraseEntity) {
                try {
                    $this->eraseManagement->process($eraseEntity);
                } catch (\Exception $e) {
                    $this->logger->error($e->getMessage(), $e->getTrace());
                }
            }

            $this->registry->register('isSecureArea', $oldValue, true);
        }
    }

    private function retrieveEraseEntityList(): EraseEntitySearchResultsInterface
    {
        $this->searchCriteriaBuilder->addFilter(
            EraseEntityInterface::SCHEDULED_AT,
            $this->dateTime->date(),
            'lteq'
        );
        $this->searchCriteriaBuilder->addFilter(
            EraseEntityInterface::STATE,
            EraseEntityInterface::STATE_COMPLETE,
            'neq'
        );
        $this->searchCriteriaBuilder->addFilter(
            EraseEntityInterface::STATUS,
            [EraseEntityInterface::STATUS_READY, EraseEntityInterface::STATUS_FAILED],
            'in'
        );

        try {
            $eraseCustomerList = $this->eraseEntityRepository->getList($this->searchCriteriaBuilder->create());
        } catch (LocalizedException $e) {
            $eraseCustomerList = [];
        }

        return $eraseCustomerList;
    }
}
