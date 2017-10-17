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


namespace Eccube\Controller\Admin\Content;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Eccube\Annotation\Inject;
use Eccube\Application;
use Eccube\Controller\AbstractController;
use Eccube\Entity\BlockPosition;
use Eccube\Entity\Layout;
use Eccube\Form\Type\Master\DeviceTypeType;
use Eccube\Repository\BlockRepository;
use Eccube\Repository\LayoutRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints\NotBlank;

// todo プレビュー実装
/**
 * @Route(service=LayoutController::class)
 */
class LayoutController extends AbstractController
{
    /**
     * @Inject(BlockRepository::class)
     * @var BlockRepository
     */
    protected $blockRepository;

    /**
     * @Inject("orm.em")
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @Inject(LayoutRepository::class)
     * @var LayoutRepository
     */
    protected $layoutRepository;

    /**
     * @Route("/{_admin}/content/layout_pc", name="admin_content_layout_pc")
     * @Template("Content/layout_all.twig")
     */
    public function indexPc()
    {
        return [
            'sub_title' => '💻PC'
        ];
    }

    /**
     * @Route("/{_admin}/content/layout_mobile", name="admin_content_layout_mobile")
     * @Template("Content/layout_all.twig")
     */
    public function indexMobile()
    {
        return [
            'sub_title' => '📱モバイル'
        ];
    }

    /**
     * @Route("/{_admin}/content/layout", name="admin_content_layout")
     * @Template("Content/layout_list.twig")
     */
    public function index(Application $app, Request $request)
    {
        $Layouts = $this->layoutRepository->findBy([], ['id' => 'DESC']);

        return [
            'Layouts' => $Layouts,
        ];
    }

    /**
     * @Method("DELETE")
     * @Route("/{_admin}/content/layout/{id}/delete", requirements={"id" = "\d+"}, name="admin_content_layout_delete")
     */
    public function delete(Application $app, Request $request, $id)
    {
        $this->isTokenValid($app);

        $Layout = $this->layoutRepository->find($id);
        if (!$Layout) {
            $app->deleteMessage();

            return $app->redirect($app->url('admin_content_layout'));
        }

        $this->entityManager->remove($Layout);
        $this->entityManager->flush($Layout);


        $app->addSuccess('admin.delete.complete', 'admin');

        return $app->redirect($app->url('admin_content_layout'));
    }

    /**
     * @Route("/{_admin}/content/layout/new", name="admin_content_layout_new")
     * @Route("/{_admin}/content/layout/{id}/edit", requirements={"id" = "\d+"}, name="admin_content_layout_edit")
     * @Template("Content/layout.twig")
     */
    public function edit(Application $app, Request $request, $id = null)
    {
        if (is_null($id)) {
            $Layout = new Layout();
        } else {
            // todo レポジトリへ移動
            try {
                $Layout = $this->layoutRepository->createQueryBuilder('l')
                    ->select('l, bp, b')
                    ->leftJoin('l.BlockPositions', 'bp')
                    ->leftJoin('bp.Block', 'b')
                    ->where('l.id = :layout_id')
                    ->orderBy('bp.block_row', 'ASC')
                    ->setParameter('layout_id', $id)
                    ->getQuery()
                    ->getSingleResult();
            } catch (NoResultException $e) {
                throw new NotFoundHttpException();
            }
        }

        // todo レポジトリへ移動
        // 未使用ブロックの取得
        $Blocks = $Layout->getBlocks();
        if (empty($Blocks)) {
            $UnusedBlocks = $this->blockRepository->findAll();
        } else {
            $UnusedBlocks = $this->blockRepository
                ->createQueryBuilder('b')
                ->select('b')
                ->where('b not in (:blocks)')
                ->setParameter('blocks', $Blocks)
                ->getQuery()
                ->getResult();
        }

        $builder = $app->form($Layout);
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'constraints' => [
                        new NotBlank(),
                    ],
                    'required' => false,
                    'label' => 'レイアウト名',
                ]
            )->add(
                'DeviceType',
                DeviceTypeType::class,
                [
                    'constraints' => [
                        new NotBlank(),
                    ],
                    'required' => false,
                ]
            );

        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Layoutの更新
            $Layout = $form->getData();
            $this->entityManager->persist($Layout);
            $this->entityManager->flush($Layout);

            // BlockPositionの更新
            // delete/insertのため、一度削除する.
            $BlockPositions = $Layout->getBlockPositions();
            foreach ($BlockPositions as $BlockPosition) {
                $Layout->removeBlockPosition($BlockPosition);
                $this->entityManager->remove($BlockPosition);
                $this->entityManager->flush($BlockPosition);
            }

            // ブロックの個数分登録を行う.
            $max = count($Blocks) + count($UnusedBlocks);
            $data = $request->request->all();
            for ($i = 0; $i < $max; $i++) {
                // block_idが取得できない場合はinsertしない
                if (!isset($data['block_id_'.$i])) {
                    continue;
                }
                // 未使用ブロックはinsertしない
                if ($data['target_id_'.$i] == \Eccube\Entity\Page::TARGET_ID_UNUSED) {
                    continue;
                }
                $Block = $this->blockRepository->find($data['block_id_'.$i]);
                $BlockPosition = new BlockPosition();
                $BlockPosition
                    ->setBlockId($data['block_id_'.$i])
                    ->setLayoutId($Layout->getId())
                    ->setBlockRow($data['block_row_'.$i])
                    ->setTargetId($data['target_id_'.$i])
                    ->setBlock($Block)
                    ->setLayout($Layout);
                $Layout->addBlockPosition($BlockPosition);
                $this->entityManager->persist($BlockPosition);
                $this->entityManager->flush($BlockPosition);
            }

            $app->addSuccess('admin.register.complete', 'admin');

            return $app->redirect($app->url('admin_content_layout_edit', array('id' => $Layout->getId())));
        }

        return [
            'form' => $form->createView(),
            'Layout' => $Layout,
            'UnusedBlocks' => $UnusedBlocks,
        ];
    }

    /**
     * @Method("POST")
     * @Route("/{_admin}/content/layout/view_block", name="admin_content_layout_view_block")
     */
    public function viewBlock(Application $app, Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }

        $id = $request->get('id');

        if (is_null($id)) {
            throw new BadRequestHttpException();
        }

        $Block = $this->blockRepository->find($id);

        if (is_null($Block)) {
            return $app->json('みつかりませんでした');
        }

        // ブロックのソースコードの取得.
        $file = $this->blockRepository->getReadTemplateFile($Block->getFileName());
        $source = $file['tpl_data'];

        return $app->json([
            'id' => $Block->getId(),
            'source' => $source,
        ]);
    }
}
