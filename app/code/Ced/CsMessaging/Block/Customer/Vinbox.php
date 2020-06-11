<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsMessaging
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMessaging\Block\Customer;

/**
 * Class Vinbox
 * @package Ced\CsMessaging\Block\Customer
 */
class Vinbox extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected $_template = 'customer/vinbox.phtml';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'customer_vinbox';
        $this->_blockGroup = 'Ced_CsMessaging';
        $this->_headerText = __('Customer-Vendor Chat');
        parent::_construct();
    }

    protected function _prepareLayout()
    {
        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('Ced\CsMessaging\Block\Customer\Vinbox\Grid', 'ced.customer.vendor.inbox.grid')
        );

        return parent::_prepareLayout();
    }

    public function getCreateUrl()
    {
        return $this->getUrl(
            '*/*/vcompose'
        );
    }

}
