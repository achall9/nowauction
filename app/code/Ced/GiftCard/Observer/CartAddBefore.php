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
 * @package     Ced_GiftCard
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\GiftCard\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Event\ObserverInterface;
use Ced\GiftCard\Model\Product\Type\GiftCard;

/**
 * Class CartAddBefore
 * @package Ced\GiftCard\Observer
 */
class CartAddBefore implements ObserverInterface
{

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $encoder;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;

    /**
     * @var \Magento\Framework\App\ResponseFactory
     */
    protected $responseFactory;

    /**
     * @var \Ced\GiftCard\Helper\Image
     */
    protected $imageHelper;


    /**
     * CartAddBefore constructor.
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Url\EncoderInterface $encoder
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \Magento\Framework\App\ResponseFactory $responseFactory
     * @param \Ced\GiftCard\Helper\Image $imageHelper
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Url\EncoderInterface $encoder,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Ced\GiftCard\Helper\Image $imageHelper
    )
    {
        $this->_messageManager = $messageManager;
        $this->productRepository = $productRepository;
        $this->encoder = $encoder;
        $this->redirect = $redirect;
        $this->urlInterface = $urlInterface;
        $this->responseFactory = $responseFactory;
        $this->imageHelper = $imageHelper;
    }

    /**
     * add to cart event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this | bool
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $productId = $observer->getRequest()->getPostValue('product');

            if (!$productId) {
                $productId = $observer->getRequest()->getParam('product');
                if (!$productId) {
                    return $this;
                }
            }
            $product = $this->productRepository->getById($productId);

            if ($product->getTypeId() == GiftCard::TYPE_CODE) {
                /*check if  a required parameter is not filled*/

                $urlEncoder = $this->encoder;

                if ($this->isValid($observer)) {
                    $this->_messageManager->addErrorMessage(__('Please Fill all the values.'));
                    $url = $this->redirect->getRedirectUrl();
                    $url = $this->urlInterface->getUrl('catalog/product/view', ['id' => $product->getId()]);

                    $urlParamName = \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED;

                    $uencURL = $urlEncoder->encode($url);

                    $this->responseFactory->create()
                        ->setRedirect($url)->sendResponse();

                    $observer->getRequest()->setParam($urlParamName, $uencURL);
                    $observer->getRequest()->setParam('product', false);
                } else {
                    $image = $observer->getRequest()->getPostValue('image');
                    if (false === strpos($image, $observer->getRequest()->getDistroBaseUrl())) {
                        $image = $this->retreiveImage($observer->getRequest()->getPostValue('image'), $observer);
                    } else {
                        $image = str_replace($observer->getRequest()->getDistroBaseUrl() . 'pub/media/', '', $image);
                    }

                    $observer->getRequest()->setParam('image', $image);
                    $observer->getRequest()->setPostValue('image', $image);
                }

            }
        } catch (\Exception $e) {
            $observer->getRequest()->setParam('product', false);
        }

        return $this;
    }

    /**
     * @param $observer
     * @return bool
     */
    public function isValid($observer)
    {
        $gift_to_email = $observer->getRequest()->getPostValue('gift_to_email');
        $qty = $observer->getRequest()->getPostValue('qty');
        $gift_to_name = $observer->getRequest()->getPostValue('gift_to_name');
        $giftcard = $observer->getRequest()->getPostValue('giftcard');
        $gift_to_email = (null == $gift_to_email || $gift_to_email == ' ' || !filter_var($gift_to_email, FILTER_VALIDATE_EMAIL));
        $qty = (null == $qty || $qty == ' ' || $qty <= 0);
        $gift_to_name = (null == $gift_to_name || $gift_to_name == ' ');
        $giftcard = (null == $giftcard || $giftcard == ' ' || $giftcard != 'giftcard');

        return $gift_to_email || $qty || $gift_to_name || $giftcard;
    }

    /**
     * @param $image
     * @param $observer
     * @return mixed
     */
    public function retreiveImage($image, &$observer)
    {
        /*url of uploaded image*/
        try {
            $helperImage = $this->imageHelper;
            $image = $helperImage->uploadImageAndGetUrl('custom_image', 'giftcard/tmp/', true);
        } catch (\Exception $e) {
            $this->_messageManager->addErrorMessage(__("Unable To select the giftcard Image, Please Try Again."));
            $observer->getRequest()->setParam('product', false);
        }
        return $image;
    }
}