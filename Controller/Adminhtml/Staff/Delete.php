<?php

namespace Bkademy\Webpos\Controller\Adminhtml\Staff;

class Delete extends \Bkademy\Webpos\Controller\Adminhtml\Staff
{
    /**
     * @var \Bkademy\Webpos\Model\StaffFactory
     */
    protected $_staffFactory;

    /**
     * Delete constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Bkademy\Webpos\Model\StaffFactory $staffFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Bkademy\Webpos\Model\StaffFactory $staffFactory
    ) {

        $this->_staffFactory = $staffFactory;
        parent::__construct($context, $resultPageFactory, $resultLayoutFactory, $resultForwardFactory);
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $userId = $this->getRequest()->getParam('staff_id');
        if ($userId > 0) {
            $userModel = $this->_staffFactory->create()->load($this->getRequest()->getParam('staff_id'));
            try {
                $userModel->delete();
                $this->messageManager->addSuccess(__('Staff was successfully deleted'));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['_current' => true]);
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
