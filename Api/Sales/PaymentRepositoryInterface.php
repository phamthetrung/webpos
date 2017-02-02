<?php

/**
 *  Copyright © 2016 Bkademy. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace Bkademy\Webpos\Api\Sales;

/**
 * Interface PaymentRepositoryInterface
 * @package Bkademy\Webpos\Api\Sales
 */
interface PaymentRepositoryInterface
{
    /**
     * Add payment for order
     *
     * @param int $id The order ID.
     * @param \Bkademy\Webpos\Api\Data\Checkout\PaymentInterface $payment
     * @return \Bkademy\Webpos\Api\Data\Sales\OrderInterface WebposOrder interface.
     */
    public function takePayment($id, \Bkademy\Webpos\Api\Data\Checkout\PaymentInterface $payment);

}
