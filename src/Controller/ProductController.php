<?php

namespace App\Controller;

use App\Form\Type\ProductType;
use App\Service\ProductDtoFactory;
use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\Service;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends FrontendController
{
    private const string PRODUCT_OBJECT_PATH = 'Products';

    public function __construct(
        private readonly ProductDtoFactory $dtoFactory,
    ) {
    }

    public function showSubmitPage(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $key = Service::getValidKey($product->getTitle(), 'object');

            try {
                $productRoot = Service::createFolderByPath(self::PRODUCT_OBJECT_PATH);

                $product
                    ->setKey($key)
                    ->setParent($productRoot)
                    ->save();

                $this->addFlash('success', 'Product saved successfully!');
                return $this->redirect($request->getUri());
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render(
            'documents/product-submission-page.html.twig',
            ['product_form' => $form->createView()]
        );
    }

    #[Route('product/details/{id}', name: 'product_details')]
    public function getDetails(int $id): Response
    {
        $product = Product::getById($id);

        if (!$product instanceof Product) {
            throw $this->createNotFoundException('Product not found');
        }

        $productDto = $this->dtoFactory->createDto($product);

        return $this->render('partials/result-details-modal.html.twig', ['product' => $productDto]);
    }
}
