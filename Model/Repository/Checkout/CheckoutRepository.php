<?php

namespace Bkademy\Webpos\Model\Repository\Checkout;

use Bkademy\Webpos\Api\Data\Checkout\QuoteDataInterface as QuoteDataInterface;
use Bkademy\Webpos\Api\Data\Checkout\ResponseInterface as ResponseInterface;

class CheckoutRepository implements \Bkademy\Webpos\Api\Checkout\CheckoutRepositoryInterface
{
    /**
     * @var ResponseInterface
     */
    protected $_responseModelData;

    /**
     * @var QuoteDataInterface
     */
    protected $_quoteModelData;

    /**
     * @var Bkademy\Webpos\Model\AdminOrder\Create
     */
    protected $_orderCreateModel;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_catalogHelperImage;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @var \Magento\Payment\Model\MethodList
     */
    protected $_paymentMethodList;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * CheckoutRepository constructor.
     * @param ResponseInterface $responseModelData
     * @param QuoteDataInterface $quoteModelData
     * @param \Bkademy\Webpos\Model\AdminOrder\Create $orderCreateModel
     * @param \Magento\Catalog\Helper\Image $catalogHelperImage
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Payment\Model\MethodList $paymentMethodList
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Bkademy\Webpos\Api\Data\Checkout\ResponseInterface $responseModelData,
        \Bkademy\Webpos\Api\Data\Checkout\QuoteDataInterface $quoteModelData,
        \Bkademy\Webpos\Model\AdminOrder\Create $orderCreateModel,
        \Magento\Catalog\Helper\Image $catalogHelperImage,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Payment\Model\MethodList $paymentMethodList,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->_responseModelData = $responseModelData;
        $this->_quoteModelData = $quoteModelData;
        $this->_orderCreateModel = $orderCreateModel;
        $this->_catalogHelperImage = $catalogHelperImage;
        $this->_scopeConfig = $scopeConfig;
        $this->_customerRepository = $customerRepository;
        $this->_paymentMethodList = $paymentMethodList;
        $this->_orderRepository = $orderRepository;
    }

    /**
     * @param int|null $quoteId
     * @param \Bkademy\Webpos\Api\Data\Checkout\ItemBuyRequestInterface[] $items
     * @param string $customerId
     * @param string[] $section
     * @return \Bkademy\Webpos\Api\Data\Checkout\QuoteDataInterface
     */
    public function saveCart($quoteId, $items, $customerId, $section){
        $customer = $this->_customerRepository->getById($customerId);
        $this->_orderCreateModel->start($quoteId);
        $this->_orderCreateModel->processItems($items);
        $this->_orderCreateModel->assignCustomer($customer);
        $this->_orderCreateModel->finish();
        return $this->_getResponse(ResponseInterface::STATUS_SUCCESS, [], $section);
    }

    /**
     * @param string $quoteId
     * @return \Bkademy\Webpos\Api\Data\Checkout\QuoteDataInterface
     */
    public function removeCart($quoteId){
        $this->_orderCreateModel->start($quoteId);
        $this->_orderCreateModel->removeQuote();
        $this->_orderCreateModel->finish(false);
        return $this->_getResponse(ResponseInterface::STATUS_SUCCESS, [], [], true);
    }

    /**
     * @param string $quoteId
     * @param string $itemId
     * @return $this
     */
    public function removeItem($quoteId, $itemId){
        $this->_orderCreateModel->start($quoteId);
        $this->_orderCreateModel->removeQuoteItem($itemId);
        $this->_orderCreateModel->finish();
        return $this->_getResponse();
    }

    /**
     * @param string $quoteId
     * @param string $shippingMethod
     * @return $this
     */
    public function saveShippingMethod($quoteId, $shippingMethod){
        $this->_orderCreateModel->start($quoteId);
        $this->_orderCreateModel->setShippingMethod($shippingMethod);
        $this->_orderCreateModel->finish();
        return $this->_getResponse();
    }

    /**
     * @param string $quoteId
     * @param string $paymentMethod
     * @return $this
     */
    public function savePaymentMethod($quoteId, $paymentMethod){
        $this->_orderCreateModel->start($quoteId);
        $this->_orderCreateModel->setPaymentMethod($paymentMethod);
        $this->_orderCreateModel->finish();
        return $this->_getResponse();
    }

    /**
     * @param string $quoteId
     * @param string $quoteData
     * @return $this
     */
    public function saveQuoteData($quoteId, $quoteData){
        $this->_orderCreateModel->start($quoteId);
        $this->_orderCreateModel->finish();
        return $this->_getResponse();
    }

    /**
     * @param string $quoteId
     * @param string $customerId
     * @return $this
     */
    public function selectCustomer($quoteId, $customerId){
        $customer = $this->_customerRepository->getById($customerId);
        $this->_orderCreateModel->start($quoteId);
        $this->_orderCreateModel->assignCustomer($customer);
        $this->_orderCreateModel->finish();
        return $this->_getResponse();
    }

    /**
     * @param string $quoteId
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function placeOrder($quoteId){
        $this->_orderCreateModel->start($quoteId);
        $order = $this->_orderCreateModel->createOrder();
        if(!$order){
            throw new \Magento\Framework\Exception\LocalizedException(__('Có gì đó sai sai'));
        }
        return $this->_orderRepository->get($order->getId());
    }

    /**
     * @param int $status
     * @param array $messages
     * @param array $sections
     * @param bool $emptyQuote
     * @return mixed
     */
    protected function _getResponse($status = ResponseInterface::STATUS_SUCCESS, $messages = [], $sections = [], $emptyQuote = false){
        $data = array(
            ResponseInterface::KEY_STATUS => $status,
            ResponseInterface::KEY_MESSAGES => $messages,
            ResponseInterface::KEY_QUOTE_DATA => $this->_getQuoteData($sections, $emptyQuote)
        );
        return $this->_responseModelData->setData($data);
    }
    
    /**
     * @param $sections
     * @param $model
     * @return array
     */
    protected function _getQuoteData($sections = array(), $empty = false){
        $data = array(
            QuoteDataInterface::KEY_QUOTE_ID  => '',
            QuoteDataInterface::KEY_ITEMS  => '',
            QuoteDataInterface::KEY_TOTALS  => '',
            QuoteDataInterface::KEY_SHIPPING  => '',
            QuoteDataInterface::KEY_PAYMENT  => ''
        );
        if($empty == false){
            if(empty($sections) || $sections == QuoteDataInterface::KEY_QUOTE_ID || (is_array($sections) && in_array(QuoteDataInterface::KEY_QUOTE_ID, $sections))){
                $data[QuoteDataInterface::KEY_QUOTE_ID] = $this->_orderCreateModel->getQuote()->getId();
            }
            if(empty($sections) || $sections == QuoteDataInterface::KEY_ITEMS || (is_array($sections) && in_array(QuoteDataInterface::KEY_ITEMS, $sections))){
                $data[QuoteDataInterface::KEY_ITEMS] = $this->_getQuoteItems();
            }
            if(empty($sections) || $sections == QuoteDataInterface::KEY_TOTALS || (is_array($sections) && in_array(QuoteDataInterface::KEY_TOTALS, $sections))){
                $data[QuoteDataInterface::KEY_TOTALS] = $this->_getTotals();
            }
            if(empty($sections) || $sections == QuoteDataInterface::KEY_SHIPPING || (is_array($sections) && in_array(QuoteDataInterface::KEY_SHIPPING, $sections))){
                $data[QuoteDataInterface::KEY_SHIPPING] = $this->_getShipping();
            }
            if(empty($sections) || $sections == QuoteDataInterface::KEY_PAYMENT || (is_array($sections) && in_array(QuoteDataInterface::KEY_PAYMENT, $sections))){
                $data[QuoteDataInterface::KEY_PAYMENT] = $this->_getPayment();
            }
        }
        return $this->_quoteModelData->setData($data);
    }

    /**
     * @return array
     */
    protected function _getQuoteItems(){
        $result = array();
        $items = $this->_orderCreateModel->getQuote()->getAllVisibleItems();
        if(count($items)){
            foreach ($items as $item){
                $result[$item->getId()] = $item->getData();
                $result[$item->getId()]['offline_item_id'] =  $item->getBuyRequest()->getData('item_id');
            }
        }
        return $result;
    }

    /**
     * @return array
     */
    protected function _getTotals(){
        $totals = $this->_orderCreateModel->getQuote()->getTotals();
        $totalsResult = array();
        foreach ($totals as $total) {
            $totalsResult[] = $total->getData();
        }
        return $totalsResult;
    }

    /**
     * @return array
     */
    protected function _getShipping(){
        $shippingList = array();
        $quote = $this->_orderCreateModel->getQuote();
        $shippingAddress = $quote->getShippingAddress();
        if (!$shippingAddress->getCountryId()) {
            throw new  \Magento\Framework\Exception\LocalizedException(__('Shipping address not set.'));
        }
        $shippingAddress->collectShippingRates()->save();
        $shippingRates = $shippingAddress->getGroupedAllShippingRates();
        foreach ($shippingRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                $methodCode = $rate->getCode();
                $methodTitle = $rate->getCarrierTitle().' - '.$rate->getMethodTitle();
                $methodPrice = ($rate->getPrice() != null) ? $rate->getPrice() : '0';
                $shippingList[] = [
                    'code' => $methodCode,
                    'title' => $methodTitle,
                    'price' => $methodPrice
                ];
            }
        }
        return $shippingList;
    }

    /**
     * @return mixed
     */
    protected function _getPayment(){
        $paymentList = array();
        $quote = $this->_orderCreateModel->getQuote();
        $methods =  $this->_paymentMethodList->getAvailableMethods($quote);
        foreach ($methods as $method) {
            $paymentList[] = array(
                'code' => $method->getCode(),
                'title' => $method->getTitle(),
            );
        };
        return $paymentList;
    }

    /**
     * @param $path
     * @return mixed
     */
    protected function _getStoreConfig($path){
        return $this->_scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
