<?php


namespace Eccube\DependencyInjection\Compiler;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TestCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $defs = $container->getDefinitions();
        $patterns = $this->getPatterns();
        foreach ($defs as $def) {
            $class = $def->getClass();
            foreach ($patterns as $pattern) {
                if (0 === strpos($class, $pattern)) {
                    $def->setPublic(true);
                }
            }
        }
    }

    protected function getPattern()
    {
        return [
            'Eccube\\Repository',
        ];
    }
}
