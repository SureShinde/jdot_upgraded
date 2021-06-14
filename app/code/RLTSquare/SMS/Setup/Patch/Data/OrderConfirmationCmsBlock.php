<?php

namespace RLTSquare\SMS\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Cms\Model\BlockFactory;
use function GuzzleHttp\Promise\all;

/**
 * Class OrderConfirmationCmsBlock
 * @package RLTSquare\SMS\Setup\Patch\Data
 */
class OrderConfirmationCmsBlock implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * OrderConfirmationCmsBlock constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param BlockFactory $blockFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        BlockFactory $blockFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->blockFactory = $blockFactory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $blockSetup = $this->blockFactory->create(['setup' => $this->moduleDataSetup]);

        $confirmationCmsBlock = [
            'title' => 'Customer Order Confirmation',
            'identifier' => 'customer_order_confirmation',
            'content' => '<div class="thankyou-wrapper" style="text-align: center;">
                            <div class="checkout-page-title-wrapper thank-you-page">
                                <div class="icon_thank_you_01"></div>
                                <h1 class="page-title">Thank you for confirming your order</h1>
                                <h2 style="margin-top:25px;margin-bottom:20px;color:red;padding: 0px 10%;font-family:Arial,sans-serif,Helvetica,sans-serif;font-weight:600;font-size:inherit;font-style:inherit;line-height:inherit;letter-spacing:1px;text-align:center;margin:0 0 20px 0;">
                                    Please note that our delivery time during the sale is 5-8 working days however due to COVID-19 unfortunate situation, lockdown &amp; for staff safety our operations and courier operations are disturbed therefore you might expect some additional delay in deliveries. Hope you understand the current situation.
                                </h2>
                            </div>
                            <div class="cmsblock-container">
                                <div class="call-us-header2">
                                    <em class="fa fa-phone"></em>
                                    <span>If you want to make any changes to your order please call us: 021 111 112 111 or Email us: eshop@junaidjamshed.com</span>
                                </div>
                            </div>
                        </div>',
            'is_active' => 1,
            'stores' => [0],
            'sort_order' => 0
        ];

        $blockSetup->setData($confirmationCmsBlock)->save();

        $alreadyConfirmedCmsBlock = [
            'title' => 'Customer Order Already Confirmed',
            'identifier' => 'customer_order_already_confirmed',
            'content' => '<div class="thankyou-wrapper" style="text-align: center;">
                            <div class="checkout-page-title-wrapper thank-you-page">
                                <div class="icon_thank_you_01"></div>
                                <h1 class="page-title">Your Order is already confirmed</h1>
                                <h2 style="margin-top:25px;margin-bottom:20px;color:red;padding: 0px 10%;font-family:Arial,sans-serif,Helvetica,sans-serif;font-weight:600;font-size:inherit;font-style:inherit;line-height:inherit;letter-spacing:1px;text-align:center;margin:0 0 20px 0;">
                                    Please note that our delivery time during the sale is 5-8 working days however due to COVID-19 unfortunate situation, lockdown &amp; for staff safety our operations and courier operations are disturbed therefore you might expect some additional delay in deliveries. Hope you understand the current situation.
                                </h2>
                            </div>
                            <div class="cmsblock-container">
                                <div class="call-us-header2">
                                    <em class="fa fa-phone"></em>
                                    <span>If you want to make any changes to your order please call us: 021 111 112 111 or Email us: eshop@junaidjamshed.com</span>
                                </div>
                            </div>
                        </div>',
            'is_active' => 1,
            'stores' => [0],
            'sort_order' => 0
        ];

        $blockSetup->setData($alreadyConfirmedCmsBlock)->save();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
