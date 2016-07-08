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


namespace Eccube\Controller\Block;

use Eccube\Application;
use Symfony\Component\HttpFoundation\Request;

class LoginController
{
    public function index(Application $app, Request $request)
    {
        $email = $request->cookies->get('email');

        /* @var $form \Symfony\Component\Form\FormInterface */
        $form = $app['form.factory']
            ->createNamedBuilder('', 'customer_login')
            ->getForm();

        $response = $app->render('Block/login.twig', array(
            'error' => $app['security.last_error']($request),
            'email' => $email,
            'form' => $form->createView(),
        ));

        $response->setPublic();
        $response->setETag(md5($response->getContent()));
        $response->isNotModified($request);

        return $response;
    }
}
