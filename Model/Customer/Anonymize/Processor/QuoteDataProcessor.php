<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Model\Customer\Anonymize\Processor;

use Exception;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote\Address;
use Lof\Gdpr\Service\Anonymize\AnonymizerInterface;
use Lof\Gdpr\Service\Erase\ProcessorInterface;

final class QuoteDataProcessor implements ProcessorInterface
{
    /**
     * @var AnonymizerInterface
     */
    private $anonymizer;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var Address
     */
    private $quoteAddressResourceModel;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    public function __construct(
        AnonymizerInterface $anonymizer,
        CartRepositoryInterface $quoteRepository,
        Address $quoteAddressResourceModel,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->anonymizer = $anonymizer;
        $this->quoteRepository = $quoteRepository;
        $this->quoteAddressResourceModel = $quoteAddressResourceModel;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function execute(int $customerId): bool
    {
        $this->searchCriteriaBuilder->addFilter('customer_id', $customerId);
        $quoteList = $this->quoteRepository->getList($this->searchCriteriaBuilder->create());

        /** @var Quote $quote */
        foreach ($quoteList->getItems() as $quote) {
            $this->quoteRepository->save($this->anonymizer->anonymize($quote));

            /** @var Quote\Address|null $quoteAddress */
            foreach ([$quote->getBillingAddress(), $quote->getShippingAddress()] as $quoteAddress) {
                if ($quoteAddress) {
                    $this->quoteAddressResourceModel->save($this->anonymizer->anonymize($quoteAddress));
                }
            }
        }

        return true;
    }
}
