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


namespace Eccube\Controller;

use Eccube\Application;

class TopController
{

    public function index(Application $app)
    {
        return $app->render('index.twig');
    }


    public function tran1(Application $app)
    {
        // update 1
        $BaseInfo = $app['eccube.repository.base_info']->get();
        $BaseInfo->setCompanyName(__LINE__);
        $app['orm.em']->flush($BaseInfo);

        return $app->render('index.twig');
    }

    public function tran2(Application $app)
    {
        // update 1
        $BaseInfo = $app['eccube.repository.base_info']->get();
        $BaseInfo->setCompanyName(__LINE__);
        $app['orm.em']->flush($BaseInfo);

        // update 2
        $BaseInfo->setCompanyName(__LINE__);
        $app['orm.em']->flush($BaseInfo);

        // 1/2 は rollback.
        throw new \Exception();

        return $app->render('index.twig');
    }

    public function tran3(Application $app)
    {
        $app['orm.em']->beginTransaction();

        try {
            $BaseInfo = $app['eccube.repository.base_info']->get();
            $BaseInfo->setCompanyName(__LINE__);
            $app['orm.em']->flush($BaseInfo);
            $app['orm.em']->commit();

        } catch (\Exception $e) {
            $app['orm.em']->rollback();
        }

        return $app->render('index.twig');
    }

    public function tran4(Application $app)
    {
        $app['orm.em']->beginTransaction();

        try {
            // update 1
            $BaseInfo = $app['eccube.repository.base_info']->get();
            $BaseInfo->setCompanyName(__LINE__);
            $app['orm.em']->flush($BaseInfo);
            $app['orm.em']->commit();

            // update 1 は rollback
            throw new \Exception();

        } catch (\Exception $e) {
            $app['orm.em']->rollback();
        }

        return $app->render('index.twig');
    }

    public function tran5(Application $app)
    {
        $app['orm.em']->beginTransaction();

        try {
            // update 1
            $BaseInfo = $app['eccube.repository.base_info']->get();
            $BaseInfo->setCompanyName(__LINE__);
            $app['orm.em']->flush($BaseInfo);
            $app['orm.em']->commit();

        } catch (\Exception $e) {
            $app['orm.em']->rollback();
        }

        $app['orm.em']->beginTransaction();

        try {
            // update 2
            $BaseInfo = $app['eccube.repository.base_info']->get();
            $BaseInfo->setCompanyName(__LINE__);
            $app['orm.em']->flush($BaseInfo);
            $app['orm.em']->commit();

        } catch (\Exception $e) {
            $app['orm.em']->rollback();
        }

        // update1/2はrollback
        throw new \Exception();

        return $app->render('index.twig');
    }

    public function tran6(Application $app)
    {
        $app['orm.em']->beginTransaction();

        try {
            // update 1
            $BaseInfo = $app['eccube.repository.base_info']->get();
            $BaseInfo->setCompanyName(__LINE__);
            $app['orm.em']->flush($BaseInfo);
            $app['orm.em']->commit();

        } catch (\Exception $e) {
            $app['orm.em']->rollback();
        }

        // update 2
        $BaseInfo->setCompanyName(__LINE__);
        $app['orm.em']->flush($BaseInfo);

        // update 3
        $BaseInfo->setCompanyName(__LINE__);
        $app['orm.em']->flush($BaseInfo);

        // update1/2/3 すべてrollback
        throw new \Exception();

        return $app->render('index.twig');
    }

    public function tran7(Application $app)
    {
        $app['orm.em']->beginTransaction();

        try {
            // update 1
            $BaseInfo = $app['eccube.repository.base_info']->get();
            $BaseInfo->setCompanyName(__LINE__);
            $app['orm.em']->flush($BaseInfo);
            $app['orm.em']->commit();

            // update 1がrollback
            throw new \Exception();

        } catch (\Exception $e) {
            // update 1がrollback
            $app['orm.em']->rollback();
        }

        // update 2
        $BaseInfo->setCompanyName(__LINE__);
        $app['orm.em']->flush($BaseInfo);

        // update 3
        $BaseInfo->setCompanyName(__LINE__);
        $app['orm.em']->flush($BaseInfo);

        // update2/3 は 暗黙のtransaction block 内のため、まとめて更新される.
        return $app->render('index.twig');
    }

    public function tran8(Application $app)
    {
        $app['orm.em']->beginTransaction();

        try {
            // update 1
            $BaseInfo = $app['eccube.repository.base_info']->get();
            $BaseInfo->setCompanyName(__LINE__);
            $app['orm.em']->flush($BaseInfo);
            $app['orm.em']->commit();

            // update 1がrollback
            throw new \Exception();

        } catch (\Exception $e) {
            // update 1がrollback
            $app['orm.em']->rollback();
        }

        // update 2
        $BaseInfo->setCompanyName(__LINE__);
        $app['orm.em']->flush($BaseInfo);

        // update 3
        $BaseInfo->setCompanyName(__LINE__);
        $app['orm.em']->flush($BaseInfo);

        // update2/3 は 暗黙のtransaction block内のため、2/3はrollbackされる
        throw new \Exception();

        return $app->render('index.twig');
    }
}
