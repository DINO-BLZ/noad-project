<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    public function about()
    {
        return $this->renderStaticPage(
            'À propos',
            'NOAD — slow fashion, principes et qualité durable.',
            'NOAD rassemble une philosophie de vêtements intemporels, de qualité maîtrisée et de consommation plus responsable.'
        );
    }

    public function contact()
    {
        return $this->renderStaticPage(
            'Contact',
            'Contactez NOAD',
            'Pour toute question sur les commandes, les drops, la whitelist ou les retours, contactez notre équipe.'
        );
    }

    public function shipping()
    {
        return $this->renderStaticPage(
            'Livraison 58 wilayas',
            'Expédition nationale',
            'Nous assurons la livraison en Algérie sur l’ensemble du territoire national avec suivi des commandes et contrôle qualité.'
        );
    }

    public function returns()
    {
        return $this->renderStaticPage(
            'Retours',
            'Politique de retour',
            'Les retours sont traités selon les conditions générales de vente et la conformité des articles livrés.'
        );
    }

    public function faq()
    {
        return $this->renderStaticPage(
            'FAQ',
            'Questions fréquentes',
            'La FAQ NOAD couvre les délais, la whitelist, les livraisons et les conditions de commande.'
        );
    }

    public function cgv()
    {
        return $this->renderStaticPage(
            'CGV',
            'Conditions générales de vente',
            'Les conditions générales de vente de NOAD définissent les règles de commande, de paiement et de livraison.'
        );
    }

    public function legal()
    {
        return $this->renderStaticPage(
            'Mentions légales',
            'Informations légales',
            'NOAD est une marque de mode entendue dans le cadre de la vente directe de pièces limitées et de l’édition maîtrisée.'
        );
    }

    public function collections()
    {
        return $this->renderStaticPage(
            'Collections',
            'Les séries NOAD',
            'Les collections NOAD s’inscrivent dans une logique de pièces intemporelles, maîtrisées et limitées.'
        );
    }

    public function journalIndex()
    {
        return $this->renderStaticPage(
            'Journal',
            'Le journal NOAD',
            'Suivez les idées, les valeurs et les inspirations qui façonnent la vision NOAD.'
        );
    }

    public function journalShow(int $id)
    {
        return $this->renderStaticPage(
            'Journal — article '.$id,
            'Article NOAD #'.$id,
            'Cette lecture a été préparée pour maintenir les liens de la homepage vers un journal dédié sans bloquer l’expérience.'
        );
    }

    public function newsletterSubscribe(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $request->session()->flash('success', 'Votre adresse e-mail a bien été enregistrée.');

        return back();
    }

    protected function renderStaticPage(string $title, string $subtitle, string $content)
    {
        return view('pages.static', compact('title', 'subtitle', 'content'));
    }
}
