<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Bkademy\Webpos\Controller\Adminhtml\Staff;

class Index extends \Bkademy\Webpos\Controller\Adminhtml\Staff
{
    /**
     * Orders grid
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('Staff Management'));
        return $resultPage;
    }
}
