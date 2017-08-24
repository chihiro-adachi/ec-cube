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

namespace Eccube;

use Eccube\Application\ApplicationTrait;
use Eccube\ServiceProvider\DiServiceProvider;
use Symfony\Component\Yaml\Yaml;

class InstallApplication extends \Silex\Application
{
    use \Silex\Application\FormTrait;
    use \Silex\Application\UrlGeneratorTrait;
    use \Silex\Application\MonologTrait;
    use \Silex\Application\SwiftmailerTrait;
    use \Silex\Application\SecurityTrait;
    use \Eccube\Application\ApplicationTrait;
    use \Eccube\Application\SecurityTrait;
    use \Eccube\Application\TwigTrait;

    public function __construct(array $values = array())
    {
        $app = $this;

        parent::__construct($values);

        $logDir = realpath(__DIR__.'/../../app/log');
        $installLog = $logDir.'/install.log';

        if (is_writable($logDir)) {
            if (file_exists($installLog) && !is_writable($installLog)) {
                die($installLog . ' の書込権限を変更して下さい。');
            }
            // install step2 でログディレクトリに書き込み権限が付与されればログ出力を開始する.
            $app->register(new \Silex\Provider\MonologServiceProvider(), array(
                'monolog.logfile' => $installLog,
            ));
        }

        // load config
        $app['config'] = function() {
            $distPath = __DIR__.'/../../src/Eccube/Resource/config';

            $configConstant = array();
            $constantYamlPath = $distPath.'/constant.yml.dist';
            if (file_exists($constantYamlPath)) {
                $configConstant = Yaml::parse(file_get_contents($constantYamlPath));
            }

            $configLog = array();
            $logYamlPath = $distPath.'/log.yml.dist';
            if (file_exists($logYamlPath)) {
                $configLog = Yaml::parse(file_get_contents($logYamlPath));
            }

            $config = array_replace_recursive($configConstant, $configLog);

            return $config;
        };

        $distPath = __DIR__.'/../../src/Eccube/Resource/config';
        $config_dist = Yaml::parse(file_get_contents($distPath.'/config.yml.dist'));
        if (!empty($config_dist['timezone'])) {
            date_default_timezone_set($config_dist['timezone']);
        }

        $app->register(new \Silex\Provider\SessionServiceProvider());
        $app->register(new \Silex\Provider\TwigServiceProvider(), array(
            'twig.path' => array(__DIR__.'/Resource/template/install'),
            'twig.form.templates' => array('bootstrap_3_horizontal_layout.html.twig'),
        ));

        $this->register(new \Silex\Provider\FormServiceProvider());
        $this->register(new \Silex\Provider\ValidatorServiceProvider());

        $this->register(new \Silex\Provider\TranslationServiceProvider(), array(
            'locale' => 'ja',
        ));
        $app->extend('translator', function($translator, \Silex\Application $app) {
            $translator->addLoader('yaml', new \Symfony\Component\Translation\Loader\YamlFileLoader());

            $r = new \ReflectionClass('Symfony\Component\Validator\Validation');
            $file = dirname($r->getFilename()).'/Resources/translations/validators.'.$app['locale'].'.xlf';
            if (file_exists($file)) {
                $translator->addResource('xliff', $file, $app['locale'], 'validators');
            }

            $file = __DIR__.'/Resource/locale/validator.'.$app['locale'].'.yml';
            if (file_exists($file)) {
                $translator->addResource('yaml', $file, $app['locale'], 'validators');
            }

            $translator->addResource('yaml', __DIR__.'/Resource/locale/ja.yml', $app['locale']);

            return $translator;
        });

        $app->register(new ServiceProvider\InstallServiceProvider());
        $app->register(new \Silex\Provider\CsrfServiceProvider());

        $app->error(function(\Exception $e, $code) use ($app) {
            if ($code === 404) {
                return $app->redirect($app->url('install'));
            } elseif ($app['debug']) {
                return;
            }

            return $app['twig']->render('error.twig', array(
                'error' => 'エラーが発生しました.',
            ));
        });

        $app->register(new DiServiceProvider(), [
            'eccube.di.dirs' => [__DIR__.'/Form/Type/Install'],
            'eccube.di.generator.dir' => __DIR__.'/../../app/cache/provider',
            'eccube.di.generator.class' => 'InstallServiceProviderCache'
        ]);

        $app->match('/', "\\Eccube\\Controller\\Install\\InstallController::index")->bind('install');
        $app->match('/step1', "\\Eccube\\Controller\\Install\\InstallController::step1")->bind('install_step1');
        $app->match('/step2', "\\Eccube\\Controller\\Install\\InstallController::step2")->bind('install_step2');
        $app->match('/step3', "\\Eccube\\Controller\\Install\\InstallController::step3")->bind('install_step3');
        $app->match('/step4', "\\Eccube\\Controller\\Install\\InstallController::step4")->bind('install_step4');
        $app->match('/step5', "\\Eccube\\Controller\\Install\\InstallController::step5")->bind('install_step5');
        $app->match('/complete', "\\Eccube\\Controller\\Install\\InstallController::complete")->bind('install_complete');
        $app->match('/migration', "\\Eccube\\Controller\\Install\\InstallController::migration")->bind('migration');
        $app->match('/migration_plugin', "\\Eccube\\Controller\\Install\\InstallController::migration_plugin")->bind('migration_plugin');
        $app->match('/migration_end', "\\Eccube\\Controller\\Install\\InstallController::migration_end")->bind('migration_end');
    }
}
