<?php
/**
 * Copyright © 2015 Ced. All rights reserved.
 */
namespace Ced\CsCommission\Model\ResourceModel;

/**
 * Commission resource
 */
class Commission extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('cscommission_commission', 'id');
    }

  
}
