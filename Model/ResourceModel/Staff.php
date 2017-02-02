<?php

namespace Bkademy\Webpos\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;

class Staff extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     * Get table name from config
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('webpos_staff', 'staff_id');
    }

}