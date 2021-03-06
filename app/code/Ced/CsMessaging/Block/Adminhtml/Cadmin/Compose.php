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

namespace Ced\CsMessaging\Block\Adminhtml\Cadmin;

use Magento\Backend\Block\Template;

/**
 * Class Compose
 * @package Ced\CsMessaging\Block\Adminhtml\Vadmin
 */
class Compose extends Template
{
    public function __construct(Template\Context $context,
                                array $data = [])
    {
        parent::__construct($context, $data);
    }

    public function getAllSelectedCustomerIds()
    {
        $customerIds = $this->getRequest()->getParam('selected');
        return implode($customerIds,',');
    }
}
