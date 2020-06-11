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
 * @author      CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMessaging\Helper;

use Magento\Framework\App\Helper\Context;
use Psr\Log\LoggerInterface;

/**
 * Class Mail
 * @package Ced\CsMessaging\Helper
 */
class Mail extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Mail constructor.
     * @param Context $context
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        Context $context,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        LoggerInterface $logger = null
    )
    {
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        $this->logger = $logger;

        parent::__construct($context);
    }

    /**
     * @param $data
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendMailToVendor($data)
    {
        $templateId = $this->csmarketplaceHelper->getStoreConfig('ced_csmarketplace/csmessaging/vendor_email_template');
        $this->sendMail($data,$templateId);
        return true;
    }

    /**
     * @param $data
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendMailToCustomer($data)
    {
        $templateId = $this->csmarketplaceHelper->getStoreConfig('ced_csmarketplace/csmessaging/customer_email_template');
        $this->sendMail($data,$templateId);
        return true;
    }

    /**
     * @param $data
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendMailToAdmin($data)
    {
        $templateId = $this->csmarketplaceHelper->getStoreConfig('ced_csmarketplace/csmessaging/admin_email_template');
        $this->sendMail($data,$templateId);
        return true;
    }

    /**
     * @param $data
     * @param $template
     */
    public function sendMail($data,$template)
    {
        $senderName = $this->csmarketplaceHelper->getStoreConfig('trans_email/ident_general/name');
        $senderEmail = $this->csmarketplaceHelper->getStoreConfig('trans_email/ident_general/email');
        $data['store'] = $this->_storeManager->getStore();
        $transport = $this->_transportBuilder
            ->setTemplateIdentifier($template)
            ->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->_storeManager->getStore()->getId(),
            ])
            ->setTemplateVars($data)
            ->setFrom([
                'name' => $senderName,
                'email' => $senderEmail
            ])
            ->addTo($data['receiver_email'], $data['receiver_name'])
            ->getTransport();
        try {
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }
}
