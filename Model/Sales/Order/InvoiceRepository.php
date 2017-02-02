<?php

/**
 *  Copyright © 2016 Bkademy. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace Bkademy\Webpos\Model\Sales\Order;

use Bkademy\Webpos\Api\Sales\InvoiceRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Metadata as Metadata;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\InvoiceSearchResultInterfaceFactory as SearchResultFactory;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Bkademy\Webpos\Model\Checkout\Data\Payment;
use Bkademy\Webpos\Model\Checkout\Data\PaymentItem;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class InvoiceRepository
 */
class InvoiceRepository extends \Magento\Sales\Model\Order\InvoiceRepository implements InvoiceRepositoryInterface
{
    /**
     * @var Metadata
     */
    protected $metadata;

    /**
     * @var SearchResultFactory
     */
    protected $searchResultFactory;
    
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var InvoiceService
     */
    protected $invoiceService;

    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var InvoiceSender
     */
    protected $invoiceSender;

    /**
     * @var ShipmentSender
     */
    protected $shipmentSender;

    /**
     * @var \Bkademy\Webpos\Model\Payment\OrderPaymentFactory
     */
    protected $orderPaymentFactory;

    /**
     * @var \Bkademy\Webpos\Helper\Currency
     */
    protected $currencyHelper;

