<?php
/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace Bkademy\Webpos\Block\Adminhtml\Staff\Edit;
/**
 * Class Tabs
 * @package Bkademy\Webpos\Block\Adminhtml\Staff\Staff\Edit
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('staff_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Staff Information'));
    }

    /**
     * @return $this
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'webpos_form',
            [
                'label' => __('General'),
                'title' => __('General'),
                'content' => $this->getLayout()->createBlock('Bkademy\Webpos\Block\Adminhtml\Staff\Edit\Tab\Form')
                    ->toHtml(),
                'active' => true
            ]
        );

        return parent::_beforeToHtml();
    }

}
