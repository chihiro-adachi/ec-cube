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

namespace Eccube\DependencyInjection\Compiler;

use Eccube\Service\Payment\Method\CreditCard;
use Eccube\Service\Payment\PaymentMethod;
use Eccube\Service\PaymentServiceInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * プラグインが作成するPaymentServiceおよびPaymentMethodをpublicに設定する.
 * 本体側からは$container->get(CreditCard::class)のように呼び出されるため.
 */
class PaymentServicePass implements CompilerPassInterface
{
    const TAG = 'eccube.payment.service';

    public function process(ContainerBuilder $container)
    {
        $ids = $container->findTaggedServiceIds(self::TAG);

        foreach ($ids as $id => $tags) {
            $def = $container->getDefinition($id);
            $class = $container->getParameterBag()->resolveValue($def->getClass());

            if (is_subclass_of($class, PaymentServiceInterface::class)
            || is_subclass_of($class, PaymentServiceInterface::class)
            || is_subclass_of($class, CreditCard::class)) {
                $def->setPublic(true);
            } else {
                throw new \InvalidArgumentException(
                    sprintf('Service "%s" must implement interface "%s or %s".', $id,
                        PaymentServiceInterface::class, PaymentMethod::class));
            }
        }
    }
}
