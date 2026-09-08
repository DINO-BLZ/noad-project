<?php

namespace App\Http\Controllers;

use App\Models\Drop;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class DropRequestController extends Controller
{
    public function store(Request $request, Drop $drop)
    {
        $user = $request->user();
        $this->authorize('request', $drop);

        $whitelist = $user->dropWhitelists()->firstOrNew([
            'drop_id' => $drop->id,
            'user_id' => $user->id,
        ]);

        if ($whitelist->exists && $whitelist->status === 'approved') {
            return back()->with('success', 'Vous êtes déjà whitelisté pour ce drop.');
        }

        if ($whitelist->exists && $whitelist->status === 'pending') {
            return back()->with('info', 'Votre demande est en attente de validation.');
        }

        if ($whitelist->exists && $whitelist->status === 'rejected') {
            $whitelist->status = 'pending';

            try {
                $whitelist->save();
            } catch (QueryException $e) {
                if ($e->getCode() === '23000') {
                    return back()->with('info', 'Votre demande est en attente de validation.');
                }

                throw $e;
            }

            return back()->with('success', 'Votre nouvelle demande a été envoyée.');
        }

        $whitelist->status = 'pending';

        try {
            $whitelist->save();
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return back()->with('info', 'Votre demande est en attente de validation.');
            }

            throw $e;
        }

        return back()->with('success', 'Votre demande de whitelist a bien été envoyée.');
    }
}