<?php

/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace Bkademy\Webpos\Api\Staff;

interface StaffRepositoryInterface
{

    /**
     * @param string $username
     * @param string $password
     * @return int|boolean
     */
    public function login($username, $password);
    
    /**
     *
     * @return boolean
     */
    public function logout();
    
}
