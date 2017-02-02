<?php

/**
 *  Copyright © 2016 Bkademy. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace Bkademy\Webpos\Model\Sales\Order;

use Bkademy\Webpos\Api\Sales\PaymentRepositoryInterface;
use Bkademy\Webpos\Model\Checkout\Data\PaymentItem;
/**
 * Class PaymentRepository
 * @package Bkademy\Webpos\Model\Sales\Order
 */
class PaymentRepository implements PaymentRepositoryInterface
{
    /**
     * @var \Bkademy\Webpos\Model\Payment\OrderPaymentFactory
     */
    protected $_orderPaymentFactory;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $transactionFactory;
    
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    
    public function __construct(
        \Bkademy\Webpos\Model\Payment\OrderPaymentFactory $orderPaymentFactory,
        \Bkademy\Webpos\Api\Sales\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Psr\Log\LoggerInterface $logger
    ){
        $this->_orderPaymentFactory = $orderPaymentFactory;
        $this->orderRepository = $orderRepository;
        $this->transactionFactory = $transactionFactory;
        $this->logger = $logger;
    }

    /**
     * Add payment for order
     *
     * @param int $id The invoice ID.
     * @param \Bkademy\Webpos\Api\Data\Checkout\PaymentInterface $payment
     * @return \Bkademy\Webpos\Api\Data\Sales\OrderInterface WebposOrder interface.
     */
    public function takePayment($id, \Bkademy\Webpos\Api\Data\Checkout\PaymentInterface $payment)
    {
        $order = $this->orderRepository->get($id);
        $additional_information = [];
        if($payment){
            $methodData = $payment->getMethodData();
            foreach ($methodData as $item){
                $orderPayment = $this->_orderPaymentFactory->create();
                $orderPayment->setData([
                    'order_id' => $order->getId(),
                    'real_amount' => $item[PaymentItem::KEY_REAL_AMOUNT],
                    'base_real_amount' => $item[PaymentItem::KEY_BASE_REAL_AMOUNT],
                    'payment_amount' => $item[PaymentItem::KEY_AMOUNT],
                    'base_payment_amount' => $item[PaymentItem::KEY_BASE_AMOUNT],
                    'method' => $item[PaymentItem::KEY_CODE],
                    'method_title' => $item[PaymentItem::KEY_TITLE],
                    'shift_id' => $item[PaymentItem::KEY_SHIFT_ID],
                    'reference_number' => $item[PaymentItem::KEY_REFERENCE_NUMBER]
                ]);
                $order->setBaseTotalPaid($order->getBaseTotalPaid() + $item[PaymentItem::KEY_BASE_AMOUNT]);
                $order->setTotalPaid($order->getTotalPaid() + $item[PaymentItem::KEY_AMOUNT]);
                $additional_information[] = $item->getAmount().' : '.$item->getTitle();
                try {
                    $orderPayment->save();
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                }
            }
            
        }
        try {
            $order->getPayment()
                //->setData($payment[PaymentItem::KEY_CODE].'_ref_no',$payment[PaymentItem::KEY_REAL_AMOUNT])
                ->setData('additional_information',$additional_information)
                ->setData('method','multipaymentforpos')
                ->save();
            if($order->getBaseTotalPaid()-$order->getBaseGrandTotal()>0){
                $order->setWebposBaseChange($order->getBaseTotalPaid()-$order->getBaseGrandTotal());
                $order->setWebposChange($order->getTotalPaid()-$order->getGrandTotal());
            }
            $order->save();
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return $this->orderRepository->get($id);
    }

}
