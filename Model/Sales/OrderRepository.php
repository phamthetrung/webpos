<?php
/**
 *  Copyright © 2016 Bkademy. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace Bkademy\Webpos\Model\Sales;

use Bkademy\Webpos\Api\Data\Sales\OrderSearchResultInterfaceFactory as SearchResultFactory;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Repository class for @see OrderInterface
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderRepository implements \Bkademy\Webpos\Api\Sales\OrderRepositoryInterface
{

    /**
     * @var SearchResultFactory
     */
    protected $searchResultFactory = null;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order|Resource
     */
    protected $orderResourceModel;

    /**
     * OrderInterface[]
     *
     * @var array
     */
    protected $registry = [];
    
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderCommentSender
     */
    protected $orderCommentSender;
    
    /**
     * @var \Magento\Sales\Model\OrderNotifier
     */
    protected $notifier;
    
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * OrderRepository constructor.
     * @param SearchResultFactory $searchResultFactory
     * @param OrderFactory $orderFactory
     * @param \Magento\Sales\Model\ResourceModel\Order $orderResourceModel
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderCommentSender $orderCommentSender
     * @param \Magento\Sales\Model\OrderNotifier $notifier
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        SearchResultFactory $searchResultFactory,
        \Bkademy\Webpos\Model\Sales\OrderFactory $orderFactory,
        \Magento\Sales\Model\ResourceModel\Order $orderResourceModel,
        \Magento\Sales\Model\Order\Email\Sender\OrderCommentSender $orderCommentSender,
        \Magento\Sales\Model\OrderNotifier $notifier,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->searchResultFactory = $searchResultFactory;
        $this->orderFactory = $orderFactory;
        $this->orderResourceModel = $orderResourceModel;
        $this->orderCommentSender = $orderCommentSender;
        $this->notifier = $notifier;
        $this->logger = $logger;
    }

    /**
     * load entity
     *
     * @param int $id
     * @return \Bkademy\Webpos\Api\Data\Sales\OrderInterface
     * @throws \Exception
     * @throws \Exception
     */
    public function get($id)
    {
        if (!$id) {
            throw new \Exception(__('Id required'));
        }
        /** @var OrderInterface $entity */
        $order = $this->orderFactory->create()->load($id);
        if (!$order->getEntityId()) {
            throw new \Exception(__('Requested entity doesn\'t exist'));
        }
        try{
            $this->setShippingAssignments($order);
        }catch(\Exception $e){
            $this->logger->critical($e);
        }
        return $order;
    }

    /**
     * Find entities by criteria
     *
     * @param \Magento\Framework\Api\SearchCriteria $searchCriteria
     * @return \Bkademy\Webpos\Api\Data\Sales\OrderSearchResultInterface Order search result interface.
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria)
    {
        /** @var \Bkademy\Webpos\Api\Data\Sales\OrderSearchResultInterface $searchResult */
        $searchResult = $this->searchResultFactory->create();

        $this->applySearchCriteria($searchResult, $searchCriteria);

        foreach ($searchResult->getItems() as $order) {
            try{
                $this->setShippingAssignments($order);
            }catch(\Exception $e){
                $this->logger->critical($e);
            }
        }
        return $searchResult;
    }

    /**
     * Register entity to delete
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $entity
     * @return bool
     */
    public function delete(\Magento\Sales\Api\Data\OrderInterface $entity)
    {
        $this->orderResourceModel->delete($entity);
        unset($this->registry[$entity->getEntityId()]);
        return true;
    }

    /**
     * Delete entity by Id
     *
     * @param int $id
     * @return bool
     */
    public function deleteById($id)
    {
        $entity = $this->get($id);
        return $this->delete($entity);
    }

    /**
     * Perform persist operations for one entity
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $entity
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function save(\Magento\Sales\Api\Data\OrderInterface $entity)
    {
        $this->orderResourceModel->save($entity);
        return $entity;
    }

    /**
     * @param $searchResult
     * @param \Magento\Framework\Api\SearchCriteria $searchCriteria
     */
    protected function applySearchCriteria(
        \Magento\Sales\Api\Data\OrderSearchResultInterface $searchResult,
        \Magento\Framework\Api\SearchCriteria $searchCriteria
    ){
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $searchResult->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $searchResult->setCurPage($searchCriteria->getCurrentPage());
        $searchResult->setPageSize($searchCriteria->getPageSize());
    }   

    /**
     * @param OrderInterface $order
     * @return void
     */
    protected function setShippingAssignments(OrderInterface $order)
    {
        $extensionAttributes = $order->getExtensionAttributes();
        if ($extensionAttributes === null) {
            $extensionAttributes = \Magento\Framework\App\ObjectManager::getInstance()->create(
                '\Magento\Sales\Api\Data\OrderExtension'
            );
        } elseif ($extensionAttributes->getShippingAssignments() !== null) {
            return;
        }
        $shippingAssignments = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Bkademy\Webpos\Model\Sales\Order\ShippingAssignmentBuilder'
        );
        $shippingAssignments->setOrderId($order->getEntityId());
        $extensionAttributes->setShippingAssignments($shippingAssignments->create());
        $order->setExtensionAttributes($extensionAttributes);
    }

    /**
     * Order cancel
     *
     * @param int $id
     * @param \Magento\Sales\Api\Data\OrderStatusHistoryInterface|null $comment Status history comment.
     * @return \Bkademy\Webpos\Api\Data\Sales\OrderInterface WebposOrder interface.
     */
    public function cancel($id,\Magento\Sales\Api\Data\OrderStatusHistoryInterface $comment = null)
    {
        $order = $this->get($id);
        if ((bool)$order->cancel()) {
            if($comment){
                $this->save($order);
                return $this->addComment($order->getId(), $comment);
            }else{
                return $this->save($order);
            }
        }
        return $order;
    }

    /**
     * Add comment to order
     *
     * @param int $id
     * @param \Magento\Sales\Api\Data\OrderStatusHistoryInterface $statusHistory
     * @return \Bkademy\Webpos\Api\Data\Sales\OrderInterface WebposOrder interface.
     */
    public function addComment($id, \Magento\Sales\Api\Data\OrderStatusHistoryInterface $statusHistory)
    {
        
        $order = $this->get($id);
        $history = $order->addStatusHistoryComment($statusHistory->getComment(), false);
        $history->setIsVisibleOnFront(true);
        $history->setIsCustomerNotified(true);
        $history->save();
        $this->save($order);
        $comment = trim(strip_tags($statusHistory->getComment()));
        try{
            $this->orderCommentSender->send($order, true, $comment);
        }catch (\Exception $e){
            $this->logger->critical($e);
        }
        return $this->get($id);
    }


    /**
     * Notify user
     *
     * @param int $id
     * @param string|null $email
     * @return bool
     */
    public function notify($id, $email)
    {
        $result = false;
        $order = $this->get($id);
        if($email)
            $order->setCustomerEmail($email);
        try{
            $this->notifier->notify($order);
        }catch (\Exception $e){
            $this->logger->critical($e);
        }
        return $result;
    }

    /**
     * Unhold holded order
     *
     * @param int $id The order ID.
     * @return \Bkademy\Webpos\Api\Data\Sales\OrderInterface WebposOrder interface.
     */
    public function unhold($id){
        $order = $this->get($id);
        $order->unhold();
        return $this->save($order);
    }
}
