<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\OgTags\Block\Catalog;

use Magefan\OgTags\Block\AbstractOg;

/**
 * Class Product
 * @package Magefan\OgTags\Block\Catalog
 */
class Product extends AbstractOg
{
    /**
     * @var string
     */
    protected $entityType = 'product';

    /**
     * @return string
     */
    public function getOgType()
    {
        return 'product';
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAdditionalAttributes()
    {
        $product = $this->getEntity();
        $stock = 'outofstock';
        if ($product->isAvailable()) {
            $stock = 'instock';
        }
        
        
        $additional =  '<meta property="product:price:amount" content="' . /* @escapeNotVerified */ $this->getEntity()->getFinalPrice() . '"/>' . PHP_EOL;
        $additional .= '<meta property="product:price:currency" content="' . /* @escapeNotVerified */ $this->stripTags($this->getCurrentCurrencyCode()) . '"/>' . PHP_EOL;
        $additional .= '<meta property="product:availability" content="' . /* @escapeNotVerified */ $stock . '"/>' . PHP_EOL;
        $additional .= '<meta property="product:retailer_item_id" content="' . /* @escapeNotVerified */ $product->getSku() . '"/>' . PHP_EOL;
        
        return $additional;
    }

    /**
     * @return mixed
     */
    public function getOgImage()
    {
        $ogImage = $this->getEntity()->getMagefanOgImage() ?: $this->getEntity()->getImage();
        if ($ogImage && $ogImage != 'no_selection') {
            return  $this->getMediaUrl('catalog/product' . $ogImage);
        } else {
            return $this->getDefaultOgImage();
        }
    }

    /**
     * @return mixed
     */
    public function getOgImagePath()
    {
        $ogImage = $this->getEntity()->getMagefanOgImage() ?: $this->getEntity()->getImage();
        if ($ogImage && $ogImage != 'no_selection') {
            return  $this->getMediaPath('catalog/product' . $ogImage);
        } else {
            return $this->getDefaultOgImagePath();
        }
    }
}
