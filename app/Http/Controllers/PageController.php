<?php

namespace App\Http\Controllers;

use App\Models\Drop;
use App\Models\NewsletterSubscriber;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
        $articles = $this->getJournalArticles();

        return view('journal.index', compact('articles'));
    }

    public function journalShow(int $id)
    {
        $articles = $this->getJournalArticles();

        if (! isset($articles[$id])) {
            abort(404);
        }

        $article = $articles[$id];
        $otherArticles = collect($articles)->except($id);

        return view('journal.show', compact('article', 'otherArticles'));
    }

    public function getJournalArticles(): array
    {
        return [
            1 => [
                'id' => 1,
                'title' => 'The Principles Behind NOAD',
                'subtitle' => 'Notre histoire, notre culture, notre vision. Pourquoi nous concevons des pièces qui résistent au conformisme et aux cycles éphémères de la hype.',
                'date' => '07.09.2026',
                'category' => 'MANIFESTE // ADN DE MARQUE',
                'read_time' => '4 MIN DE LECTURE',
                'image' => asset('images/journal/journal-01.jpg'),
                'fallback_gradient' => 'linear-gradient(135deg, #1f0b0b 0%, #0a0a0a 100%)',
                'summary' => 'Our story, our culture, our vision. Why we build garments resistant to conformity and fleeting hype cycles.',
                'sections' => [
                    [
                        'heading' => '01 // LE CONSTAT : LA TYRANNIE DU CYCLE ÉPHÉMÈRE',
                        'content' => 'NOAD est né d’un constat sans appel : le vêtement a perdu sa substance. L’industrie moderne de la mode est devenue une machine algorithmique à produire de l’oubli. Des collections interchangeables, fabriquées à la hâte dans des matières synthétiques de piètre qualité, conçues pour être photographiées une fois et jetées trois semaines plus tard. Nous refusons catégoriquement cette dynamique.',
                    ],
                    [
                        'heading' => '02 // NO ADVANTAGE : L’ÉTHIQUE DU MÉRITE ET DU VRAI',
                        'content' => 'Le nom NOAD — contraction de "No Advantage" — est un manifeste. Dans notre vision, il n’y a aucun raccourci, aucun faux privilège. Le respect ne s’achète pas par un logo tapageur floqué sur une poitrine. Il s’exprime par la tenue, la coupe, la noblesse d’un tissu lourd et la fidélité à des principes immuables. Porter NOAD, c’est refuser de se dissoudre dans la masse.',
                    ],
                    [
                        'heading' => '03 // LE SYSTÈME DE DROPS & WHITELIST : L’ARTISANAT AU JUSTE NOMBRE',
                        'content' => 'Nous ne fonctionnons pas par stocks massifs. Chaque drop NOAD est produit en quantité strictement limitée, numérotée, pensée comme une pièce d’archive. Le système de whitelist n’a pas été créé pour alimenter une hype superficielle, mais pour garantir que chaque pièce trouve son propriétaire légitime au prix juste, sans intermédiaires spéculateurs. C’est le respect du temps de coupe, du fil et de l’artisan.',
                    ],
                    [
                        'heading' => '04 // L’ARMURE URBAINE CONTEMPORAINE',
                        'content' => 'Nos silhouettes s’inspirent des lignes brutalistes, de la rigueur architecturale et de la rue nord-africaine : des pièces faites pour résister aux éléments, pour vivre, vieillir et s’embellir avec le temps. Une pièce NOAD ne se démode pas, parce qu’elle n’a jamais cherché à être à la mode.',
                    ],
                ],
                'quote' => '« Nous ne créons pas des tendances pour la saison prochaine. Nous fabriquons des pièces que vous porterez encore dans dix ans. »',
            ],
            2 => [
                'id' => 2,
                'title' => 'British Casuals: Past, Present, Future',
                'subtitle' => 'Des gradins pluvieux du nord de l’Angleterre aux ateliers textiles italiens. Comment une sous-culture ouvrière a redéfini le vestiaire masculin contemporain.',
                'date' => '02.09.2026',
                'category' => 'SOUS-CULTURE // TERRACE HERITAGE',
                'read_time' => '6 MIN DE LECTURE',
                'image' => asset('images/journal/journal-02.jpg'),
                'fallback_gradient' => 'linear-gradient(135deg, #0d1520 0%, #0a0a0a 100%)',
                'summary' => 'From the terraces of 80s Britain to the research labs of Italian sportswear. The untold story of subcultural elegance.',
                'sections' => [
                    [
                        'heading' => '01 // 1977 : LA NAISSANCE SUR LES TERRACES',
                        'content' => 'À la fin des années 70, dans les villes ouvrières de Liverpool, Manchester et Aberdeen, une révolution vestimentaire silencieuse s’opère sur les gradins de football (les "terraces"). Alors que la jeunesse anglaise est divisée entre punks et skins, un nouveau groupe émerge : les "Casuals" (ou "Perry Boys"). Leur mot d’ordre : rompre avec la violence ostentatoire et s’emparer des codes de l’élégance sportive haut de gamme.',
                    ],
                    [
                        'heading' => '02 // LES RAIDS EUROPÉENS ET LE BUTIN TEXTILE',
                        'content' => 'En suivant leurs clubs lors des Coupes d’Europe à Saint-Étienne, Rome, Munich ou Rotterdam, ces jeunes supporters découvrent des marques continentales jusqu’alors introuvables au Royaume-Uni. Ils rapportent dans leurs sacs des pièces exclusives signées Sergio Tacchini, Fila White Line, Lacoste ou Ellesse. Être Casual, c’est avant tout une quête obsessionnelle de la rareté : porter une veste que personne d’autre n’a pu dénicher.',
                    ],
                    [
                        'heading' => '03 // L’ÉLÉGANCE POUR ÉCHAPPER AU RADAR',
                        'content' => 'Le génie tactique des Casuals résidait dans l’abandon total des écharpes, des drapeaux et des couleurs de club. Habillés comme de jeunes cadres ou des joueurs de tennis mondains, ils passaient sous le radar des cordons de police et déjouaient le profilage traditionnel. L’élégance devenait une arme de camouflage et un insigne secret de reconnaissance entre initiés.',
                    ],
                    [
                        'heading' => '04 // L’ÈRE DU TEXTILE TECHNIQUE : MASSIMO OSTI & STONE ISLAND',
                        'content' => 'Au milieu des années 80, le vestiaire casual mute vers la fonctionnalité pure pour affronter la pluie et le vent britannique. C’est la rencontre mythique avec le travail de Massimo Osti : Stone Island et C.P. Company. Tissus teints en pièce, toiles militaires imperméabilisées, vestes à lunettes "Mille Miglia", détails techniques utilitaires. Aux pieds, une allégeance indéfectible aux silhouettes en suède d’Adidas (Samba, Gazelle, Trimm Trab, Spezial, Forest Hills).',
                    ],
                    [
                        'heading' => '05 // L’ÉCHO CHEZ NOAD',
                        'content' => 'L’ADN de NOAD est profondément imprégné de cet esprit terrace : la passion pour les coupes impeccables, la primauté du textile technique résistant, le refus des logos géants tape-à-l’œil, et cette fierté indomptable qui unit ceux qui partagent une culture commune.',
                    ],
                ],
                'quote' => '« Le vrai casual ne crie pas sa présence. La qualité de sa veste et la rareté de ses baskets parlent pour lui. »',
            ],
            3 => [
                'id' => 3,
                'title' => 'Quality Over Trends',
                'subtitle' => 'Pourquoi nous privilégions les cotons lourds, les finitions renforcées et l’artisanat textile plutôt que la course effrénée aux volumes.',
                'date' => '28.08.2026',
                'category' => 'SAVOIR-FAIRE // SLOW FASHION',
                'read_time' => '3 MIN DE LECTURE',
                'image' => asset('images/journal/journal-03.jpg'),
                'fallback_gradient' => 'linear-gradient(135deg, #141414 0%, #0a0a0a 100%)',
                'summary' => 'Why heavyweight cotton, reinforced stitching, and limited batches outweigh mass marketing.',
                'sections' => [
                    [
                        'heading' => '01 // LE CHOIX DU GRAMMAGE LOURD (HEAVYWEIGHT)',
                        'content' => 'Toutes les pièces en jersey NOAD sont développées sur des grammages denses : du 240g/m² pour nos t-shirts au 450g/m² en molleton gratté pour nos hoodies. Cette densité procure un tombé structuré incomparable, une isolation naturelle et une résistance aux lavages sans déformation.',
                    ],
                    [
                        'heading' => '02 // DES DÉTAILS CONÇUS POUR L’ÉPREUVE DU TEMPS',
                        'content' => 'Coutures bord-à-bord renforcées, empiècements doublés aux zones de tension, finitions minimalistes thermo-scellées et zips métalliques robustes. Chaque élément est testé pour résister aux sollicitations de la vie quotidienne sans faillir.',
                    ],
                    [
                        'heading' => '03 // POSSÉDER MOINS, MAIS POSSÉDER MIEUX',
                        'content' => 'Acheter un vêtement NOAD est un engagement mutuel. En produisant moins mais mieux, nous remettons la valeur au cœur de la création. Une pièce que l’on chérit, que l’on patine et qui raconte une histoire.',
                    ],
                ],
                'quote' => '« La perfection ne réside pas dans l’abondance, mais dans ce qu’il reste quand on a retiré tout le superflu. »',
            ],
        ];
    }

    public function sitemap()
    {
        $urls = collect([
            'home',
            'shop.index',
            'drops.index',
            'about',
            'collections.index',
            'journal.index',
            'contact',
            'shipping',
            'returns',
            'faq',
            'cgv',
            'legal',
        ])->map(fn (string $name) => [
            'loc' => route($name),
            'lastmod' => null,
        ]);

        Product::query()
            ->select(['slug', 'updated_at'])
            ->orderBy('id')
            ->get()
            ->each(fn (Product $product) => $urls->push([
                'loc' => route('products.show', $product->slug),
                'lastmod' => $product->updated_at,
            ]));

        Drop::query()
            ->select(['slug', 'updated_at'])
            ->orderBy('id')
            ->get()
            ->each(fn (Drop $drop) => $urls->push([
                'loc' => route('drops.show', $drop->slug),
                'lastmod' => $drop->updated_at,
            ]));

        return response()
            ->view('sitemap', compact('urls'))
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function newsletterSubscribe(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $subscriber = NewsletterSubscriber::firstOrCreate(
            ['email' => Str::lower(trim($validated['email']))],
            ['subscribed_at' => now()]
        );

        $message = $subscriber->wasRecentlyCreated
            ? 'Votre inscription à la newsletter est confirmée.'
            : 'Cette adresse est déjà inscrite à la newsletter.';

        $request->session()->flash('newsletter_status', $message);

        return back();
    }

    protected function renderStaticPage(string $title, string $subtitle, string $content)
    {
        return view('pages.static', compact('title', 'subtitle', 'content'));
    }
}
