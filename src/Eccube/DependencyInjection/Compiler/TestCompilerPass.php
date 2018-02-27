<?php


namespace Eccube\DependencyInjection\Compiler;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TestCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $defs = $container->getDefinitions();
        $services = $this->getPublicServices();
        foreach ($defs as $def) {
            $class = $def->getClass();
            foreach ($services as $service) {
                if (0 === strpos($class, $service)) {
                    $def->setPublic(true);
                }
            }
        }
    }

    protected function getPublicServices()
    {
        return [
            'Eccube\\Repository',
        ];
    }
}
