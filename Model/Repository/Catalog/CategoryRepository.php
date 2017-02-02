<?php

/**
 *  Copyright © 2016 Bkademy. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace Bkademy\Webpos\Model\Repository\Catalog;


/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class CategoryRepository extends \Magento\Catalog\Model\CategoryRepository
    implements \Bkademy\Webpos\Api\Catalog\CategoryRepositoryInterface
{

    /**
     * Get category list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Bkademy\Webpos\Api\Data\Catalog\CategorySearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $collection = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Bkademy\Webpos\Model\ResourceModel\Catalog\Category\Collection'
        );
        $collection->addAttributeToSelect('name');
        $collection->addAttributeToSelect('image');
        $collection->addAttributeToSelect('path');
        $collection->addAttributeToSelect('parent_id');
        $collection->addAttributeToSelect('is_active');
        $collection->addAttributeToFilter(\Magento\Catalog\Model\Category::KEY_IS_ACTIVE, '1');
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->load();
        $searchResult = \Magento\Framework\App\ObjectManager::getInstance()->get(
            '\Bkademy\Webpos\Api\Data\Catalog\CategorySearchResults'
        );
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        return $searchResult;
    }
}
