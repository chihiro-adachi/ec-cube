<?php

namespace Eccube\Controller\Block;

class CategoryFooterController
{
    public function index(\Eccube\Application $app)
    {
        return $app->render('Block/category_footer.twig');
    }
}
