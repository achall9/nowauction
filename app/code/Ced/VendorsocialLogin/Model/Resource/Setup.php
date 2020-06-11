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
 * @package     VendorsocialLogin
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

/**
 * VendorsocialLogin    Resource/Setup Model
 *
 * @category    Ced
 * @package        Ced_VendorsocialLogin
 * @author        CedCommerce Magento Core Team <connect@cedcommerce.com>
 */

namespace Ced\VendorsocialLogin\Model\Resource;

class Setup extends \Magento\Eav\Setup\EavSetup

{

    protected $_customerAttributes = [];

    /**
     *
     * @param array $customerAttributes
     * @return \Ced\VendorsocialLogin\Model\Resource\Setup
     */

    public function setCustomerAttributes(array $customerAttributes)

    {

        $this->_customerAttributes = $customerAttributes;

        return $this;
    }

    /**
     * Add our custom attributes
     *
     * @return \Ced\VendorsocialLogin\Model\Resource\Setup
     */

    public function installCustomerAttributes()

    {
        foreach ($this->_customerAttributes as $code => $attr) {
            $this->addAttribute(\Magento\Customer\Model\Customer::ENTITY, $code, $attr);
        }

        return $this;
    }

    /**
     * Remove custom attributes
     *
     * @return \Ced\VendorsocialLogin\Model\Resource\Setup
     */

    public function removeCustomerAttributes()

    {

        foreach ($this->_customerAttributes as $code => $attr) {
            $this->removeAttribute('customer', $code);
        }

        return $this;
    }
}