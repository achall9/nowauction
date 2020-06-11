<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsMessaging
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMessaging\Model\ResourceModel\CadminMessage;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Ced\CsMessaging\Model\ResourceModel\Vcustomer
 */
class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Ced\CsMessaging\Model\CadminMessage', 'Ced\CsMessaging\Model\ResourceModel\CadminMessage');
    }
}