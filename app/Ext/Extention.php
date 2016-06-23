<?php

namespace Ext;

use Eccube\Application;
use Eccube\Entity\PluginOption;
use Eccube\Event\EccubeEvents;
use Eccube\Event\TemplateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Extention implements EventSubscriberInterface
{
    const PLUGIN_CODE = 'Extension';

    private $app;

    /**
     * Constructor function.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * 商品編集画面にフォーム項目を追加する.
     *
     * @param \Eccube\Event\EventArgs $args
     */
    public function onAdminProductEditInitialize(\Eccube\Event\EventArgs $args)
    {
        // フォーム項目の追加
        $builder = $args->getArgument('builder');
        $builder->add(
            'plg_sales_type',
            'choice',
            array(
                'choices' => array('1' => '有効', '2' => '無効'),
                'data' => '2',
                'label' => '販売対象',
                'mapped' => false,
                'expanded' => true,
                'multiple' => false,
                'constraints' => array(
                    new \Symfony\Component\Validator\Constraints\NotBlank(),
                ),
            )
        );

        // オプションテーブルからデータを取得
        $Product = $args->getArgument('Product');
        if ($Product->getId()) {
            $Option = $this->app['eccube.repository.plugin_option']->findOneBy(
                array(
                    'plugin_code' => self::PLUGIN_CODE,
                    'option_key' => $Product->getId(),
                )
            );

            if (!is_null($Option)) {
                $builder->get('plg_sales_type')->setData($Option->getOptionValue());
            }
        }
    }

    /**
     * 追加項目をデータベースに保存する.
     *
     * @param \Eccube\Event\EventArgs $args
     */
    public function onAdminProductEditComplete(\Eccube\Event\EventArgs $args)
    {
        $Product = $args->getArgument('Product');

        $Option = $this->app['eccube.repository.plugin_option']->findOneBy(
            array(
                'plugin_code' => self::PLUGIN_CODE,
                'option_key' => $Product->getId(),
            )
        );

        if (is_null($Option)) {
            $Option = new PluginOption();
            $Option->setPluginCode(self::PLUGIN_CODE);
            $Option->setOptionKey($Product->getId());
        }

        $form = $args->getArgument('form');
        $salesType = $form['plg_sales_type']->getData();
        $Option->setOptionValue($salesType);

        $this->app['orm.em']->persist($Option);
        $this->app['orm.em']->flush($Option);
    }

    /**
     * 商品詳細画面の表示制御を行う.
     *
     * @param TemplateEvent $event
     */
    public function onRenderProductDetailTwig(TemplateEvent $event)
    {
        $Product = $event->getParameters('Product');
        $Option = $this->app['eccube.repository.plugin_option']->findOneBy(
            array(
                'plugin_code' => self::PLUGIN_CODE,
                'option_key' => $Product->getId(),
            )
        );

        // 表示制御
        $source = $event->getSource();
        $event->setSource($source);
    }

    /**
     * Return the events to subscribe to.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            // 管理：商品登録画面への項目追加
            EccubeEvents::ADMIN_PRODUCT_EDIT_INITIALIZE => 'onAdminProductEditInitialize',
            EccubeEvents::ADMIN_PRODUCT_EDIT_COMPLETE => 'onAdminProductEditComplete',
            // フロント：商品一覧画面の制御
            'Product/detail.twig' => 'onRenderProductDetailTwig',
        );
    }
}
