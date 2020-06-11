<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ced\CsCommission\Model\ResourceModel\Commission;

/**
 * Commissions Collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Ced\CsCommission\Model\Commission', 'Ced\CsCommission\Model\ResourceModel\Commission');
    }
}
