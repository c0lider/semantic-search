<?php

use CmsIg\Seal\Schema\Index;
use CmsIg\Seal\Schema\Field;

return new Index('product', [
        'id' => new Field\IdentifierField('id'),
        'name' => new Field\TextField('name'),
        'description' => new Field\TextField('description'),
        'tags' => new Field\TextField('tags', multiple: true, filterable: true),
    ]
);