    /**
     *
     * @var \Magento\Framework\App\ObjectManager 
     */
    protected $_objectManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * InvoiceRepository constructor.
     * @param Metadata $invoiceMetadata
     * @param SearchResultFactory $searchResultFactory
     * @param \Bkademy\Webpos\Api\Sales\OrderRepositoryInterface $orderRepository
     * @param \Bkademy\Webpos\Model\Service\Sales\InvoiceService $invoiceService
     * @param \Magento\Framework\DB\TransactionFactory $transactionFactory
     * @param InvoiceSender $invoiceSender
     * @param ShipmentSender $shipmentSender
     * @param \Bkademy\Webpos\Model\Payment\OrderPaymentFactory $orderPaymentFactory
     * @param \Bkademy\Webpos\Helper\Currency $currencyHelper
     */
    public function __construct(
        Metadata $invoiceMetadata,
        SearchResultFactory $searchResultFactory,
        \Bkademy\Webpos\Api\Sales\OrderRepositoryInterface $orderRepository,
        \Bkademy\Webpos\Model\Service\Sales\InvoiceService $invoiceService,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        InvoiceSender $invoiceSender,
        ShipmentSender $shipmentSender,
        \Bkademy\Webpos\Model\Payment\OrderPaymentFactory $orderPaymentFactory,
        \Bkademy\Webpos\Helper\Currency $currencyHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->metadata = $invoiceMetadata;
        $this->searchResultFactory = $searchResultFactory;
        $this->orderRepository = $orderRepository;
        $this->invoiceService = $invoiceService;
        $this->transactionFactory = $transactionFactory;
        $this->invoiceSender = $invoiceSender;
        $this->shipmentSender = $shipmentSender;
        $this->orderPaymentFactory = $orderPaymentFactory;
        $this->currencyHelper = $currencyHelper;
        $this->logger = $logger;
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     * Perform persist operations for one entity
     * 
     * @param \Magento\Sales\Api\Data\InvoiceInterface $entity
     * @param \Bkademy\Webpos\Api\Data\Checkout\PaymentInterface|null $payment
     * @param string|null $invoiceAmout
     * @return \Bkademy\Webpos\Api\Data\Sales\OrderInterface Order interface.
     */
    public function saveInvoice(
        \Magento\Sales\Api\Data\InvoiceInterface $entity,
        \Bkademy\Webpos\Api\Data\Checkout\PaymentInterface $payment = null,
        $invoiceAmount = null
    ){
        $orderId = $entity->getOrderId();
        $order = $this->orderRepository->get($orderId);
        $data = $this->prepareInvoice($entity);
        $invoice = null;
        $invoiceItems = isset($data['invoice']['items']) ? $data['invoice']['items'] : [];
        if($invoiceAmount && (float)$invoiceAmount >0){
            $baseInvoiceAmount = $this->currencyHelper->currencyConvert($invoiceAmount, $entity->getStoreCurrencyCode(), $order->getBaseCurrencyCode());
            if($order->getBaseTotalDue() - $baseInvoiceAmount <= 0.0001){
                $baseInvoiceAmount = $order->getBaseTotalDue();
                $invoiceAmount = $order->getTotalDue();
                $invoice = $this->invoiceService->prepareInvoice($order, []);
            }else{
                $invoice = $this->invoiceService->prepareInvoice($order, $invoiceItems);
            }
            $invoice->setBaseGrandTotal($baseInvoiceAmount);
            $invoice->setGrandTotal($invoiceAmount);
            $invoice->setBaseSubtotal($invoice->getBaseGrandTotal()+$invoice->getBaseDiscountAmount()-$invoice->getBaseShippingAmount());
            $invoice->setSubtotal($invoice->getGrandTotal()+$invoice->getDiscountAmount()-$invoice->getShippingAmount());
            
            $orderPayment = $this->_objectManager->create("Bkademy\Webpos\Model\Payment\OrderPayment");
            $additional_information = $order->getPayment()->getData('additional_information');
            if($payment){
                $methodData = $payment->getMethodData();
                $paymentCode = $methodData[0]["code"];
                $paymentTitle = $methodData[0]["title"];
                $orderPayment->setData([
                    "order_id" => $orderId,
                    "payment_amount" => $invoiceAmount,
                    "base_payment_amount" => $baseInvoiceAmount,
                    "method" => $paymentCode,
                    "method_title" => $paymentTitle
                ]);
                $orderPayment->save();
                $additional_information[] = $invoiceAmount.' : '.$paymentTitle;
            }else{
                $paymentCode = $order->getPayment()->getMethodInstance()->getCode();
                $paymentTitle = $order->getPayment()->getMethodInstance()->getTitle();
                $orderPayment->setData([
                    "order_id" => $orderId,
                    "payment_amount" => $invoiceAmount,
                    "base_payment_amount" => $baseInvoiceAmount,
                    "method" => $paymentCode,
                    "method_title" => $paymentTitle
                ]);
                $orderPayment->save();
                $additional_information[] = $invoiceAmount.' : '.$paymentTitle;
            }
            unset($additional_information['method_title']);
            $order->getPayment()->setData('additional_information',$additional_information);
            if($baseInvoiceAmount == $order->getBaseGrandTotal()){
                $items = $order->getAllVisibleItems();
                if(count($items) > 0){
                    $baseAmount = $baseInvoiceAmount/count($items);
                    $amount = $invoiceAmount/count($items);
                    foreach ($items as $item){
                        $qty_ordered = $item->getData('qty_ordered');
                        $qty_refunded = $item->getData('qty_refunded');
                        $qty_canceled = $item->getData('qty_canceled');
                        $item->setQtyInvoiced($qty_ordered - $qty_refunded - $qty_canceled);
                        $item->setRowInvoiced($amount);
                        $item->setBaseRowInvoiced($baseAmount);
                    }
                }
            }
            $order->save();
        }else{
            $invoice = $this->invoiceService->prepareInvoice($order, $invoiceItems);
            /*
            if($invoice->getBaseGrandTotal()>$order->getTotalDue()){
                $invoice->setBaseGrandTotal($order->getBaseTotalDue());
                $invoice->setGrandTotal($order->getTotalDue());
                $invoice->setBaseSubtotal($invoice->getBaseGrandTotal()+$invoice->getBaseDiscountAmount()-$invoice->getBaseShippingAmount());
                $invoice->setSubtotal($invoice->getGrandTotal()+$invoice->getDiscountAmount()-$invoice->getShippingAmount());
            }
             */
        }
        

        if (!$invoice) {
            throw new LocalizedException(__('We can\'t save the invoice right now.'));
        }

        if (!$invoice->getTotalQty()) {
            throw new LocalizedException(
                __('You can\'t create an invoice without products.')
            );
        }
    
        
        if (!empty($data['invoice']['comment_text'])) {
            $invoice->addComment(
                $data['invoice']['comment_text'],
                isset($data['invoice']['comment_customer_notify']),
                isset($data['invoice']['is_visible_on_front'])
            );

            $invoice->setCustomerNote($data['invoice']['comment_text']);
            $invoice->setCustomerNoteNotify(isset($data['invoice']['comment_customer_notify']));
        }
        $invoice->register();

        $invoice->getOrder()->setCustomerNoteNotify(!empty($data['invoice']['send_email']));
        $invoice->getOrder()->setIsInProcess(true);
        
        $transactionSave = $this->transactionFactory->create()
        ->addObject(
            $invoice
        )->addObject(
            $invoice->getOrder()
        );

        $shipment = false;
        if (!empty($data['invoice']['do_shipment']) || (int)$invoice->getOrder()->getForcedShipmentWithInvoice()) {
            $shipment = $this->_prepareShipment($invoice);
            if ($shipment) {
                $transactionSave->addObject($shipment);
            }
        }
        $transactionSave->save();
        try {
            if (!empty($data['invoice']['send_email'])) {
                $this->invoiceSender->send($invoice);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        
        return $this->orderRepository->get($orderId);
    }

    /**
     * prepare invoice from \Magento\Sales\Api\Data\InvoiceInterface $entity 
     * 
     * @param \Magento\Sales\Api\Data\InvoiceInterface $entity
     * @return array|null
     */
    protected function prepareInvoice(\Magento\Sales\Api\Data\InvoiceInterface $entity){
        $data = [];
        $items = $entity->getItems();
        $orderId = $entity->getOrderId();
        if(count($items>0) && $orderId) {
            $data['order_id'] = $orderId;
            $invoice = [];
            foreach ($items as $item){
                $invoice['items'][$item->getOrderItemId()] = $item->getQty();
            }
            $invoice['send_email'] = $entity->getEmailSent();
            $comments = $entity->getComments();
            if(count($comments) && $comment = $comments[0]){
                $invoice['comment_text'] = $comment->getComment();
                if($invoice['send_email'])
                    $invoice['comment_customer_notify'] = 1;
            }
            $data['invoice'] = $invoice;
            return $data;
        }
        return null;
    }

    /**
     *
     * @param \Bkademy\Webpos\Api\Data\Sales\OrderInterface $order
     * @param array $data
     */
    protected function _savePaymentsToOrder($order, $data, $invoice){
        if($order instanceof \Bkademy\Webpos\Api\Data\Sales\OrderInterface){
            if(count($data) > 0){
                foreach ($data as $payment){
                    if (isset($payment[PaymentItem::KEY_CODE])) {
                        $order->getPayment()->setData($payment[PaymentItem::KEY_CODE].'_ref_no',$payment[PaymentItem::KEY_AMOUNT]);

                        $orderPayment = $this->orderPaymentFactory->create();
                        $orderPayment->setData([
                            "order_id" => $order->getId(),
                            "payment_amount" => $invoice->getGrandTotal(),
                            "base_payment_amount" => $invoice->getBaseGrandTotal(),
                            "method" => $payment[PaymentItem::KEY_CODE],
                            "method_title" => $payment[PaymentItem::KEY_TITLE]
                        ]);
                        $orderPayment->save();
                    }
                }
            }
        }
    }
}
