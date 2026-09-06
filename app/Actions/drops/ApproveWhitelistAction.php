<?php

namespace App\Actions\Drops;

use App\Models\Drop;
use App\Models\DropWhitelist;
use Illuminate\Support\Facades\DB;
use LogicException;

class ApproveWhitelistAction
{
    /**
     * Approve a pending whitelist request.
     *
     * The drop row is locked during the transaction so that
     * concurrent approvals cannot exceed max_whitelist_slots.
     *
     * @throws LogicException
     */
    public function execute(
        Drop $drop,
        DropWhitelist $whitelist
    ): DropWhitelist {
        return DB::transaction(function () use ($drop, $whitelist) {

            /*
             * Lock the drop row.
             *
             * This is important for concurrent approvals:
             * two requests cannot simultaneously validate the
             * available whitelist slots.
             */
            $lockedDrop = Drop::query()
                ->whereKey($drop->id)
                ->lockForUpdate()
                ->firstOrFail();

            /*
             * Reload and lock the whitelist request as well.
             */
            $lockedWhitelist = DropWhitelist::query()
                ->whereKey($whitelist->id)
                ->where('drop_id', $lockedDrop->id)
                ->lockForUpdate()
                ->firstOrFail();

            /*
             * Only pending requests can be approved.
             */
            if ($lockedWhitelist->status !== 'pending') {
                throw new LogicException(
                    'Cette demande de whitelist ne peut plus être approuvée.'
                );
            }

            /*
             * If the drop has a whitelist limit, check the
             * number of already approved requests while the
             * drop row is locked.
             */
            if ($lockedDrop->max_whitelist_slots !== null) {
                $approvedCount = DropWhitelist::query()
                    ->where('drop_id', $lockedDrop->id)
                    ->where('status', 'approved')
                    ->count();

                if (
                    $approvedCount >=
                    $lockedDrop->max_whitelist_slots
                ) {
                    throw new LogicException(
                        'Le nombre maximum de places whitelist a été atteint.'
                    );
                }
            }

            /*
             * Approve the request.
             */
            $lockedWhitelist->update([
                'status' => 'approved',
            ]);

            /*
             * Make sure the returned model has the user
             * relationship required by the Controller/Mailable.
             */
            $lockedWhitelist->load('user');

            return $lockedWhitelist;
        });
    }
}