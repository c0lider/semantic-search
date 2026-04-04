<?php

namespace App\Service;

class SearchEvaluator
{
    private array $productQueries = [
        'Coffee machine',
        'Smartphone',
        'Yoga mat',
        'Winter jacket',
        'Gaming mouse',
        'Running shoes',
        'Food',
        'Umbrella',
        'Wall paint',
        'Stroller',
        'Cooking pot',
        'Bluetooth speaker',
        'Garden chair',
        'Bicycle helmet',
        'Vacuum cleaner',
        'Espresso beans',
        'Wristwatch',
        'Waterproof headphones for swimming',
        'Warm blanket for the winter',
        'Sturdy backpack for hiking',
        'Sustainable glass water bottle',
        'Compact camera for vacation',
        'Cordless phone for seniors',
        'Organic fair trade tea',
        'Non-stick frying pan',
        'Electric toothbrush with app',
        'Non-slip socks for babies',
        'Powerful blender for smoothies',
        'Ergonomic chair for the office',
        'Tent for three people',
        'Tool kit for bicycles',
        'Animal friendships',
        'Protective case for tablets',
        'LED lamp with color change',
        'Running shoes with special cushioning for marathon runners',
        'Laptop with high graphics performance for professional video editing',
        'Winter boots that keep warm down to minus twenty degrees',
        'A knife made of Damascus steel with a handcrafted wooden handle',
        'Ergonomic keyboard to prevent joint pain',
        'A smart refrigerator that reduces energy consumption',
        'A stroller that can be folded with one hand',
        'Garden watering system with smartphone control',
        'A backpack with an integrated solar panel for charging',
        'Coffee machine with integrated grinder and milk foam system',
        'Headphones with active noise cancellation for travel',
        'A mattress that adapts individually to the body shape',
        'Hand blender with various attachments for chopping',
        'A robot vacuum that removes pet hair particularly efficiently',
        'Waterproof jacket with breathable membrane for athletes',
        'A frying pan that requires no butter for searing',
        'A TV with a very high resolution and bright image',
    ];

    private array $movieQueries = [
        'Action',
        'Romance',
        'Time travel',
        'Space',
        'Batman',
        'Horror house',
        'Magic',
        'Mafia',
        'Western',
        'Post-apocalyptic',
        'Superheroes',
        'Comedy',
        'Dinosaurs',
        'Pirates',
        'Vampire hunting',
        'Samurai',
        'Spy movie',
        'A movie about Mars',
        'Love in Paris',
        'Escape from zombies',
        'Search for the murderer',
        'Battle against aliens',
        'Adventure in the jungle',
        'Bank robbery',
        'Life in the Middle Ages',
        'War in space',
        'A true story',
        'Journey to the center of the Earth',
        'Battle of the gladiators',
        'Life after death',
        'Secrets of the ocean',
        'Rise of a musician',
        'Revenge of a father',
        'Dancing in the rain',
        'A dystopian world where children must fight each other',
        'A man who enters the dreams of other people',
        'A police officer hunting replicants in the future',
        'The founding of a social network at a university',
        'The destruction of a ring in a far-off land',
        'A father traveling through a black hole',
        'The discovery of a park with dinosaurs',
        'A mathematician cracking codes during the war',
        'A toy that comes to life',
        'A cook who succeeds despite a lack of talent',
        'Escape from a prison through a tunnel',
        'A boxer who gets one last chance',
        'Search for a small fish in the ocean',
        'A small robot on Earth collecting trash',
        'Battle for the throne in a fictional empire',
        'Confrontation with fears in a hotel',
        'A monster that falls in love with a girl',
    ];

    public function __construct(
        private readonly SearchOrchestratorInterface $searchOrchestrator,
    ) {
    }

    public function evaluateSearch(): void
    {
        $results = [];

        echo "Performing search evaluation on the product index" . PHP_EOL;
        foreach ($this->productQueries as $query) {
            $result = $this->searchOrchestrator->findObjectsByQuery($query, 'product');
            echo "\tTook {$result->time}ms for query '$query'" . PHP_EOL;
            $results[$query] = $result->time;
        }
        echo "Performing search evaluation on the movie index" . PHP_EOL;
        foreach ($this->movieQueries as $query) {
            $result = $this->searchOrchestrator->findObjectsByQuery($query, 'movie');
            echo "\tTook {$result->time}ms for query '$query'" . PHP_EOL;
            $results[$query] = $result->time;
        }

        $this->writeResultsToCsv($results);
    }

    private function writeResultsToCsv(array $results): void
    {
        $filePath = 'latency_results.csv';
        $tempData = [];
        $isNewFile = !file_exists($filePath);

        if ($isNewFile) {
            $tempData[] = ['Query', 'Zyklus 1'];
            foreach ($results as $query => $time) {
                $tempData[] = [$query, $time];
            }
        } else {
            if (($handle = fopen($filePath, 'r')) !== false) {
                while (($row = fgetcsv($handle, 0, ';')) !== false) {
                    $query = $row[0];
                    if (str_contains($query, 'Query')) {
                        $iterationCount = count($row);
                        $row[] = "Zyklus $iterationCount";
                    } else {
                        $row[] = isset($results[$query]) ? $results[$query] : 'N/A';
                    }
                    $tempData[] = $row;
                }
                fclose($handle);
            }
        }

        $file = fopen($filePath, 'w');

        if ($isNewFile) {
            // add UTF-8 BOM
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        }

        foreach ($tempData as $line) {
            fputcsv($file, $line, ';');
        }
        fclose($file);
    }
}
