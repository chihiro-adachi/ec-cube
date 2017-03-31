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

namespace Eccube\Command;

use Doctrine\Common\Annotations\AnnotationReader;
use Eccube\Annotation\EntityExt;
use Knp\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\CS\Finder;


class GenProxyCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('debug:entity-proxy')
            ->setDescription('generate entity proxies');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Eccube\Application $app */
        $app = $this->getSilexApplication();

//        if ($input->getArgument('mode') == 'clean') {
//            // プロキシのクリア
//            $files = Finder::create()
//                ->in($app['config']['root_dir'].'/app/cache/doctrine/entity-proxies')
//                ->name('*.php')
//                ->files();
//            $fs = new Filesystem();
//            foreach ($files as $file) {
//                $output->writeln('remove -> '.$file->getRealPath());
//                unlink($file->getRealPath());
//            }
//            return;
//        }
        // Acmeからファイルを抽出
        $files = Finder::create()
            ->in(
                [
                    $app['config']['root_dir'].'/app/Acme/Entity',
                ]
            )
            ->name('*.php')
            ->files();

        // traitの一覧を取得
        $traits = [];
        $includedFiles = [];
        foreach ($files as $file) {
            require_once $file->getRealPath();
            $includedFiles[] = $file->getRealPath();
        }

        $declared = get_declared_traits();

        foreach ($declared as $className) {
            $rc = new \ReflectionClass($className);
            $sourceFile = $rc->getFileName();
            if (in_array($sourceFile, $includedFiles)) {
                $traits[] = $className;
            }
        }

        // traitから@EntityExtを抽出
        $reader = new AnnotationReader();
        $proxies = [];
        foreach ($traits as $trait) {
            $anno = $reader->getClassAnnotation(new \ReflectionClass($trait), EntityExt::class);
            if ($anno) {
                $proxies[$anno->target][] = $trait;
            }
        }
        // プロキシファイルの生成
        foreach ($proxies as $targetEntity => $traits) {
            $rc = new \Zend\Code\Reflection\ClassReflection($targetEntity);
            $generator
                = \Zend\Code\Generator\ClassGenerator::fromReflection($rc);

            foreach ($traits as $trait) {
                $generator->addTrait('\\'.$trait);
            }

            $generator->setExtendedClass('\\Eccube\\Entity\\AbstractEntity');

            $dir = $app['config']['root_dir'].'/app/cache/doctrine/entity-proxies';
            $file = basename($rc->getFileName());

            $code = $generator->generate();
            file_put_contents($dir.'/'.$file, '<?php '.PHP_EOL.$code);
            $output->writeln('gen -> '.$dir.'/'.$file);


        }
    }
}
