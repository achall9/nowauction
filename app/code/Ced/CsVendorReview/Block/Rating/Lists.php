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
 * @package   Ced_CsVendorReview
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsVendorReview\Block\Rating;

/**
 * Class Lists
 * @package Ced\CsVendorReview\Block\Rating
 */
class Lists extends \Magento\Framework\View\Element\Template
{
    protected $_vendor;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Ced\CsVendorReview\Model\Review
     */
    protected $review;

    /**
     * @var \Ced\CsVendorReview\Model\Rating
     */
    protected $rating;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    public $messageManager;

    /**
     * Lists constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Ced\CsVendorReview\Model\Review $review
     * @param \Ced\CsVendorReview\Model\Rating $rating
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Ced\CsVendorReview\Model\Review $review,
        \Ced\CsVendorReview\Model\Rating $rating,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->review = $review;
        $this->rating = $rating;
        $this->messageManager = $messageManager;
        $reviews = $this->review->getCollection()
            ->addFieldToFilter('vendor_id', $this->getVendorId())
            ->addFieldToFilter('status', 1)
            ->setOrder('created_at', 'desc');
        $this->setReviews($reviews);
    }

    /**
     * @return $this|\Magento\Framework\View\Element\Template
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $toolbar = $this->getLayout()->getBlock('product_review_list.toolbar');
        if ($toolbar) {
            $toolbar->setCollection($this->getReviews());
            $this->setChild('toolbar', $toolbar);
            $this->getReviews()->load();
        }
        return $this;
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getRatings()
    {
        $rating = $this->rating->getCollection()
            ->setOrder('sort_order', 'asc');
        return $rating;
    }

    /**
     * @return mixed
     */
    public function getVendor()
    {
        if (!$this->_vendor) {
            $this->_vendor = $this->registry->registry('current_vendor');
        }
        return $this->_vendor;
    }

    /**
     * @return mixed
     */
    public function getVendorId()
    {
        return $this->getVendor()->getId();
    }
}
