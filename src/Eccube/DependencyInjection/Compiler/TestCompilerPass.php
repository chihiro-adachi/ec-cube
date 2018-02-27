<?php


namespace Eccube\DependencyInjection\Compiler;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TestCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $defs = $container->getDefinitions();
        foreach ($defs as $def) {
            $class = $def->getClass();
            if (0 === strpos($class, 'Eccube\\')) {
                $def->setPublic(true);
            }
        }
    }
}
