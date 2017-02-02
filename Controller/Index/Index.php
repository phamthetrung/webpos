<?php

namespace Bkademy\Webpos\Controller\Index;
/**
 * Created by PhpStorm.
 * User: root
 * Date: 12/01/2017
 * Time: 15:48
 */
class Index extends \Magento\Framework\App\Action\Action
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
        $resultLayout = $this->_resultPageFactory->create();
        $resultLayout->getLayout()->getUpdate()->removeHandle('default');
        return $resultLayout;
    }
}