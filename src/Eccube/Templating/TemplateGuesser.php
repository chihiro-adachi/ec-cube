<?php

namespace Eccube\Templating;

use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\HttpFoundation\Request;

class TemplateGuesser extends \Sergiors\Silex\Templating\TemplateGuesser
{
    public function guessTemplateName($controller, Request $request, $engine = 'twig')
    {
        $className = class_exists('Doctrine\Common\Util\ClassUtils')
            ? ClassUtils::getClass($controller[0])
            : get_class($controller[0]);

        if (!preg_match('/Controller\\\(.+)Controller$/', $className, $matchController)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The "%s" class does not look like a controller class '.
                    '(it must be in a "Controller" sub-namespace and the class name must end with "Controller")',
                    get_class($controller[0])
                )
            );
        }

        return new TemplateReference($matchController[1], $controller[1], $engine);
    }
}
