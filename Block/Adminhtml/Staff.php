<?php

namespace Bkademy\Webpos\Block\Adminhtml;

class Staff extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml';
        $this->_blockGroup = 'Bkademy_Webpos';
        $this->_headerText = __('Staff Managemer');
        
        parent::_construct();
    }
}