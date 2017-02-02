<?php
/**
 *  Copyright © 2016 Bkademy. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace Bkademy\Webpos\Api\Data\Catalog;

/**
 * @api
 */
interface ProductInterface
    //extends ExtensibleDataInterface
//\Magento\Framework\Api\CustomAttributesDataInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */

    const SPECIAL_PRICE = 'special_price';
    const SPECIAL_FROM_DATE = 'special_from_date';
    const SPECIAL_TO_DATE = 'special_to_date';
    const SHORT_DESCRIPTION = 'short_description';
    const DESCRIPTION = 'description';
    const MEDIA_GALLERY = 'media_gallery';
    const TAX_CLASS_ID = 'tax_class_id';

    /**#@-*/

    /**
     * Product id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Product type id
     *
     * @return string|null
     */
    public function getTypeId();

    /**
     * Product sku
     *
     * @return string
     */
    public function getSku();

    /**
     * Product name
     *
     * @return string|null
     */
    public function getName();

    /**
     * Product price
     *
     * @return float|null
     */
    public function getPrice();

    /**
     * Product final price
     *
     * @return float|null
     */
    public function getFinalPrice();

    /**
     * Product special price
     *
     * @return float|null
     */
    public function getSpecialPrice();

    /**
     * Product special price from date
     *
     * @return string|null
     */
    public function getSpecialFromDate();

    /**
     * Product special price to date
     *
     * @return string|null
     */
    public function getSpecialToDate();


    /**
     * Product short description
     *
     * @return string|null
     */
    public function getShortDescription();

    /**
     * Product description
     *
     * @return string|null
     */
    public function getDescription();

    /**
     * Product status
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Product updated date
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Product weight
     *
     * @return float|null
     */
    public function getWeight();

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\ProductExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Catalog\Api\Data\ProductExtensionInterface $extensionAttributes);

     /**
     * Get list of product options
     *
     * @return \Magento\Catalog\Api\Data\ProductCustomOptionInterface[]|null
     */
    public function getCustomOptions();

    /**
     * Get category ids by product
     *
     * @return array/null
     */
    public function getCategoryIds();

    /**
     * Sets product image from it's child if possible
     *
     * @return string
     */
    public function getImage();

    /**
     * Retrieve images
     *
     * @return array/null
     */
    public function getImages();

    /**
     * Retrieve image
     *
     * @return string/null
     */
    //public function getImage();
    
    /**
     * Get stock data by product
     *
     * @return array/null
     */
    public function getStock();

    /**
     * Gets list of product tier prices
     *
     * @return \Magento\Catalog\Api\Data\ProductTierPriceInterface[]|null
     */
    public function getTierPrices();

    /**
     * Get list of product config options
     *
     * @return \Bkademy\Webpos\Api\Data\Catalog\Product\ConfigOptionsInterface[]|null
     */
    public function getConfigOptions();

    /**
     * Get list of product bundle options
     *
     * @return \Bkademy\Webpos\Api\Data\Catalog\Product\BundleOptionsInterface[]|null
     */
    public function getBundleOptions();

    /**
     * Get list of product grouped options
     *
     * @return \Bkademy\Webpos\Api\Data\Catalog\Product\GroupedOptionsInterface[]|null
     */
    public function getGroupedOptions();

    /**
     * Composes configuration for js
     *
     * @return string
     */
    public function getJsonConfig();

    /**
     * Get JSON encoded configuration array which can be used for JS dynamic
     * price calculation depending on product options
     *
     * @return string
     */
    public function getPriceConfig();

    /**
     * Retrieve product tax class id
     *
     * @return int
     */
    public function getTaxClassId();

    /**
     * Retrieve product has option
     *
     * @return int
     */
    public function hasOptions();

    /**
     * get search string to search product
     *
     * @return string
     */
    public function getSearchString();

    /**
     * Get barcode options
     *
     * @return \Bkademy\Webpos\Api\Data\Catalog\Product\BarcodeOptionsInterface[]|null
     */
    public function getBarcodeOptions();

    /**
     * get barcode string
     *
     * @return string
     */
    public function getBarcodeString();
    
    /**
     * get is virtual
     *
     * @return string
     */
    public function getIsVirtual();
    
    /**
     * Product credit value
     *
     * @return string
     */
    public function getCustomercreditValue();
    
    /**
     * Product credit value
     *
     * @return int
     */
    public function getStorecreditType();
    
    /**
     * Product credit value
     *
     * @return float|null
     */
    public function getStorecreditRate();
    
    /**
     * Product credit min value
     *
     * @return float|null
     */
    public function getStorecreditMin();
    
    /**
     * Product credit max value
     *
     * @return float|null
     */
    public function getStorecreditMax();
    
}
