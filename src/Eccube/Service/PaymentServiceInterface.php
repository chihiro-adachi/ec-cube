<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eccube\Service;

use Eccube\Service\Payment\PaymentDispatcher;
use Eccube\Service\Payment\PaymentMethod;
use Eccube\Service\Payment\PaymentResult;

interface PaymentServiceInterface
{
    /**
     * @return PaymentDispatcher
     */
    public function dispatch(PaymentMethod $method);

    /**
     * @return PaymentResult
     */
    public function doVerify(PaymentMethod $method);

    /**
     * @return PaymentResult
     */
    public function doCheckout(PaymentMethod $method);
}
