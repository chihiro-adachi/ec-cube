<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eccube\Controller\Admin\Content;

use Eccube\Controller\AbstractController;
use Eccube\Util\CacheUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class CacheController extends AbstractController
{
    /**
     * @Route("/%eccube_admin_route%/content/cache", name="admin_content_cache")
     * @Template("@admin/Content/cache.twig")
     */
    public function index(Request $request, CacheUtil $cacheUtil)
    {
        $builder = $this->formFactory->createBuilder(FormType::class);
        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cacheUtil->clearCache();

            $this->addSuccess('admin.common.delete_complete', 'admin');
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/content/cache/clear_cache", name="admin_content_cache_clear_cache")
     * @return Response
     */
    public function clearCache(CacheUtil $cacheUtil)
    {
        $cacheUtil->clearCache();
        $this->addSuccess('admin.common.delete_complete', 'admin');

        return new Response();
    }

    /**
     * @Route("/%eccube_admin_route%/content/cache/create_cache", name="admin_content_cache_create_cache")
     * @return Response
     */
    public function createCache(KernelInterface $kernel)
    {
        $console = new Application($kernel);
        $console->setAutoExit(false);

        $command = [
            'command' => 'cache:warmup',
            '--no-optional-warmers' => true,
            '--no-ansi' => true,
        ];

        $input = new ArrayInput($command);

        $output = new BufferedOutput(
            OutputInterface::VERBOSITY_DEBUG,
            true
        );

        $console->run($input, $output);

        return new Response();
    }

    /**
     * @Route("/%eccube_admin_route%/content/cache/enable_maintenance", name="admin_content_cache_enable_maintenance")
     * @return Response
     */
    public function enableMaintenance()
    {
        $projectDir = $this->container->getParameter('kernel.project_dir');
        if (!file_exists($projectDir . '/.maintenance')) {
            touch($projectDir . '/.maintenance');
        }

        return new Response();
    }

    /**
     * @Route("/%eccube_admin_route%/content/cache/disable_maintenance", name="admin_content_cache_disable_maintenance")
     * @return Response
     */
    public function disableMaintenance()
    {
        $projectDir = $this->container->getParameter('kernel.project_dir');
        if (file_exists($projectDir . '/.maintenance')) {
            unlink($projectDir . '/.maintenance');
        }
        return new Response();
    }
}
