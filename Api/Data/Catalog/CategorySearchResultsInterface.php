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
interface CategorySearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get attributes list.
     *
     * @return \Bkademy\Webpos\Api\Data\Catalog\CategoryInterface[]
     */
    public function getItems();

    /**
     * Set attributes list.
     *
     * @param \Bkademy\Webpos\Api\Data\Catalog\CategoryInterface[] $items
     * @return $this
     */
    public function setItems(array $items);

    /**
     * Get first categories
     *
     * @return array
     */
    public function getFirstCategories();

    /**
     * Set total count.
     *
     * @param array $categories
     * @return $this
     */
    public function setFirstCategories($categories);
}
