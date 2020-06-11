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
 * @package     Ced_CsTableRateShipping
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsTableRateShipping\Model;

/**
 * Class Tablerate
 * @package Ced\CsTableRateShipping\Model
 */
class Tablerate extends \Magento\Framework\Model\AbstractModel
{

    const STATUS_APPROVED = '1';

    const STATUS_NOT_APPROVED = '0';

    const STATUS_PENDING = '2';

    protected static $_states;

    protected static $_statuses;

    protected $_codeSeparator = '-';

    /**
     * Tablerate constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry
    )
    {
        parent::__construct($context, $registry);

    }

    protected function _construct()
    {
        $this->_init('Ced\CsTableRateShipping\Model\Resource\Tablerate');
    }
}
 