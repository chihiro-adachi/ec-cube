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
        foreach ($defs as $id => $def) {
            $class = $def->getClass();
            foreach ($services as $service) {
                if (0 === strpos($class, $service) || 0 === strpos($id, $service)) {
                    $def->setPublic(true);
                }
            }
        }
    }

    protected function getPublicServices()
    {
        return [
//            'logger',
//            'eccube.logger',
//            'security.encoder_factory',
            'Knp\\Component\\Pager\\PaginatorInterface',
            'Eccube\\Entity\\BaseInfo',
            'Eccube\\Util\\CacheUtil',
            'Eccube\\Security\\Core\\Encoder\\PasswordEncoder',
            //'Eccube\\Service',
            'Eccube\\Repository',
        ];
    }
}
