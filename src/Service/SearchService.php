<?php

namespace App\Service;

use App\Dto\SearchResultDto;

class SearchService
{
    private array $dummyProducts = [
        ['id' => 1,  'name' => 'Alpha Gadget',       'description' => 'High-tech gadget for everyday use.',       'color' => 'Red'],
        ['id' => 2,  'name' => 'Beta Widget',        'description' => 'Durable widget with multiple features.',   'color' => 'Blue'],
        ['id' => 3,  'name' => 'Gamma Device',       'description' => 'Compact device for smart solutions.',     'color' => 'Green'],
        ['id' => 4,  'name' => 'Delta Tool',         'description' => 'Reliable tool for professionals.',        'color' => 'Yellow'],
        ['id' => 5,  'name' => 'Epsilon Accessory',  'description' => 'Stylish accessory for daily wear.',      'color' => 'Black'],
        ['id' => 6,  'name' => 'Zeta Instrument',    'description' => 'Precision instrument for measurements.', 'color' => 'White'],
        ['id' => 7,  'name' => 'Eta Module',         'description' => 'Modular component for tech setups.',      'color' => 'Orange'],
        ['id' => 8,  'name' => 'Theta Appliance',    'description' => 'Smart appliance for modern homes.',      'color' => 'Purple'],
        ['id' => 9,  'name' => 'Iota Device',        'description' => 'Compact and lightweight device.',         'color' => 'Gray'],
        ['id' => 10, 'name' => 'Kappa Gadget',       'description' => 'Innovative gadget with advanced features.','color' => 'Cyan'],
        ['id' => 11, 'name' => 'Lambda Widget',      'description' => 'Versatile widget for multiple tasks.',   'color' => 'Magenta'],
        ['id' => 12, 'name' => 'Mu Tool',            'description' => 'Essential tool for everyday work.',      'color' => 'Brown'],
        ['id' => 13, 'name' => 'Nu Accessory',       'description' => 'Elegant accessory for modern style.',    'color' => 'Pink'],
        ['id' => 14, 'name' => 'Xi Module',          'description' => 'Flexible module for tech projects.',     'color' => 'Teal'],
        ['id' => 15, 'name' => 'Omicron Appliance',  'description' => 'Smart appliance with energy-saving mode.','color' => 'Navy'],
        ['id' => 16, 'name' => 'Pi Device',          'description' => 'High-performance device for daily tasks.','color' => 'Olive'],
        ['id' => 17, 'name' => 'Rho Gadget',         'description' => 'Compact gadget with sleek design.',       'color' => 'Maroon'],
        ['id' => 18, 'name' => 'Sigma Widget',       'description' => 'Advanced widget for productivity.',       'color' => 'Lime'],
        ['id' => 19, 'name' => 'Tau Tool',           'description' => 'Reliable tool for home and office.',     'color' => 'Silver'],
        ['id' => 20, 'name' => 'Upsilon Accessory',  'description' => 'Trendy accessory for tech enthusiasts.', 'color' => 'Gold'],
    ];

    public function search(string $query): array
    {
        $products = array_map(
            fn($product) => new SearchResultDto(
                id: $product['id'],
                name: $product['name'],
                description: $product['description'],
                color: $product['color']
            ),
            $this->dummyProducts
        );

        // simulate a delay to mimic a real search
        usleep(100000);

        return $products;
    }
}
