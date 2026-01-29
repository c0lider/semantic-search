<?php

use CmsIg\Seal\Schema\Index;
use CmsIg\Seal\Schema\Field;

return new Index('product', [
        'id' => new Field\IdentifierField('id'),
        'title' => new Field\TextField('title'),
        'brand' => new Field\TextField('brand'),
        'description' => new Field\TextField('description'),
        'tags' => new Field\TextField('tags', multiple: true, filterable: true),
        'rating' => new Field\FloatField('rating', filterable: true),
        'price' => new Field\FloatField('price', filterable: true),
        'discountPercentage' => new Field\FloatField('discountPercentage', filterable: true),
        'stock' => new Field\IntegerField('stock', filterable: true),
        'warrantyInfo' => new Field\TextField('warrantyInfo'),
        'reviews' => new Field\ObjectField('reviews', [
            'rating' => new Field\FloatField('rating'),
            'comment' => new Field\TextField('comment'),
            'date' => new Field\DateTimeField('date'),
            'reviewerName' => new Field\TextField('reviewerName'),
        ]),
        'embedding' => new Field\FloatField('embedding', multiple: true, filterable: true),
    ]
);
