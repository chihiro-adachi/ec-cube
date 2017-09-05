<?php

namespace Eccube\Templating;

use Symfony\Component\Templating\TemplateReference as BaseTemplateReference;

class TemplateReference extends BaseTemplateReference
{
    public function __construct($controller = null, $action = null, $engine = null)
    {
        $this->parameters = [
            'controller' => $controller,
            'action' => $action,
            'engine' => $engine,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getLogicalName()
    {
        return sprintf(
            '%s/%s.%s',
            $this->parameters['controller'],
            $this->parameters['action'],
            $this->parameters['engine']
        );
    }
}
