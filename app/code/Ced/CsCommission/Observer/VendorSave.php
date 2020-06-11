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
 * @package     Ced_CsCommission
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsCommission\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Event\Observer;

/**
 * Class VendorSave
 * @package Ced\CsCommission\Observer
 */
class VendorSave implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var Session
     */
    protected $_authSession;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * VendorSave constructor.
     * @param ObjectManagerInterface $objectManager
     * @param RequestInterface $request
     * @param Session $authSession
     * @param ResponseInterface $response
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        RequestInterface $request,
        Session $authSession,
        ResponseInterface $response
    )
    {
        $this->_objectManager = $objectManager;
        $this->request = $request;
        $this->_authSession = $authSession;
        $this->response = $response;
    }

    /**
     * Adds catalog categories to top menu
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        if ($this->_authSession->isLoggedIn()) {
            $params = $this->request->getPost();
            $params['section'] = 'ced_csmarketplace';
            $params['is_csgroup'] = 2;
            $response = json_decode(json_encode($params), 1);
            $this->request->setParams($response);
            $configuration = $this->_objectManager->get(
                'Magento\Config\Controller\Adminhtml\System\Config\Save',
                $this->request,
                $this->response
            );
            $configuration->dispatch($this->request);
        }
        return $this;
    }
}
