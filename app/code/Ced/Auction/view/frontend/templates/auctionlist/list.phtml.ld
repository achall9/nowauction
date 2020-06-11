<!--
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
 * @package     Ced_Auction
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
-->
<div class="page-title-wrapper">
    <h1 class="page-title">
        <span data-ui-id="page-title-wrapper" class="base">Product List</span>
    </h1>
</div>
<div class="products wrapper grid products-grid">
    <ol class="products list items product-items auction-list row">
        <?php
        $timezone = $block->getTimeZone()->getConfigTimezone();
        $auctionDetails = $block->getAuctionProduct();
        $block->updateClosedAuction();
        $enableAuction = $block->getAuctionEnable();
        if ($enableAuction && $auctionDetails) {
        foreach ($auctionDetails as $detail) {
            $block->changeStatus();
            $productId = $detail->getProductId();
            $startTime = $detail->getStartDatetime();
            $endTime = $detail->getEndDatetime();
            $url = $block->getAuctionProductUrl($productId);

            if ($url) {
                if ($detail->getStatus() == 'not started') {
                    $countDownTime = $startTime;
                    ?>

                    <li class="item product product-item col-xl-3 col-lg-4 col-md-4 col-sm-6 col-6" data-product="<?= $productId; ?>"
                        data-counter="<?= $countDownTime; ?>">
                        <div class="product-item-info" data-container="product-grid">

                            <a href="<?php if ($block->getAuctionProductUrl($productId)) {
                                echo $block->getAuctionProductUrl($productId);
                            } ?>"
                               class="product photo product-item-photo" tabindex="-1">
                                <?php $image = $block->getImage($block->getProduct($productId), 'category_page_list'); ?>
                                <?php if ($image) {
                                    echo $image->toHtml();
                                } ?>
                            </a>
                            <div class="product details product-item-details">
                                <input type="hidden" value="1">
                                <strong class="product name product-item-name">


                                    <a class="ced_product-item-link"
                                       href="<?php if ($block->getAuctionProductUrl($productId)) {
                                           echo $block->getAuctionProductUrl($productId);
                                       } ?>">
                                        <?php if ($detail->getProductName()) {
                                            echo $detail->getProductName();
                                        } ?>
                                    </a>
                                </strong>

                                <div class="price-box price-final_price">
                                                <span>
                                                    <span class="price-container price-final_price tax weee">
                                                        <span class="price-wrapper ">
                                                            <span class="price"><?= __('Reserved Price:') ?></span>
                                                        </span>
                                                    </span>
                                                </span>
                                    <?php $startingPrice = $this->helper('Magento\Framework\Pricing\Helper\Data')
                                        ->currency($detail->getStartingPrice(), true, false);
                                    if ($startingPrice) {
                                        echo $startingPrice;
                                    } ?>

                                    <span class="price-wrapper ">
                                                            <span class="price"><?= __('Starts at:') ?></span>
                                                </span>

                                    <div id="countdown_timer" class="auction_timer">
                                        <div class="auction_timer_days"
                                             id="auction_days_<?php if ($productId) {
                                                 echo $productId;
                                             } ?>"></div>
                                        <div class="auction_timer_hours"
                                             id="auction_hours_<?php if ($productId) echo $productId; ?>">
                                        </div>
                                        <div class="auction_timer_minutes"
                                             id="auction_minutes_<?php if ($productId) {
                                                 echo $productId;
                                             } ?>">
                                        </div>
                                        <div class="auction_timer_seconds"
                                             id="auction_seconds_<?php if ($productId) {
                                                 echo $productId;
                                             } ?>">
                                        </div>
                                    </div>
                                </div>

                                <button class="action tocart primary"><?= __('Starts soon') ?>
                                </button>
                            </div>
                        </div>
                    </li>
                    <?php
                } ?>
                    <?php
                if ($detail->getStatus() == 'processing') {
                    $countDownTime = $endTime;
                    ?>
                    <li class="item product product-item col-xl-3 col-lg-4 col-md-4 col-sm-6 col-6" data-product="<?= $productId; ?>"
                        data-counter="<?= $countDownTime; ?>">
                        <div class="product-item-info" data-container="product-grid">

                            <a
                                    href="<?php if ($block->getAuctionProductUrl($productId)) {
                                        echo $block->getAuctionProductUrl($productId);
                                    } ?>"
                                    class="product photo product-item-photo" tabindex="-1">
                                <?php $image = $block->getImage($block->getProduct($productId), 'category_page_list'); ?>
                                <?php if ($image) {
                                    echo $image->toHtml();
                                } ?>
                            </a>
                            <div class="product details product-item-details">
                                <input type="hidden" value="1">
                                <strong class="product name product-item-name">


                                    <a class="ced_product-item-link"
                                       href="<?php if ($block->getAuctionProductUrl($productId)) {
                                           echo $block->getAuctionProductUrl($productId);
                                       } ?>">
                                        <?php if ($detail->getProductName()) {
                                            echo $detail->getProductName();
                                        } ?></a>
                                </strong>

                                <div class="price-box price-final_price">
                                                <span>
                                                    <span class="price-container price-final_price tax weee">
                                                        <span class="price-wrapper ">
                                                            <span class="price"><?= __('Reserved Price:') ?></span>
                                                        </span>
                                                    </span>
                                                </span>
                                    <?php $startingPrice = $this->helper('Magento\Framework\Pricing\Helper\Data')
                                        ->currency($detail->getStartingPrice(), true, false);
                                    if ($startingPrice) {
                                        echo $startingPrice;
                                    } ?>

                                    <span class="price-wrapper ">
                                                            <span class="price"><?= __('Time left:') ?></span>
                                            </span>

                                    <div id="countdown_timer" class="auction_timer">
									<a class="ced_product-item-link"
                                       href="<?php if ($block->getAuctionProductUrl($productId)) {
                                           echo $block->getAuctionProductUrl($productId);
                                       } ?>">
                                    
                                        <div class="auction_timer_days"
                                             id="auction_days_<?php if ($productId) {
                                                 echo $productId;
                                             } ?>"></div>
                                        <div class="auction_timer_hours"
                                             id="auction_hours_<?php if ($productId) {
                                                 echo $productId;
                                             } ?>"></div>
                                        <div class="auction_timer_minutes"
                                             id="auction_minutes_<?php if ($productId) {
                                                 echo $productId;
                                             } ?>"></div>
                                        <div class="auction_timer_seconds"
                                             id="auction_seconds_<?php if ($productId) {
                                                 echo $productId;
                                             } ?>"></div>
                                    </div>
									</a>
                                </div>

                                <button class="action tocart primary"
                                        onclick="window.location.href='<?= $url ?>'"><?= __('Start bidding') ?>
                                </button>
                            </div>
                        </div>
                    </li>

                    <?php
                } ?>

                    <?php
                if ($detail->getStatus() == 'closed') { ?>
                    <li class="item product product-item col-xl-3 col-lg-4 col-md-4 col-sm-6 col-6" data-product="<?= $productId; ?>">
                        <div class="product-item-info" data-container="product-grid">

                            <a href="<?= $block->getAuctionProductUrl($productId) ?>"
                               class="product photo product-item-photo" tabindex="-1">
                                <?php $image = $block->getImage($block->getProduct($productId), 'category_page_list'); ?>
                                <?php if ($image) {
                                    echo $image->toHtml();
                                } ?>
                            </a>
                            <div class="product details product-item-details">
                                <input type="hidden" value="1">
                                <strong class="product name product-item-name">


                                    <a class="ced_product-item-link"
                                       href="<?php if ($block->getAuctionProductUrl($productId)) {
                                           echo $block->getAuctionProductUrl($productId);
                                       } ?>">
                                        <?php if ($detail->getProductName()) {
                                            echo $detail->getProductName();
                                        } ?></a>
                                </strong>

                                <button class="action tocart primary"><?= __('Bidding closed') ?>
                                </button>
                            </div>
                        </div>
                    </li>
                    <?php
                } ?>

                    <?php
                if ($detail->getStatus() == 'cancelled') { ?>
                    <li class="item product product-item col-xl-3 col-lg-4 col-md-4 col-sm-6 col-6" data-product="<?= $productId; ?>">
                        <div class="product-item-info" data-container="product-grid">

                            <a href="<?php if ($block->getAuctionProductUrl($productId)) {
                                echo $block->getAuctionProductUrl($productId);
                            } ?>"
                               class="product photo product-item-photo" tabindex="-1">
                                <?php $image = $block->getImage($block->getProduct($productId), 'category_page_list'); ?>
                                <?php if ($image) {
                                    echo $image->toHtml();
                                } ?>
                            </a>
                            <div class="product details product-item-details">
                                <input type="hidden" value="1">
                                <strong class="product name product-item-name">


                                    <a class="ced_product-item-link"
                                       href="<?php if ($block->getAuctionProductUrl($productId)) {
                                           echo $block->getAuctionProductUrl($productId);
                                       } ?>">
                                        <?php if ($detail->getProductName()) {
                                            echo $detail->getProductName();
                                        } ?></a>
                                </strong>

                                <button class="action tocart primary"><?= __('Bidding cancelled') ?>
                                </button>
                            </div>
                        </div>
                    </li>
                <?php
                }
            }
        } ?>

            <script>
                require(['jquery', 'jquery/ui'], function ($) {
                    $(document).ready(function () {
                        let timezone = '<?= $timezone ?>';
                        console.log('time');
                        console.log(timezone);

                        $('.product-item').each(function (index, value) {
                            console.log(index);
                            console.log(value);
                            let counter = $(this).data('counter');
                            let product = $(this).data('product');
                            if (product !== undefined && product !== null && counter !== undefined && counter !== null)
                                runTimer(counter, product);
                        });

                        function runTimer($countDownTime, productid) {
                            var countDownDate = new Date($countDownTime).getTime();

                            var x = setInterval(function () {
                                let currLocalTime = new Date().toLocaleString("en-US", {timeZone: timezone});
                                let currentLocalTime = new Date(currLocalTime);
                                console.log('Current ' + timezone + ' time: ' + currentLocalTime.toLocaleString());


                                let now = currentLocalTime.getTime();

                                var distance = countDownDate - now;

                                var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                                var seconds = Math.floor((distance % (1000 * 60)) / 1000);
                                //var productid = $productId;

                                console.log(document.getElementById("auction_days_" + productid));
                                document.getElementById("auction_days_" + productid).innerHTML = days + "d: ";
                                document.getElementById("auction_hours_" + productid).innerHTML = hours + "h: ";
                                document.getElementById("auction_minutes_" + productid).innerHTML = minutes + "m: ";
                                document.getElementById("auction_seconds_" + productid).innerHTML = seconds + "s ";

                                if (distance < 0) {
                                    clearInterval(x);
                                    document.getElementById("auction_days_" + productid).innerHTML = 0 + "d: ";
                                    document.getElementById("auction_hours_" + productid).innerHTML = 0 + "h: ";
                                    document.getElementById("auction_minutes_" + productid).innerHTML = 0 + "m: ";
                                    document.getElementById("auction_seconds_" + productid).innerHTML = 0 + "s ";

                                    $.ajax({
                                        type: 'POST',
                                        url: "<?php echo $this->getUrl('auction/startbid/start');?>",
                                        data: {remove: true},
                                        dataType: "json",
                                        success: function (res) {
                                            window.location.reload();
                                        }
                                    });
                                }
                            }, 1000);
                        }
                    });
                });
            </script>
            <?php
        }?>
    </ol>
    <?php if (count($auctionDetails->getData()) == 0) { ?>
        <div><p>Sorry, no auction products are available.</p></div>
    <?php } ?>
</div>


<?php if ($block->getPagerHtml()) { ?>
    <div class="order-products-toolbar toolbar bottom"><?php echo $block->getPagerHtml(); ?></div>
    <?php
} ?>


