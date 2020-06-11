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

namespace Ced\CsMessaging\Block\Vendor;

/**
 * Class Cinbox
 * @package Ced\CsMessaging\Block\Vendor
 */
class Cinbox extends \Magento\Backend\Block\Widget\Container
{
    protected $_template = 'Ced_CsMessaging::vendor/cinbox.phtml';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'vendor_cinbox';
        $this->_blockGroup = 'Ced_CsMessaging';
        $this->_headerText = __('Vendor-Customer Chat');
        parent::_construct();
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $addButtonProps = [
            'id' => 'customer_composer',
            'label' => __('Compose'),
            'class' => 'add',
            'onclick' => "setLocation('" . $this->getCreateUrl() . "')",
        ];
        $this->buttonList->add('add_new', $addButtonProps);

        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('Ced\CsMessaging\Block\Vendor\Cinbox\Grid', 'ced.vendor.customer.inbox.grid')
        );

        return parent::_prepareLayout();

    }

    /**
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }

    /**
     * @return string
     */
    public function getCreateUrl()
    {
        return $this->getUrl(
            '*/*/ccompose'
        );
    }

}
