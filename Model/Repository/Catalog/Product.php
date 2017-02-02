<?php

/**
 *  Copyright © 2016 Bkademy. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace Bkademy\Webpos\Model\Repository\Catalog;
/**
 * Catalog product model
 *
 * @method Product setHasError(bool $value)
 * @method \Magento\Catalog\Model\ResourceModel\Product getResource()
 * @method null|bool getHasError()
 * @method Product setAssociatedProductIds(array $productIds)
 * @method array getAssociatedProductIds()
 * @method Product setNewVariationsAttributeSetId(int $value)
 * @method int getNewVariationsAttributeSetId()
 * @method int getPriceType()
 * @method \Magento\Catalog\Model\ResourceModel\Product\Collection getCollection()
 * @method string getUrlKey()
 * @method Product setUrlKey(string $urlKey)
 * @method Product setRequestPath(string $requestPath)
 * @method Product setWebsiteIds(array $ids)
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Product extends \Magento\Catalog\Model\Product
    implements \Bkademy\Webpos\Api\Data\Catalog\ProductInterface
{

    /** @var \Magento\Catalog\Model\Product  */
    protected $_product;

    protected $_chidrenCollection;

    public function getProduct()
    {
        if (!$this->_product) {
            $this->_product = $this->load($this->getId());
        }
        return $this->_product;
    }
    
    /**
     * get product type instance
     * @param type $product
     * @return type
     */
    protected function _getProductTypeInstance($product = null){
        if(is_null($product))
            $product = $this;
        $type = '';
        if($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
            $type = 'Magento\ConfigurableProduct\Model\Product\Type\Configurable';
        return \Magento\Framework\App\ObjectManager::getInstance()->get($type);
    }

    /**
     * Product short description
     *
     * @return string|null
     */
    public function getShortDescription()
    {
        return $this->getData(self::SHORT_DESCRIPTION);
    }

    /**
     * Product description
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }


    public function getStock()
    {
        /** @var \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository */
        $stockItemRepository = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magento\CatalogInventory\Model\Stock\StockItemRepository'
        );
        try {
            $stockQty = $stockItemRepository->get($this->getId())->getQty();
            if ($stockQty) {
                return $stockQty;
            } else {
                return 0;
            }

        } catch (\Exception $w) {
            return 0;
        }
    }

    /**
     * Retrieve images
     *
     * @return array/null
     */
    public function getImages()
    {
        $product = $this;//->getProduct();
        $images = [];
        if (!empty($product->getMediaGallery('images'))) {
            foreach ($product->getMediaGallery('images') as $image) {
                if ((isset($image['disabled']) && $image['disabled']) || empty($image['value_id'])) {
                    continue;
                }
                $images[] = $this->getMediaConfig()->getMediaUrl($image['file']);
            }
        }
        return $images;
    }

    /**
     * Sets product image from it's child if possible
     *
     * @return string
     */
    public function getImage()
    {
        $imageString = parent::getImage();
        if ($imageString && $imageString != 'no_selection') {
            return $this->getMediaConfig()->getMediaUrl($imageString);
        } else {
            $storeManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                '\Magento\Store\Model\StoreManagerInterface'
            );
            $url = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            return $url.'webpos/catalog/category/image.jpg';
        }
    }

    /**
     * Get list of product options
     *
     * @return \Magento\Catalog\Api\Data\ProductCustomOptionInterface[]|null
     */
    public function getCustomOptions()
    {
        return $this->getProduct()->getOptions();
    }

    /**
     * Get list of product config options
     *
     * @return \Bkademy\Webpos\Api\Data\Catalog\Product\ConfigOptionsInterface[]|null
     */
    public function getConfigOptions()
    {
        if ($this->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $product = $this;
            $productTypeInstance = $this->_getProductTypeInstance($product);
            $productAttributeOptions = $productTypeInstance->getConfigurableAttributesAsArray($product);
            $options = array();
            foreach ($productAttributeOptions as $productAttributeOption) {
                $values = $productAttributeOption['values'];
                $optionId = $productAttributeOption['attribute_id'];
                $code = $productAttributeOption['attribute_code'];
                $optionLabel = $productAttributeOption['label'];
                $options[$code]['optionId'] = $optionId;
                $options[$code]['optionLabel'] = $optionLabel;
                foreach ($values as $value) {
                    $optionValueId = $value['value_index'];
                    $val = $value['label'];
                    $options[$code][$optionValueId] = $val;
                }
            }
            return $options;

        }
    }

    /**
     * Composes configuration for js
     *
     * @return string
     */
    public function getJsonConfig()
    {
        /** if product is configurable */
        if ($this->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            /** @var \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $configurable */
            $configurable = \Magento\Framework\App\ObjectManager::getInstance()->create(
                '\Magento\ConfigurableProduct\Block\Product\View\Type\Configurable'
            );
            $currentProduct = $this;
            $configurable->setProduct($currentProduct);
            return $configurable->getJsonConfig();
        }
    }

    /**
     * Get JSON encoded configuration array which can be used for JS dynamic
     * price calculation depending on product options
     *
     * @return string
     */
    public function getPriceConfig()
    {
        if ($this->getTypeId() != \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
            return;
        /* @var $product \Magento\Catalog\Model\Product */
        $product = $this;//->getProduct();
        /** @var \Magento\Framework\Locale\FormatInterface $localeFormat */
        $localeFormat = \Magento\Framework\App\ObjectManager::getInstance()->get(
            'Magento\Framework\Locale\FormatInterface'
        );
        /** @var  \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency */
        $priceCurrency = \Magento\Framework\App\ObjectManager::getInstance()->get(
            'Magento\Framework\Pricing\PriceCurrencyInterface'
        );

        /** @var \Magento\Framework\Json\EncoderInterface $jsonEncoder */
        $jsonEncoder = \Magento\Framework\App\ObjectManager::getInstance()->get(
            'Magento\Framework\Json\EncoderInterface'
        );
        if (!$this->hasOptions()) {
            $config = [
                'productId' => $product->getId(),
                'priceFormat' => $localeFormat->getPriceFormat()
            ];
            return $jsonEncoder->encode($config);
        }

        $tierPrices = [];
        $tierPricesList = $product->getPriceInfo()->getPrice('tier_price')->getTierPriceList();
        if(!empty($tierPricesList)){
            foreach ($tierPricesList as $tierPrice) {
                $value = ($tierPrice['price'])?$tierPrice['price']->getValue():0;
                $tierPrices[] = $priceCurrency->convert($value);
            }
        }
        $oldPrice = ($product->getPriceInfo()->getPrice('regular_price')->getAmount())?$product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue():0;
        $basePrice = ($product->getPriceInfo()->getPrice('final_price')->getAmount())?$product->getPriceInfo()->getPrice('final_price')->getAmount()->getBaseAmount():0;
        $finalPrice = ($product->getPriceInfo()->getPrice('final_price')->getAmount())?$product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue():0;

        $config = [
            'productId' => $product->getId(),
            'priceFormat' => $localeFormat->getPriceFormat(),
            'prices' => [
                'oldPrice' => [
                    'amount' => $priceCurrency->convert(
                        $oldPrice
                    ),
                    'adjustments' => []
                ],
                'basePrice' => [
                    'amount' => $priceCurrency->convert(
                        $finalPrice
                    ),
                    'adjustments' => []
                ],
                'finalPrice' => [
                    'amount' => $priceCurrency->convert(
                        $finalPrice
                    ),
                    'adjustments' => []
                ]
            ],
            'idSuffix' => '_clone',
            'tierPrices' => $tierPrices
        ];

        return $jsonEncoder->encode($config);
    }

    /**
     * Return true if product has options
     *
     * @return bool
     */
    public function hasOptions()
    {
        if ($this->getTypeInstance()->hasOptions($this)) {
            return true;
        }

        if ($this->getTypeId() == \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE) {
            return true;
        }

        return false;
    }

    /**
     * Get list of product bundle options
     *
     * @return \Bkademy\Webpos\Api\Data\Catalog\Product\BundleOptionsInterface[]|null
     */
    public function getBundleOptions()
    {
        if ($this->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            $bundleChilds = [];
            $product = $this;
            $store_id = $this->_storeManager->getStore()->getId();
            $options = \Magento\Framework\App\ObjectManager::getInstance()->get(
                'Magento\Bundle\Model\Option'
            )->getResourceCollection()
                ->setProductIdFilter($product->getId())
                ->setPositionOrder();
            $options->joinValues($store_id);
            $typeInstance = \Magento\Framework\App\ObjectManager::getInstance()->get(
                'Magento\Bundle\Model\Product\Type'
            );
            $selections = $typeInstance->getSelectionsCollection($typeInstance->getOptionsIds($product), $product);
            $price_type = $product->getData('price_type');
            foreach ($options->getItems() as $option) {
                $bundleChilds[$option->getId()]['title'] = $option->getTitle();
                $bundleChilds[$option->getId()]['required'] = $option->getRequired();
                $bundleChilds[$option->getId()]['type'] = $option->getType();
                $bundleChilds[$option->getId()]['id'] = $option->getId();
                $bundleChilds[$option->getId()]['product_id'] = $product->getId();
                foreach ($selections as $selection) {
                    $selection_price_type = $selection->getData('selection_price_type');
                    $selection_price_value = $selection->getData('selection_price_value');
                    $price = $selection->getData('price');
                    $selection_price = ($selection_price_type == 0) ? $selection_price_value : $price * $selection_price_value;

                    if ($price_type == 0) {
                        $selection_price = $price;
                    }
                    if ($option->getId() == $selection->getOptionId()) {
                        $bundleChilds[$option->getId()]['items'][$selection->getSelectionId()] = [];
                        $bundleChilds[$option->getId()]['items'][$selection->getSelectionId()] = $selection->getData();
                    }
                }
            };
            return $bundleChilds;
        }
    }

    /**
     * Get list of product grouped options
     *
     * @return \Bkademy\Webpos\Api\Data\Catalog\Product\GroupedOptionsInterface[]|null
     */
    public function getGroupedOptions()
    {
        if ($this->getTypeId() == \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE) {
            $childProducts = [];
            $product = $this;
            $typeInstance = \Magento\Framework\App\ObjectManager::getInstance()->get(
                'Magento\GroupedProduct\Model\Product\Type\Grouped'
            );
            $childs = $typeInstance->getAssociatedProducts($product);
            if (!empty($childs)) {
                foreach ($childs as $child) {
                    $childProducts[$child->getId()]['id'] = $child->getId();
                    $childProducts[$child->getId()]['type_id'] = $child->getTypeId();
                    $childProducts[$child->getId()]['sku'] = $child->getSku();
                    $childProducts[$child->getId()]['name'] = $child->getName();
                    $childProducts[$child->getId()]['price'] = $child->getFinalPrice();
                    $childProducts[$child->getId()]['default_qty'] = $child->getQty();
                    $childProductModel = \Magento\Framework\App\ObjectManager::getInstance()->create(
                        '\Magento\Catalog\Model\Product'
                    )->load($child->getId());
                    $imageString = $childProductModel->getImage();
                    if ($imageString && $imageString != 'no_selection') {
                        $imgSrc = $this->getMediaConfig()->getMediaUrl($imageString);
                    } else {
                        $storeManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                            '\Magento\Store\Model\StoreManagerInterface'
                        );
                        $url = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                        $imgSrc = $url.'webpos/catalog/category/image.jpg';
                    }
                    $childProducts[$child->getId()]['image'] = $imgSrc;
                    $childProducts[$child->getId()]['tax_class_id'] = $child->getData('tax_class_id');
                    /** @var \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository */
                    $stockItemRepository = \Magento\Framework\App\ObjectManager::getInstance()->create(
                        '\Magento\CatalogInventory\Model\Stock\StockItemRepository'
                    );
                    try {
                        $stockData = $stockItemRepository->get($child->getId())->getData();
                        $childProducts[$child->getId()]['stock'] = [$stockData];
                    } catch (\Exception $w) {
                        $childProducts[$child->getId()]['stock'] = [];
                    }
                    $childProducts[$child->getId()]['tier_price'] = $this->getPriceModel()->getTierPrices(
                        \Magento\Framework\App\ObjectManager::getInstance()->create(
                            '\Magento\Catalog\Model\Product'
                        )->load($child->getId())
                    );
                }
            }
            return $childProducts;
        }
    }

    /**
     *
     * @param string $price
     * @return string
     */
    public function formatPrice($price){
        $pricingHelper = \Magento\Framework\App\ObjectManager::getInstance()->get(
            '\Magento\Framework\Pricing\Helper\Data'
        );
        return $pricingHelper->currency($price,true,false);
    }

    /**
     * Retrieve assigned category Ids
     *
     * @return array
     */
    public function getCategoryIds()
    {
        if (!$this->hasData('category_ids')) {
            $wasLocked = false;
            if ($this->isLockedAttribute('category_ids')) {
                $wasLocked = true;
                $this->unlockAttribute('category_ids');
            }
            //$ids = $this->_getResource()->getCategoryIds($this);
            $ids = $this->getShowedCategoryIds();
            $this->setData('category_ids', $ids);
            if ($wasLocked) {
                $this->lockAttribute('category_ids');
            }
        }

        if (is_array($this->_getData('category_ids')) && count($this->_getData('category_ids'))) {
            $catStrings = '';
            foreach ($this->_getData('category_ids') as $catId) {
                $catStrings .= '\'' . $catId . '\'';
            }
            return $catStrings;
        }

        if (!is_array($this->_getData('category_ids')) && count($this->_getData('category_ids'))) {
            return $this->_getData('category_ids');
        }
    }

    /**
     * Retrieve product tax class id
     *
     * @return int
     */
    public function getTaxClassId()
    {
        return $this->getData(self::TAX_CLASS_ID);
    }

    /**
     * @return array
     */
    public function getAttributesToSearch()
    {
        $helper = \Magento\Framework\App\ObjectManager::getInstance()->create(
            'Bkademy\Webpos\Helper\Data'
        );
        $attributeSearch = $helper->getStoreConfig('webpos/product_search/product_attribute');
        return explode(',', $attributeSearch);
    }

    /**
     * @return string
     */
    public function getBarcodeAttribute()
    {
        $helper = \Magento\Framework\App\ObjectManager::getInstance()->create(
            'Bkademy\Webpos\Helper\Data'
        );
        return $helper->getStoreConfig('webpos/product_search/barcode');
    }

    /**
     * get search string to search product
     *
     * @return string
     */
    public function getSearchString()
    {
        $searchString = '';
        $attributesToSearch = $this->getAttributesToSearch();
        if (!empty($attributesToSearch)) {
            foreach ($attributesToSearch as $attribute) {
                if ($this->getData($attribute) && !is_array($this->getData($attribute)))
                    $searchString .= ' '.$this->getData($attribute);
            }
        }
        if ($this->getBarcodeAttribute()) {
            $searchString .= ' '.$this->getBarCodeByProduct();
        }
        return $searchString;
    }

    /**
     * Get barcode options
     *
     * @return \Bkademy\Webpos\Api\Data\Catalog\Product\BarcodeOptionsInterface[]|null
     */
    public function getBarcodeOptions()
    {
        if ($this->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            /** @var \Bkademy\Webpos\Model\Repository\Catalog\Product\Type\Configurable $configurable */
            $configurable = \Magento\Framework\App\ObjectManager::getInstance()->create(
                'Bkademy\Webpos\Model\Repository\Catalog\Product\Type\Configurable'
            );
            $configurable->setProduct($this);
            $currentProduct = $this;

            $helper = \Magento\Framework\App\ObjectManager::getInstance()->get(
                '\Magento\ConfigurableProduct\Helper\Data'
            );
            $options = $helper->getOptions($currentProduct, $configurable->getAllowProducts());
            $attributesData = \Magento\Framework\App\ObjectManager::getInstance()->get(
                'Magento\ConfigurableProduct\Model\ConfigurableAttributeData'
            )->getAttributesData($currentProduct, $options);

            $config = [
                'index' => isset($options['index']) ? $options['index'] : [],
            ];

            if ($currentProduct->hasPreconfiguredValues() && !empty($attributesData['defaultValues'])) {
                $config['defaultValues'] = $attributesData['defaultValues'];
            }
            $config = array_merge($config, $configurable->getAdditionalConfig());
            $productIds = $config['index'];
            $configOptions = $this->getConfigOptions();
            $collection = $this->getChildrenCollection();
            $collection->addFinalPrice();
            //$barcodeAttribute = $this->getBarcodeAttribute();
            $barcodeOptions = [];
            foreach ($collection as $product) {
                $barcode = $this->getBarCodeByProduct($product);
                if (!is_null($barcode) && $barcode != '') {
                    if (!empty($productIds[$product->getId()])) {
                        $label = '';
                        $i = 0;
                        $data = [];
                        foreach ($productIds[$product->getId()] as $id => $value) {
                            foreach ($configOptions as $configOption) {
                                if (isset($configOption[$value])) {
                                    if ($i > 0)
                                        $label .= ', ';
                                    $label .= $configOption[$value];
                                    $i++;
                                    break;
                                }
                            }
                            $data['options'][] = ['id' => $id, 'value' => $value];
                        }
                        $productPrice = $product->getFinalPrice();
                        $productId = $product->getId();
                        $data['product'] = ['product_id' => $productId,'price' => $productPrice];
                        $data['label'] = $label;
                        $barcodeOptions[][trim($barcode)] = $data;
                    }
                }
            }
            return $barcodeOptions;
        }
    }
    
    /**
     * get children collection of configurable product
     * @return type
     */
    public function getChildrenCollection(){
        if(!$this->_chidrenCollection){
            $configurable = \Magento\Framework\App\ObjectManager::getInstance()->create(
                '\Magento\ConfigurableProduct\Model\Product\Type\Configurable'
            );
            $collection = $configurable->getUsedProductCollection($this);
            $collection->addAttributeToSelect($this->getBarcodeAttribute());
            $this->_chidrenCollection = $collection;
        }
        return $this->_chidrenCollection;
    }

    /**
     * get barcode string
     *
     * @return string
     */
    public function getBarcodeString()
    {
        $barcodeString = '';
        if ($this->getBarcodeAttribute() && $this->getBarCodeByProduct()) {
            $barcodeString .= ','.$this->getBarCodeByProduct().',';
        }

        if ($this->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {		
            $collection = $this->getChildrenCollection();
            foreach ($collection as $product) {
                $barcode = $this->getBarCodeByProduct($product);
                if ($barcode) {
                    $barcodeString .= ','.$barcode.',';
                }
            }
        }
        return $barcodeString;
    }

    public function getBarCodeByProduct($product = null){
        $barcodeAttribute = $this->getBarcodeAttribute();
        if(!$product){
            $product = $this;
        }
        $barcode = $product->getData($barcodeAttribute);
        $barcodeObject = new \Magento\Framework\DataObject();
        $barcodeObject->setBarcode($barcode);
        \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Framework\Event\ManagerInterface')->dispatch('webpos_product_get_barcode_after', ['object_barcode' => $barcodeObject,'product'=>$product]);
        $barcode = $barcodeObject->getBarcode();
        return $barcode;
    }
    
    /**
     * get All category ids include anchor cateogries
     * @param type $ids
     * @return type
     */
    public function getShowedCategoryIds(){
        $categoryCollection = $this->getCategoryCollection();
        $categoryIds = $categoryCollection->getAllIds();
        $anchorIds = [];
        foreach($categoryCollection as $category){
            $pathIds = $category->getPathIds();
            array_pop($pathIds);
            $anchorIds = array_unique(array_merge($anchorIds, $pathIds));
        }
        $anchorCollection = \Magento\Framework\App\ObjectManager::getInstance()
            ->create('Magento\Catalog\Model\ResourceModel\Category\Collection')
            ->addFieldToFilter('is_anchor', 1)
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('entity_id', array('in' => $anchorIds));
        return array_unique(array_merge($categoryIds, $anchorCollection->getAllIds()));
    }
    
    public function getIsVirtual() {
        $virtualTypes = [
            'customercredit',
        ];
        if (in_array($this->getTypeId(), $virtualTypes)) {
            return true;
        }
        return false;
    }
    
    /**
     * Product credit value
     *
     * @return string
     */
    public function getCustomercreditValue(){
        if ($this->getTypeId()=== 'customercredit') {
            
            if($this->getStorecreditType() == 1)
                return $this->getStorecreditValue();
            elseif($this->getStorecreditType() == 3){
                return (string)$this->getStorecreditDropdown();
            }
        }
        return;
    }
    
    /**
     * Product credit type
     *
     * @return int
     */
    public function getStorecreditType(){
        if ($this->getTypeId()=== 'customercredit') {
            return $this->getData('storecredit_type');
        }
        return;
    }
    
    /**
     * Product credit rate
     *
     * @return float|null
     */
    public function getStorecreditRate(){
        if ($this->getTypeId()=== 'customercredit') {
            return $this->getData('credit_rate');
        }
        return;
    }
    
    /**
     * Product credit min value
     *
     * @return float|null
     */
    public function getStorecreditMin(){
        if ($this->getTypeId()=== 'customercredit') {
            return $this->getData('storecredit_from');
        }
        return;
    }
    
    /**
     * Product credit max value
     *
     * @return float|null
     */
    public function getStorecreditMax(){
        if ($this->getTypeId()=== 'customercredit') {
            return $this->getData('storecredit_to');
        }
        return;
    }
}
