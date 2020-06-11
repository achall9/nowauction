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
 * @category  Ced
 * @package   Ced_CsMultiShipping
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMultiShipping\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
 
class Vendorrates extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{

    protected $_code = 'vendor_rates';
    protected $_isFixed = true;
   
    public function collectRates(RateRequest $request)
    {
        return false;
    }

    public function getAllowedMethods()
    {
        return array('vendor_rates'=>$this->getConfigData('name'));
    }
}
