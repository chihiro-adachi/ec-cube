<?php

namespace Plugin\Fuga\Controller\Admin;

use Eccube\Controller\AbstractController;
use Plugin\Fuga\Form\Type\Admin\ConfigType;
use Plugin\Fuga\Repository\ConfigRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class ConfigController extends AbstractController
{
    /**
     * @var ConfigRepository
     */
    protected $configRepository;

    /**
     * ConfigController constructor.
     *
     * @param ConfigRepository $configRepository
     */
    public function __construct(ConfigRepository $configRepository)
    {
        $this->configRepository = $configRepository;
    }
    /**
     * @Route("/%eccube_admin_route%/fuga/config", name="plugin_Fuga_config")
     * @Template("@Fuga/admin/config.twig")
     */
    public function index(Request $request)
    {
        $Config = $this->configRepository->get();
        $form = $this->createForm(ConfigType::class, $Config);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $Config = $form->getData();
            $this->entityManager->persist($Config);
            $this->entityManager->flush($Config);
            $this->addSuccess('登録しました。', 'admin');

            return $this->redirectToRoute('plugin_Fuga_config');
        }

        return [
            'form' => $form->createView(),
        ];
    }
}