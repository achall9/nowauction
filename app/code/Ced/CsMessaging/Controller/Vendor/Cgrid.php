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

namespace Ced\CsMessaging\Controller\Vendor;

/**
 * Class Cgrid
 * @package Ced\CsMessaging\Controller\Vendor
 */
class Cgrid extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|void
     */
    public function execute()
    {
        if (!$this->_getSession()->getVendorId())
            return;
        $resultRedirect = $this->resultPageFactory->create();
        return $resultRedirect;

    }
}
