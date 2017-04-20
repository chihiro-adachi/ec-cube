<?php

namespace Eccube\Controller\Block;

class CategoryFooterController
{
    public function index(\Eccube\Application $app)
    {
        $Categories = $app['eccube.repository.category']->getList();

        return $app->render('Block/category_footer.twig', array(
            'Categories' => $Categories
        ));
    }
}
