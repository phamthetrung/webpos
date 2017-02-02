<?php
/**
 *  Copyright © 2016 Bkademy. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace Bkademy\Webpos\Api\Data\Catalog;

interface SwatchResultInterface
{
    /**
     * Set swatch list.
     *
     * @api
     * @param array
     * @return $this
     */
    public function setItems(array $items);

    /**
     * Get swatch list.
     *
     * @api
     * @return array
     */
    public function getItems();

    /**
     * Set total count
     *
     * @param int $count
     * @return $this
     */
    public function setTotalCount($count);

    /**
     * Get total count
     *
     * @return int
     */
    public function getTotalCount();

}
