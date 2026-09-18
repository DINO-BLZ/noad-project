<?php

declare(strict_types=1);

use Elastic\Adapter\Indices\Mapping;
use Elastic\Adapter\Indices\Settings;
use Elastic\Migrations\Facades\Index;
use Elastic\Migrations\MigrationInterface;

final class CreateProductsIndex implements MigrationInterface
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        Index::create('products', function (Mapping $mapping, Settings $settings): void {
            // =========================================================
            // ANALYSEUR AUTOCOMPLETE
            // =========================================================
            //
            // Exemple : "JACKET" devient à l'indexation :
            // JA, JAC, JACK, JACKE, JACKET
            //
            // La recherche, elle, utilise l'analyseur standard.

            $settings->analysis([
                'filter' => [
                    'product_autocomplete_lowercase' => [
                        'type' => 'lowercase',
                    ],
                ],
                'tokenizer' => [
                    'product_edge_ngram_tokenizer' => [
                        'type' => 'edge_ngram',
                        'min_gram' => 2,
                        'max_gram' => 20,
                    ],
                ],
                'analyzer' => [
                    'product_autocomplete' => [
                        'type' => 'custom',
                        'tokenizer' => 'product_edge_ngram_tokenizer',
                        'filter' => [
                            'product_autocomplete_lowercase',
                        ],
                    ],
                ],
            ]);

            // =========================================================
            // MAPPING
            // =========================================================

            $mapping->text('name', [
                'analyzer' => 'product_autocomplete',
                'search_analyzer' => 'standard',
            ]);

            $mapping->text('description', [
                'analyzer' => 'product_autocomplete',
                'search_analyzer' => 'standard',
            ]);
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Index::dropIfExists('products');
    }
}