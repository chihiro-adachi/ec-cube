<?php

namespace Eccube\Doctrine\EventSubscriber;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Eccube\Application;
use Eccube\Entity\AbstractEntity;

class LoadEventSubscriber implements EventSubscriber
{
    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @param Application $app
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function getSubscribedEvents()
    {
        return [Events::postLoad];
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof AbstractEntity) {
            $entity->setAnnotationReader($this->reader);
        }
    }
}
