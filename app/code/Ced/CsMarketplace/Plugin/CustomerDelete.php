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
 * @package     Ced_CsMarketplace
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Plugin;

use Magento\Framework\Exception\LocalizedException;
use Ced\CsMarketplace\Model\Vendor;
use Ced\CsMarketplace\Helper\Mail;
use Ced\CsMarketplace\Model\VproductsFactory;

/**
 * Class CustomerDelete
 * @package Ced\CsMarketplace\Plugin
 */
class CustomerDelete
{
    /**
     * @var Vendor
     */
    public $vendorModel;

    /**
     * @var VproductsFactory
     */
    public $vProductsFactory;

    /**
     * @var Mail
     */
    public $mail;

    /**
     * CustomerDelete constructor.
     * @param Vendor $vendorModel
     * @param VproductsFactory $vProductsFactory
     * @param Mail $mail
     */
    public function __construct(
        Vendor $vendorModel,
        VproductsFactory $vProductsFactory,
        Mail $mail
    )
    {
        $this->vendorModel = $vendorModel;
        $this->vProductsFactory = $vProductsFactory;
        $this->mail = $mail;
    }

    /**
     * @param \Magento\Customer\Model\Customer $subject
     * @param $result
     * @return mixed
     */
    public function afterAfterDeleteCommit(\Magento\Customer\Model\Customer $subject, $result)
    {
        $customerId = $result->getId();
        try {
            $vendor = $this->vendorModel->loadByCustomerId($customerId);
            if ($vendor && $vendor->getId()) {
                $this->vProductsFactory->create()->deleteVendorProducts($vendor->getId());
                $this->mail->sendAccountEmail(Vendor::VENDOR_DELETED_STATUS, '', $vendor, 0);
                $vendor->delete();
            }
        } catch (LocalizedException $e) {
        }
        return $result;
    }
}