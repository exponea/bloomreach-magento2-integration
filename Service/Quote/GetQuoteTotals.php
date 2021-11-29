<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Quote;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Quote\Model\GuestCart\GuestCartTotalRepository;
use Magento\Quote\Model\QuoteIdMask as QuoteIdMaskModel;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResource;

/**
 * The class is responsible for obtaining quote totals
 */
class GetQuoteTotals
{
    /**
     * @var CartTotalRepositoryInterface
     */
    private $cartTotalRepository;

    /**
     * @var GuestCartTotalRepository
     */
    private $guestCartTotalRepository;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var QuoteIdMaskResource
     */
    private $quoteIdMaskResource;

    /**
     * @var TotalsInterface[]
     */
    private $totalsCache = [];

    /**
     * @param CartTotalRepositoryInterface $cartTotalRepository
     * @param GuestCartTotalRepository $guestCartTotalRepository
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param QuoteIdMaskResource $quoteIdMaskResource
     */
    public function __construct(
        CartTotalRepositoryInterface $cartTotalRepository,
        GuestCartTotalRepository $guestCartTotalRepository,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        QuoteIdMaskResource $quoteIdMaskResource
    ) {
        $this->cartTotalRepository = $cartTotalRepository;
        $this->guestCartTotalRepository = $guestCartTotalRepository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteIdMaskResource = $quoteIdMaskResource;
    }

    /**
     * Returns quote totals
     *
     * @param CartInterface $quote
     *
     * @return TotalsInterface|null
     */
    public function execute(CartInterface $quote): ?TotalsInterface
    {
        if (!array_key_exists($quote->getId(), $this->totalsCache)) {
            try {
                $quoteTotals = $this->getQuoteTotals($quote);
            } catch (NoSuchEntityException $e) {
                $quoteTotals = null;
            }

            $this->totalsCache[$quote->getId()] = $quoteTotals;
        }

        return $this->totalsCache[$quote->getId()];
    }

    /**
     * Calculates quote totals
     *
     * @param CartInterface $quote
     *
     * @return TotalsInterface
     * @throws NoSuchEntityException
     */
    private function getQuoteTotals(CartInterface $quote): TotalsInterface
    {
        if ($quote->getCustomerIsGuest()) {
            return $this->guestCartTotalRepository->get($this->getQuoteMaskedId((int) $quote->getId()));
        }

        return $this->cartTotalRepository->get($quote->getId());
    }

    /**
     * Returns quote masked id
     *
     * @param int $quoteId
     *
     * @return string
     */
    private function getQuoteMaskedId(int $quoteId): string
    {
        /** @var QuoteIdMaskModel $quoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create();
        $this->quoteIdMaskResource->load($quoteIdMask, $quoteId, 'quote_id');

        return $quoteIdMask->getMaskedId() ?? '';
    }
}
