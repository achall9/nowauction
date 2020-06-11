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
  * @category  Ced
  * @package   Ced_CsOrder
  * @author    CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
  * @license      https://cedcommerce.com/license-agreement.txt
  */
namespace Ced\CsOrder\Helper;

use Magento\Framework\UrlInterface;
use Magento\Framework\App\ObjectManager;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var UrlInterface|null
     */
    private $url;

    /**
     * @var bool|false
     */
    protected $_isSplitOrder = false;

    /**
     * @var \Magento\CoreModel\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\View\Context
     */
    protected $viewContext;

    /**
     * @var \Ced\CsMarketplace\Model\Vendor
     */
    protected $vendor;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $address;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory
     */
    protected $trackCollection;

    /**
     * @var \Ced\CsOrder\Model\ResourceModel\Shipment\CollectionFactory
     */
    protected $shipmentCollection;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param UrlInterface|null $url
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Customer\Model\AddressFactory $address
     * @param \Ced\CsMarketplace\Model\Vendor $vendor
     * @param \Magento\Framework\View\Context $viewContext
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Ced\CsOrder\Model\ResourceModel\Shipment\CollectionFactory $shipmentCollection
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory $trackCollection
     * @param UrlInterface $url
     */
    public function __construct(\Magento\Framework\App\Helper\Context $context,
        UrlInterface $url = null,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\AddressFactory $address,
        \Ced\CsMarketplace\Model\Vendor $vendor,
        \Magento\Framework\View\Context $viewContext,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Customer\Model\Session $customerSession,
        \Ced\CsOrder\Model\ResourceModel\Shipment\CollectionFactory $shipmentCollection,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory $trackCollection
    ) {

        $this->url = $url ?: ObjectManager::getInstance()->get(UrlInterface::class);
        parent::__construct($context);
        $this->_scopeConfigManager = $scopeConfig;
        $this->address = $address;
        $this->vendor = $vendor;
        $this->context = $viewContext;
        $this->request = $request;
        $this->customerSession = $customerSession;
        $this->shipmentCollection = $shipmentCollection;
        $this->trackCollection = $trackCollection;
    }

    /**
     * @param $address
     * @return bool|string
     */
    public function getVendorNameByAddress($address)
    {
        if (is_numeric($address)) {
              $address = $this->address->create()->load($address);
            if($address->getVendorId()) {
                $vendor = $this->vendor->load($address->getVendorId());
                return $vendor->getName();
            }
            else
            {
                return 'Admin';
            }
        } elseif ($address && $address->getId()) {
            $vendor = $this->vendor->load($address->getVendorId());
            return $vendor->getName();
        }else{
            return false;
        }

    }

    /**
     * Check Vendor Log is enabled
     *
     * @return boolean
     */
    public function isVendorLogEnabled()
    {
        return $this->_scopeConfigManager->getValue('ced_csmarketplace/vlogs/active', $this->getStore()->getId());
    }

    /**
     * Get current store
     * @return mixed
     */
    public function getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        if($storeId) {
            return $this->_scopeConfigManager->getStore($storeId);
        }
        else {
            return $this->_scopeConfigManager->getStore();
        }
    }

    /**
     * @param $data
     * @param bool $tag
     */
    public function logProcessedData($data, $tag=false)
    {
        if(!$this->isVendorLogEnabled()) {
            return;
        }

        $controller = $this->context->getControllerName();
        $action = $this->context->getActionName();
        $router = $this->context->getRouteName();
        $module = $this->context->getModuleName();

        $out = '';
        @$out .= "<pre>";
        @$out .= "Controller: $controller\n";
        @$out .= "Action: $action\n";
        @$out .= "Router: $router\n";
        @$out .= "Module: $module\n";
        foreach(debug_backtrace() as $key=>$info)
        {
            @$out .= "#" . $key . " Called " . $info['function'] ." in " . $info['file'] . " on line " . $info['line']."\n";
            break;
        }
        if($tag) {
            @$out .= "#Tag " . $tag."\n";
        }

        @$out .= "</pre>";
    }

    /**
     * @param Exception $e
     */
    public function logException(Exception $e)
    {
        if(!$this->isVendorLogEnabled()) {
            return;
        }

    }

    /**
     * Check Vendor Log is enabled
     *
     * @return boolean
     */
    public function isVendorDebugEnabled()
    {
        $isDebugEnable = (int)$this->_scopeConfigManager->getValue('ced_csmarketplace/vlogs/debug_active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $clientIp = $this->_getRequest()->getClientIp();
        $allow = false;

        if($isDebugEnable ) {
            $allow = true;

            $allowedIps =$this->_scopeConfigManager->getValue('dev/restrict/allow_ips', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if ($isDebugEnable && !empty($allowedIps) && !empty($clientIp)) {
                $allowedIps = preg_split('#\s*,\s*#', $allowedIps, null, PREG_SPLIT_NO_EMPTY);
                if (array_search($clientIp, $allowedIps) === false
                    && array_search($this->request->getHttpHost(), $allowedIps) === false
                ) {
                    $allow = false;
                }
            }
        }

        return $allow;

    }

    /**
     * Check Vendor Log is enabled
     *
     * @return boolean
     */
    public function isSplitOrderEnabled()
    {
        $this->_isSplitOrder = (boolean)$this->_scopeConfigManager->getValue('ced_vorders/general/vorders_mode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $this->_isSplitOrder;
    }

    /**
     * @param $vorder
     * @return bool
     */
    public function canCreateInvoiceEnabled($vorder)
    {
        $isSplitOrderEnable = (boolean)$this->_scopeConfigManager->getValue('ced_vorders/general/vorders_caninvoice', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $isSplitOrderEnable;
    }

    /**
     * @param $vorder
     * @return bool
     */
    public function canCreateShipmentEnabled($vorder)
    {
        if($vorder->canShowShipmentButton()) {
            $isSplitOrderEnable = (boolean)$this->_scopeConfigManager->getValue('ced_vorders/general/vorders_canshipment', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            return $isSplitOrderEnable;
        }
        return false;

    }

    /**
     * @param $vorder
     * @return bool
     */
    public function canCreateCreditmemoEnabled($vorder)
    {
        $isSplitOrderEnable = (boolean)$this->_scopeConfigManager->getValue('ced_vorders/general/vorders_cancreditmemo', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $isSplitOrderEnable;
    }

    /**
     * @param $vorder
     * @return bool
     */
    public function canShowShipmentBlock($vorder)
    {
        if($vorder->getCode()==null) {
            return false;
        }
        return true;
    }

    /**
     * Check Can distribute shipment
     *
     * @return boolean
     */
    public function isActive()
    {
        return (boolean)$this->_scopeConfigManager->getValue('ced_vorders/general/vorders_active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }


    /**
     * @param $order
     * @return bool
     */
    public function isShipmentCreated($order)
    {
        $isCreated = false;
        $vendorId = $this->customerSession->getVendorId();
        if(count($order->getShipmentsCollection())){
            $shipmentId = $order->getShipmentsCollection()->getColumnValues('entity_id');

            $vShipments = $this->shipmentCollection->create()
                            ->addFieldToFilter('shipment_id',array('in'=>$shipmentId))
                            ->addFieldToFilter('vendor_id',$vendorId);
            if(count($vShipments)){
              $isCreated = true;
            }

        }
        return $isCreated;
    }

    /**
     * @param $key
     * @param $model
     * @param string $method
     * @return string
     */
    protected function _getTrackingUrl($key, $model, $method = 'getId')
    {
        $urlPart = "{$key}:{$model->{$method}()}:{$model->getProtectCode()}";

        $params = [
            '_scope' => $model->getStoreId(),
            '_nosid' => true,
            '_direct' => 'shipping/tracking/popup',
            '_query' => ['hash' => $this->urlEncoder->encode($urlPart)]
        ];

        return $this->url->getUrl('', $params);
    }

    /**
     * Shipping tracking popup URL getter
     *
     * @param \Magento\Sales\Model\AbstractModel $model
     * @return string
     */
    public function getTrackingPopupUrlBySalesModel($model)
    {
        $vendorId = $this->customerSession->getVendorId();
        if(count($model->getShipmentsCollection())){
            $shipmentId = $model->getShipmentsCollection()->getColumnValues('entity_id');

            $vShipments = $this->shipmentCollection->create()
                            ->addFieldToFilter('shipment_id',array('in'=>$shipmentId))
                            ->addFieldToFilter('vendor_id',$vendorId);
            if(count($vShipments)){
              $model = $this->trackCollection->create()
                ->addFieldToFilter('parent_id',$vShipments->getFirstItem()->getShipmentId());
                if(count($model)){
                  $model = $model->getFirstItem();
                }
            }
        }
        if ($model instanceof \Magento\Sales\Model\Order) {
            return $this->_getTrackingUrl('order_id', $model);
        } elseif ($model instanceof \Magento\Sales\Model\Order\Shipment) {
            return $this->_getTrackingUrl('ship_id', $model);
        } elseif ($model instanceof \Magento\Sales\Model\Order\Shipment\Track) {
            return $this->_getTrackingUrl('track_id', $model, 'getEntityId');
        }
        return '';
    }
}
