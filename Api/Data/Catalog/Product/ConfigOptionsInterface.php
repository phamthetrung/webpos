<?php

/**
 *  Copyright © 2016 Bkademy. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace Bkademy\Webpos\Api\Data\Catalog\Product;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface ConfigOptionsInterface
 */
interface ConfigOptionsInterface extends ExtensibleDataInterface
{
    /**#@+
     * Config Options object data keys
     */

    const KEY_CONFIG_OPTIONS = 'config_options';

    /**
     * Gets product items of config options
     *
     * @return \Bkademy\Webpos\Api\Data\Catalog\Product\ConfigOptionsInterface
     */
    public function getConfigOptions();

    /**
     * Sets product items of config options
     *
     * @param \Bkademy\Webpos\Api\Data\Catalog\Product\ConfigOptionsInterface $configOptions
     * @return $this
     */
    public function setConfigOptions(\Bkademy\Webpos\Api\Data\Catalog\Product\ConfigOptionsInterface $configOptions);
}
