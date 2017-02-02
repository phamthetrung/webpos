<?php
/**
 * Copyright © 2016 Bkademy. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Bkademy\Webpos\Controller\Index;

/**
 * Class Index
 * @package Bkademy\Webpos\Controller\Index
 */
class Collection extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ){
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $productCollection = $this->_objectManager
            ->create('Magento\Catalog\Model\ResourceModel\Product\Collection')
            ->addAttributeToSelect(['name', 'price', 'image'])
            ->addAttributeToFilter( 'entity_id', array( 'in' => array(210,211,212) ) )
            ->setPageSize(10,1);
        $output = '';

        $productCollection->setDataToAll('price', 20);
        $productCollection->save();
        foreach ($productCollection as $product) {
            $output .= \Zend_Debug::dump($product->debug(), null, false);
        }
//        $output = $productCollection->getSelect()->__toString();
        $this->getResponse()->setBody($output);
    }
}