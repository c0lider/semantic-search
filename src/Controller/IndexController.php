<?php

namespace App\Controller;

use Pimcore\Controller\FrontendController;
use Symfony\Bridge\Twig\Attribute\Template;

class IndexController extends FrontendController
{
    #[Template(template: 'documents/index.html.twig')]
    public function indexAction(): array
    {
        return [];
    }
}
