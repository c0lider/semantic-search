<?php

namespace App\Tests\Unit;

use Pimcore\Model\DataObject\Product;
use Pimcore\Test\KernelTestCase;


class ExampleTest extends KernelTestCase
{
    public function testProductSave() {
        self::bootKernel();
        $product = new Product();
        $product->setKey('test-product');
        $product->setParentId(1);
        $this->assertEquals('test-product', $product->getKey());
    }
}
