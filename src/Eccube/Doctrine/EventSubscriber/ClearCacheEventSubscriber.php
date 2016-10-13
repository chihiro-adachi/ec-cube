<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */


namespace Eccube\Doctrine\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Eccube\Application;

class ClearCacheEventSubscriber implements EventSubscriber
{
    private $entities;

    /**
     * @var Application
     */
    private $app;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->entities = array(
            'Eccube\Entity\BaseInfo',
            'Eccube\Entity\PageLayout',
            'Eccube\Entity\BlockPosition',
            'Eccube\Entity\Block',
        );
        $this->groups = array(
            'Eccube\Entity\PageLayout',
            'Eccube\Entity\BlockPosition',
            'Eccube\Entity\Block',
        );
    }

    /**
     * @inheritdoc
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::postUpdate,
            Events::postRemove,
        );
    }

    protected function clearCache(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        foreach ($this->entities as $class) {
            error_log($class);
            if ($entity instanceof $class) {

                $em = $args->getEntityManager();
                $cacheImpl = $em->getConfiguration()->getResultCacheImpl();
                if (in_array($class, $this->groups)) {
                    foreach ($this->groups as $group) {
                        error_log('clear:'.$group);
                        $cacheImpl->setNameSpace($group);
                        $cacheImpl->deleteAll();
                    }
                } else {
                    error_log('clear:'.$class);
                    $cacheImpl->setNameSpace($class);
                    $cacheImpl->deleteAll();
                }

            }
        }
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        error_log("==postRemove==");
        $this->clearCache($args);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        error_log("==postUpdate==");
        $this->clearCache($args);
    }
}
