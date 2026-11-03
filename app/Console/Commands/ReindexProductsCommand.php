<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class ReindexProductsCommand extends Command
{
    protected $signature = 'search:reindex-products';

    protected $description = 'Réindexe le catalogue produit dans Elasticsearch (no-op si Scout est désactivé).';

    public function handle(): int
    {
        $driver = config('scout.driver');

        if ($driver === null || $driver === 'null') {
            $this->info('Scout est désactivé, réindexation ignorée.');

            return self::SUCCESS;
        }

        try {
            return $this->call('scout:import', [
                'model' => Product::class,
            ]);
        } catch (\Throwable $exception) {
            report($exception);
            $this->error('Erreur lors de la réindexation dans Elasticsearch : '.$exception->getMessage());

            return self::FAILURE;
        }
    }
}
