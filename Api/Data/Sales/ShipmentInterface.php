<?php
/**
 *  Copyright © 2016 Bkademy. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace Bkademy\Webpos\Api\Data\Sales;

interface ShipmentInterface
{
    const ITEMS = 'items';

    /**
     * Gets items for the shipment.
     *
     * @return \Bkademy\Webpos\Api\Data\Sales\ShipmmentInterface[] Array of items.
     */
    public function getItems();

    /**
     * Sets items for the shipment.
     *
     * @param \Bkademy\Webpos\Api\Data\Sales\ShipmmentInterface[] $items
     * @return $this
     */
    public function setItems($items);
}
