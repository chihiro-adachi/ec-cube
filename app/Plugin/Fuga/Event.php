<?php

namespace Plugin\Fuga;

use Eccube\Event\TemplateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Event implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            '@admin/Customer/edit.twig' => 'onAdminCustomerEdit'
        ];
    }

    /**
     * @param TemplateEvent $event
     */
    public function onAdminCustomerEdit(TemplateEvent $event)
    {
        $event->addSnippet("{{ include('@Fuga/hello.twig') }}");
    }
}
