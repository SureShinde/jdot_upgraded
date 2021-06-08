<?php
/**
 * @extension   Remmote_Facebookpixelremarketing
 * @author      Remmote
 * @copyright   2017 - Remmote.com
 * @descripion  Helper
 */
namespace Remmote\Facebookpixelremarketing\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    // General Configuration
    const MODULE_ENABLED         = 'remmote_facebookpixelremarketing/general/enabled';
    const PIXEL_ID               = 'remmote_facebookpixelremarketing/general/pixel_id';
    const USE_PRODUCT_ID         = 'remmote_facebookpixelremarketing/general/use_product_id';

    // Facebook Pixel Events
    const VIEW_CONTENT           = 'remmote_facebookpixelremarketing/events/view_content';
    const SEARCH                 = 'remmote_facebookpixelremarketing/events/search';
    const ADD_TO_CART            = 'remmote_facebookpixelremarketing/events/add_to_cart';
    const ADD_TO_WISHLIST        = 'remmote_facebookpixelremarketing/events/add_to_wishlist';
    const INITIATE_CHECKOUT      = 'remmote_facebookpixelremarketing/events/initiate_checkout';
    const ADD_PAYMENT_INFO       = 'remmote_facebookpixelremarketing/events/add_payment_info';
    const PURCHASE               = 'remmote_facebookpixelremarketing/events/purchase';
    const LEAD                   = 'remmote_facebookpixelremarketing/events/lead';
    const COMPLETE_REGISTRATION  = 'remmote_facebookpixelremarketing/events/complete_registration';

    // Facebook Product Catalogs
    const PRODUCTCATALOGURL         = 'remmote_facebookpixelremarketing/product_catalogs/productcatalogurl';
    const EXPORT_ALL                = 'remmote_facebookpixelremarketing/product_catalogs/export_all';
    const USE_PRODUCT_DESCRIPTION   = 'remmote_facebookpixelremarketing/product_catalogs/use_description';
    const EXPORT_NOT_VISIBLE_IND    = 'remmote_facebookpixelremarketing/product_catalogs/export_not_visible_individually';
    const EXTRA_ATTRIBUTES          = 'remmote_facebookpixelremarketing/product_catalogs/extra_attributes';
    const INCLUDE_TAX               = 'remmote_facebookpixelremarketing/product_catalogs/include_tax';
    const EXPORTCATALOG             = 'remmote_facebookpixelremarketing/product_catalogs/exportcatalog';

    // Facebook Scheduled Recurring Uploads
    const SYNC_ENABLED           = 'remmote_facebookpixelremarketing/scheduled_uploads/enabled';
    const CRON_FREQUENCY         = 'remmote_facebookpixelremarketing/scheduled_uploads/frequency';
    const CRON_TIME              = 'remmote_facebookpixelremarketing/scheduled_uploads/time';
    const TIME_LASTEXPORT        = 'remmote_facebookpixelremarketing/scheduled_uploads/time_lastexport';

    /**
     * Get config value from database by store
     * @param  [type] $field
     * @param  [type] $websiteId
     * @return [type]
     * @author Remmote
     * @date   2017-07-18
     */
    public function getConfigValue($field, $websiteId = null)
    {
        if ($websiteId) {
            return $this->scopeConfig->getValue(
                $field,
                \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES,
                $websiteId
            );
        }
        return $this->scopeConfig->getValue($field);
    }

    /**
     * Check if module is enabled and Pixel ID is set
     * @param  [type] $websiteId
     * @return boolean
     * @author Remmote
     * @date   2017-07-18
     */
    public function isEnabled($websiteId = null)
    {
        return $this->getPixelId($websiteId) &&
               $this->getConfigValue(self::MODULE_ENABLED, $websiteId);
    }

    /**
     * Get Pixel ID
     * @param  [type] $websiteId
     * @return [type]
     * @author Remmote
     * @date   2017-07-18
     */
    public function getPixelId($websiteId = null)
    {
        return $this->getConfigValue(self::PIXEL_ID, $websiteId);
    }

    /**
     * Use Product ID instead Product SKU
     * @param  [type] $websiteId
     * @return [type]
     * @author Remmote
     * @date   2017-07-18
     */
    public function getUseProductId($websiteId = null)
    {
        return $this->getConfigValue(self::USE_PRODUCT_ID, $websiteId);
    }

    /**
     * Check if viewContent event is enabled
     * @param  [type] $websiteId
     * @return [type]
     * @author Remmote
     * @date   2017-07-18
     */
    public function viewContentEnabled($websiteId = null)
    {
        return $this->getConfigValue(self::VIEW_CONTENT, $websiteId);
    }

    /**
     * Check if Search event is enabled
     * @param  [type] $websiteId
     * @return [type]
     * @author Remmote
     * @date   2017-07-18
     */
    public function searchEnabled($websiteId = null)
    {
        return $this->getConfigValue(self::SEARCH, $websiteId);
    }

    /**
     * Check if AddToCart event is enabled
     * @param  [type] $websiteId
     * @return [type]
     * @author Remmote
     * @date   2017-07-18
     */
    public function addToCartEnabled($websiteId = null)
    {
        return $this->getConfigValue(self::ADD_TO_CART, $websiteId);
    }

    /**
     * Check if AddToWhislist event is enabled
     * @param  [type] $websiteId
     * @return [type]
     * @author Remmote
     * @date   2017-07-18
     */
    public function addToWishlistEnabled($websiteId = null)
    {
        return $this->getConfigValue(self::ADD_TO_WISHLIST, $websiteId);
    }

    /**
     * Check if InitiateCheckout event is enabled
     * @param  [type] $websiteId
     * @return [type]
     * @author Remmote
     * @date   2017-07-18
     */
    public function initiateCheckoutEnabled($websiteId = null)
    {
        return $this->getConfigValue(self::INITIATE_CHECKOUT, $websiteId);
    }

    /**
     * Check if AddPaymentInfo event is enabled
     * @param  [type] $websiteId
     * @return [type]
     * @author Remmote
     * @date   2017-07-18
     */
    public function addPaymentInfoEnabled($websiteId = null)
    {
        return $this->getConfigValue(self::PURCHASE, $websiteId);
    }

    /**
     * Check if Purchase event is enabled
     * @param  [type] $websiteId
     * @return [type]
     * @author Remmote
     * @date   2017-07-18
     */
    public function purchaseEnabled($websiteId = null)
    {
        return $this->getConfigValue(self::PURCHASE, $websiteId);
    }

    /**
     * Check if Lead event is enabled
     * @param  [type] $websiteId
     * @return [type]
     * @author Remmote
     * @date   2017-07-18
     */
    public function leadEnabled($websiteId = null)
    {
        return $this->getConfigValue(self::LEAD, $websiteId);
    }

    /**
     * Check if CompleteRegistration event is enabled
     * @param  [type] $websiteId
     * @return [type]
     * @author Remmote
     * @date   2017-07-18
     */
    public function completeRegistrationEnabled($websiteId = null)
    {
        return $this->getConfigValue(self::COMPLETE_REGISTRATION, $websiteId);
    }

    /**
     * PRODUCTCATALOGURL function is not necessary
     */

    /**
     * Check if option to export all products is selected
     * @param  [type] $websiteId
     * @return [type]
     * @author Remmote
     * @date   2017-07-18
     */
    public function exportAllEnabled($websiteId = null)
    {
        return $this->getConfigValue(self::EXPORT_ALL, $websiteId);
    }

    /**
     * Check if use product description instead of short description
     * @param  [type]     $website
     * @return [type]
     * @author edudeleon
     * @date   2018-05-10
     */
    public function useProductDescription($websiteId = null)
    {
        return $this->getConfigValue(self::USE_PRODUCT_DESCRIPTION, $websiteId);
    }

    /**
     * Export products not visible individually
     * @param  [type] $websiteId
     * @return [type]
     * @author Remmote
     * @date   2017-07-18
     */
    public function exportProductsNotVisibleIndEnabled($websiteId = null)
    {
        return $this->getConfigValue(self::EXPORT_NOT_VISIBLE_IND, $websiteId);
    }

    /**
     * Return array of extra attributes
     * @param  [type]  $websiteId
     * @param  boolean $arrayFormat
     * @return [type]
     * @author Remmote
     * @date   2017-07-18
     */
    public function getExtraAttributes($websiteId = null, $arrayFormat = true)
    {
        $extraAttributes = $this->getConfigValue(self::EXTRA_ATTRIBUTES, $websiteId);

        if ($arrayFormat && $extraAttributes) {
            $extraAttributes = explode(',', $extraAttributes);
            if (is_array($extraAttributes)) {
                foreach ($extraAttributes as $key => $value) {
                    $extraAttributes[$key] = trim($value);
                }
                return $extraAttributes;
            } else {
                return [];
            }
        }

        return $extraAttributes;
    }

    /**
     * Check if include tax in product price
     * @param  [type] $websiteId
     * @return [type]
     * @author Remmote
     * @date   2017-07-18
     */
    public function includeTax($websiteId = null)
    {
        return $this->getConfigValue(self::INCLUDE_TAX, $websiteId);
    }

    /**
     * EXPORTCATALOG function is not necessary
     */

    /**
     * Check if module is enabled
     * @param  [type]  $websiteId
     * @return boolean
     * @author Remmote
     * @date   2017-07-18
     */
    public function syncEnabled($websiteId = null)
    {
        return $this->getConfigValue(self::SYNC_ENABLED, $websiteId);
    }

    /**
     * Gets the cron frequency cofiguration value
     * @param  [type] $websiteId
     * @return [type]
     * @author Remmote
     * @date   2017-07-18
     */
    public function getCronFrequency($websiteId = null)
    {
        return $this->getConfigValue(self::CRON_FREQUENCY, $websiteId);
    }

    /**
     * Gets the cron time configuration value
     * @param  [type] $websiteId
     * @return [type]
     * @author Remmote
     * @date   2017-07-18
     */
    public function getCronTime($websiteId = null){
        return $this->getConfigValue(self::CRON_TIME, $websiteId);
    }
    
    /**
     * TIME_LASTEXPORT function is not necessary
     */
}