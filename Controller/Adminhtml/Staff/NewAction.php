<?php
namespace Bkademy\Webpos\Controller\Adminhtml\Staff;

use Magento\Framework\Controller\ResultFactory;
/**
 * Action NewAction
 */
class NewAction extends \Bkademy\Webpos\Controller\Adminhtml\Staff
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        return $resultForward->forward('edit');
    }
}

