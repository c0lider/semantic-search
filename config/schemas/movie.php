<?php

use CmsIg\Seal\Schema\Field;
use CmsIg\Seal\Schema\Index;

return new Index('movie', [
    'id' => new Field\IdentifierField('id'),
    'title' => new Field\TextField('title'),
    'tagline' => new Field\TextField('tagline'),
    'overview' => new Field\TextField('overview'),
    'keywords' => new Field\TextField('keywords', multiple: true, filterable: true),
    'genres' => new Field\TextField('genres', multiple: true, filterable: true),
    'director' => new Field\TextField('director'),
    'cast' => new Field\TextField('cast', multiple: true),
    'runtime' => new Field\IntegerField('runtime', filterable: true),
    'releaseDate' => new Field\DateTimeField('releaseDate', filterable: true),
    'budget' => new Field\FloatField('budget', filterable: true),
    'revenue' => new Field\FloatField('revenue', filterable: true),
    'rating' => new Field\FloatField('rating', filterable: true),
    'embedding' => new Field\FloatField('embedding', multiple: true, filterable: true),
]);
