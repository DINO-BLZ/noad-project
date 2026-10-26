<?php

namespace App\Concerns;

/*
 * Ordre global des verrous :
 * 1. Variantes.
 * 2. Lignes du panier.
 * 3. Lignes de la table drop_product.
 * 4. Commandes.
 *
 * On ne remonte jamais dans cet ordre : une opération qui a acquis
 * un verrou à une étape donnée ne doit pas demander ensuite un verrou
 * appartenant à une étape précédente.
 */
trait LocksRowsForUpdate
{
    public function withRowLock($query)
    {
        return $query->lockForUpdate();
    }
}
