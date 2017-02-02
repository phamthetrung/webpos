<?php

/**
 *  Copyright © 2016 Bkademy. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace Bkademy\Webpos\Api\Sales;

interface InvoiceRepositoryInterface extends \Magento\Sales\Api\InvoiceRepositoryInterface
{
    /**
     * Performs persist operations for a specified invoice.
     *
     * @param \Magento\Sales\Api\Data\InvoiceInterface $entity The invoice.
     * @param \Bkademy\Webpos\Api\Data\Checkout\PaymentInterface|null $payment
     * @param string|null $invoiceAmount
     * @return \Bkademy\Webpos\Api\Data\Sales\OrderInterface Order interface.
     */
    public function saveInvoice(
        \Magento\Sales\Api\Data\InvoiceInterface $entity,
        \Bkademy\Webpos\Api\Data\Checkout\PaymentInterface $payment = null,
        $invoiceAmount = null
    );
}
