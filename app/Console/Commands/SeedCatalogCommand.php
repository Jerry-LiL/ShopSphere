<?php

namespace App\Console\Commands;

use Database\Seeders\LargeCatalogSeeder;
use Illuminate\Console\Command;

class SeedCatalogCommand extends Command
{
    protected $signature = 'shop:seed-catalog {--count=10000 : Number of products to generate} {--chunk=500 : Batch chunk size}';
    protected $description = 'Seed the catalog with a large number of products using bulk batch inserts';

    public function handle(): int
    {
        $count = (int) $this->option('count');
        $chunk = (int) $this->option('chunk');

        $this->info("Starting catalog generation for {$count} products...");
        $seeder = new LargeCatalogSeeder();
        $seeder->setCommand($this);
        $seeder->run($count, $chunk);

        return self::SUCCESS;
    }
}
