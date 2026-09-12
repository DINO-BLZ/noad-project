@extends('layouts.app')

@section('content')
<div class="admin-dashboard-view">

    <!-- Barre Supérieure d'Opérations Back-Office -->
    <header class="admin-top-panel">
        <div class="panel-left">
            <span class="system-tag">
                NOAD / BACK-OFFICE OPERATIONS
                <span class="dept">DEPT 01</span>
            </span>
            <div class="server-status">
                <span class="pulse-indicator"></span>
                <span class="server-text">SERVEUR LIVE • DROP 01 ACTIF • WHITELIST OUVERTE</span>
            </div>
        </div>
        <div class="panel-right">
            <div class="admin-profile-badge">
                <span class="admin-shield-icon">🛡</span>
                <div class="admin-identity">
                    <span class="admin-role">SYS_ADMIN</span>
                    <span class="admin-hub">ALGER / LONDON HQ</span>
                </div>
            </div>
            <div class="sys-clock">
                <span class="clock-time">{{ now()->format('H:i:s') }}</span>
                <span class="clock-tz">UTC+1</span>
            </div>
        </div>
    </header>

    <!-- Navigation Interne Admin -->
    <nav class="admin-navigation-bar">
        <div class="nav-links-stack">
            <a href="{{ route('admin.dashboard') }}" class="admin-nav-item active">DASHBOARD</a>
            <a href="{{ route('admin.orders.index') }}" class="admin-nav-item">
                COMMANDES
                <span class="nav-pill-badge">{{ $pendingOrdersCount ?? 18 }}</span>
            </a>
            <a href="{{ route('admin.products.index') }}" class="admin-nav-item">PRODUITS &amp; STOCKS</a>
            <a href="{{ route('admin.drops.index') }}" class="admin-nav-item">
                DROPS
                <span class="nav-dot-red"></span>
            </a>
            <a href="{{ route('admin.whitelist.index') }}" class="admin-nav-item">
                WHITELIST
                <span class="nav-pill-dim">{{ $whitelistCount ?? 342 }}</span>
            </a>
            <a href="#" class="admin-nav-item">CLIENTS</a>
            <a href="#" class="admin-nav-item">LOGISTIQUE / WILAYAS</a>
        </div>
        <div class="nav-config-action">
            <a href="#" class="btn-config"><span>⚙</span> CONFIG</a>
        </div>
    </nav>

    <!-- Rangée Supérieure : KPIs Opérationnels -->
    <section class="kpi-metrics-grid">

        <!-- KPI 1 : CA -->
        <article class="metric-card">
            <div class="metric-header">
                <span class="metric-label">CHIFFRE D'AFFAIRES (DROP 01)</span>
                <span class="metric-glyph">💵</span>
            </div>
            <div class="metric-figure">
                <span class="val">{{ number_format($revenue ?? 4820000, 0, ',', ' ') }}</span>
                <span class="currency">DA</span>
            </div>
            <div class="metric-footer">
                <span class="growth-tag positive">+24% VS OBJECTIF</span>
                <span class="cadence-tag">CADENCE: OPTIMALE</span>
            </div>
        </article>

        <!-- KPI 2 : Commandes -->
        <article class="metric-card">
            <div class="metric-header">
                <span class="metric-label">COMMANDES TOTALES</span>
                <span class="metric-glyph">📦</span>
            </div>
            <div class="metric-figure">
                <span class="val">{{ $totalOrders ?? 142 }}</span>
            </div>
            <div class="metric-footer">
                <span class="meta-tag"><strong class="highlight-txt">94%</strong> CONFIRMÉES</span>
                <span class="meta-tag alert"><strong class="alert-txt">6%</strong> EN ATTENTE CIB</span>
            </div>
        </article>

        <!-- KPI 3 : Stock Restant -->
        <article class="metric-card alert-mode">
            <div class="metric-header">
                <span class="metric-label">STOCK GLOBAL RESTANT</span>
                <span class="metric-glyph alert-icon">▲</span>
            </div>
            <div class="metric-figure">
                <span class="val alert-val">{{ $stockRemainingPercent ?? 18 }}%</span>
                <span class="sub-alert-tag">FLUX TENDU</span>
            </div>
            <div class="metric-footer">
                <span class="meta-tag">HARRINGTON L: 2 UNITÉS</span>
                <span class="critical-tag">ÉPUISEMENT IMMINENT</span>
            </div>
        </article>

        <!-- KPI 4 : Whitelist -->
        <article class="metric-card">
            <div class="metric-header">
                <span class="metric-label">CANDIDATURES WHITELIST</span>
                <span class="metric-glyph">👥</span>
            </div>
            <div class="metric-figure">
                <span class="val">{{ number_format($whitelistApplications ?? 1248, 0, ',', ' ') }}</span>
            </div>
            <div class="metric-footer">
                <span class="meta-tag"><strong class="highlight-txt">{{ $pendingReviews ?? 342 }}</strong> EN ATTENTE</span>
                <a href="{{ route('admin.whitelist.index') }}" class="action-link-red">ACTION REQUISE →</a>
            </div>
        </article>

    </section>

    <!-- Grille Principale 2 Colonnes : Commandes & Suivi Drop -->
    <div class="dashboard-main-grid">

        <!-- Colonne Gauche : Commandes, Actions & Logistique -->
        <div class="grid-primary-column">

            <!-- 1. Dernières commandes à traiter -->
            <section class="admin-data-card">
                <div class="card-top-bar">
                    <div class="bar-title-group">
                        <span class="dot-square-red">■</span>
                        <h2 class="card-heading">DERNIÈRES COMMANDES À TRAITER</h2>
                        <span class="queue-tag">18 EN ATTENTE</span>
                    </div>
                    <div class="bar-actions-group">
                        <button type="button" class="btn-tool-sm">FILTRER</button>
                        <a href="{{ route('admin.orders.index') }}" class="btn-tool-sm">TOUT VOIR</a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="dashboard-orders-table">
                        <thead>
                            <tr>
                                <th>N° COMMANDE</th>
                                <th>CLIENT</th>
                                <th>WILAYA</th>
                                <th>ARTICLES</th>
                                <th>TOTAL (DA)</th>
                                <th>STATUT</th>
                                <th class="text-right">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders ?? [] as $order)
                            <tr>
                                <td>
                                    <span class="mono-code">{{ $order->reference ?? ('ND-2026-' . $order->id) }}</span>
                                </td>
                                <td>
                                    <div class="client-cell">
                                        <span class="client-name">{{ $order->fullname ?? 'Client' }}</span>
                                        <span class="client-email">{{ $order->email ?? '' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="wilaya-cell">
                                        {{ sprintf('%02d', $order->wilaya_code ?? 16) }} - {{ $order->city ?? 'Alger' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="qty-bubble">{{ $order->items_count ?? 1 }}</span>
                                </td>
                                <td>
                                    <span class="price-cell">{{ number_format($order->total, 0, ',', ' ') }} DA</span>
                                </td>
                                <td>
                                    @if(($order->status ?? 'pending') === 'pending')
                                        <span class="badge-status to-ship">À EXPÉDIER</span>
                                    @elseif(($order->status ?? '') === 'transit')
                                        <span class="badge-status in-transit">EN TRANSIT</span>
                                    @else
                                        <span class="badge-status delivered">LIVRÉ</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn-detail-order">DÉTAIL</a>
                                </td>
                            </tr>
                            @empty
                            <!-- Mock d'affichage conforme à la maquette de référence -->
                            <tr>
                                <td><span class="mono-code">ND-2026-8942</span></td>
                                <td>
                                    <div class="client-cell">
                                        <span class="client-name">Belhocine Mouaad</span>
                                        <span class="client-email">m.belhocine@outlook.dz</span>
                                    </div>
                                </td>
                                <td><span class="wilaya-cell">16 - Alger</span></td>
                                <td class="text-center"><span class="qty-bubble">3</span></td>
                                <td><span class="price-cell">34 500 DA</span></td>
                                <td><span class="badge-status to-ship">À EXPÉDIER</span></td>
                                <td class="text-right"><a href="#" class="btn-detail-order">DÉTAIL</a></td>
                            </tr>
                            <tr>
                                <td><span class="mono-code">ND-2026-8941</span></td>
                                <td>
                                    <div class="client-cell">
                                        <span class="client-name">Kaddour Amine</span>
                                        <span class="client-email">amine.kd@gmail.com</span>
                                    </div>
                                </td>
                                <td><span class="wilaya-cell">31 - Oran</span></td>
                                <td class="text-center"><span class="qty-bubble">1</span></td>
                                <td><span class="price-cell">19 000 DA</span></td>
                                <td><span class="badge-status in-transit">EN TRANSIT</span></td>
                                <td class="text-right"><a href="#" class="btn-detail-order">DÉTAIL</a></td>
                            </tr>
                            <tr>
                                <td><span class="mono-code">ND-2026-8940</span></td>
                                <td>
                                    <div class="client-cell">
                                        <span class="client-name">Zitouni Farouk</span>
                                        <span class="client-email">farouk.z@icloud.com</span>
                                    </div>
                                </td>
                                <td><span class="wilaya-cell">25 - Constantine</span></td>
                                <td class="text-center"><span class="qty-bubble">2</span></td>
                                <td><span class="price-cell">27 000 DA</span></td>
                                <td><span class="badge-status to-ship">À EXPÉDIER</span></td>
                                <td class="text-right"><a href="#" class="btn-detail-order">DÉTAIL</a></td>
                            </tr>
                            <tr>
                                <td><span class="mono-code">ND-2026-8939</span></td>
                                <td>
                                    <div class="client-cell">
                                        <span class="client-name">Benali Ryad</span>
                                        <span class="client-email">ryad.b@protonmail.com</span>
                                    </div>
                                </td>
                                <td><span class="wilaya-cell">06 - Béjaïa</span></td>
                                <td class="text-center"><span class="qty-bubble">4</span></td>
                                <td><span class="price-cell">48 000 DA</span></td>
                                <td><span class="badge-status delivered">LIVRÉ</span></td>
                                <td class="text-right"><a href="#" class="btn-detail-order">DÉTAIL</a></td>
                            </tr>
                            <tr>
                                <td><span class="mono-code">ND-2026-8938</span></td>
                                <td>
                                    <div class="client-cell">
                                        <span class="client-name">Saidi Walid</span>
                                        <span class="client-email">walid_saidi@gmail.com</span>
                                    </div>
                                </td>
                                <td><span class="wilaya-cell">19 - Sétif</span></td>
                                <td class="text-center"><span class="qty-bubble">1</span></td>
                                <td><span class="price-cell">16 000 DA</span></td>
                                <td><span class="badge-status to-ship">À EXPÉDIER</span></td>
                                <td class="text-right"><a href="#" class="btn-detail-order">DÉTAIL</a></td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-pagination-bar">
                    <span class="pagination-info">AFFICHAGE 5 SUR 18 COMMANDES</span>
                    <div class="pagination-buttons">
                        <button type="button" class="btn-pag disabled">PRÉCÉDENT</button>
                        <button type="button" class="btn-pag">SUIVANT</button>
                    </div>
                </div>
            </section>

            <!-- 2. Actions Rapides Opérateur -->
            <section class="admin-data-card">
                <div class="card-top-bar">
                    <div class="bar-title-group">
                        <span class="flash-glyph">⚡</span>
                        <h2 class="card-heading">ACTIONS RAPIDES OPÉRATEUR</h2>
                    </div>
                    <span class="mono-meta">EXEC_MODE: DIRECT</span>
                </div>
                <div class="quick-actions-bar">
                    <a href="{{ route('admin.products.create') }}" class="btn-quick-action primary">
                        <span>+ NOUVEAU PRODUIT</span>
                    </a>
                    <a href="{{ route('admin.drops.index') }}" class="btn-quick-action">
                        <span>🚀 CRÉER UN DROP</span>
                    </a>
                    <form action="{{ route('admin.whitelist.index') }}" method="GET" class="action-form-wrap">
                        <button type="submit" class="btn-quick-action red-border">
                            <span>✓ APPROUVER (50)</span>
                        </button>
                    </form>
                    <a href="#" class="btn-quick-action">
                        <span>⤓ EXPORTER LOGISTIQUE</span>
                    </a>
                </div>
            </section>

            <!-- 3. Répartition Expéditions par Zone Algérie -->
            <section class="admin-data-card">
                <div class="card-top-bar">
                    <div class="bar-title-group">
                        <h2 class="card-heading">RÉPARTITION DES EXPÉDITIONS PAR ZONE</h2>
                    </div>
                    <span class="mono-meta">FLUX NATIONAL ALGERIA</span>
                </div>
                <div class="geo-breakdown-grid">
                    <div class="geo-cell">
                        <span class="geo-title">ALGER &amp; CENTRE</span>
                        <span class="geo-val">58%</span>
                        <span class="geo-sub">82 COLIS</span>
                    </div>
                    <div class="geo-cell">
                        <span class="geo-title">ORAN &amp; OUEST</span>
                        <span class="geo-val">22%</span>
                        <span class="geo-sub">31 COLIS</span>
                    </div>
                    <div class="geo-cell">
                        <span class="geo-title">EST &amp; CONSTANTINE</span>
                        <span class="geo-val">14%</span>
                        <span class="geo-sub">20 COLIS</span>
                    </div>
                    <div class="geo-cell">
                        <span class="geo-title">SUD &amp; SAHARA</span>
                        <span class="geo-val">06%</span>
                        <span class="geo-sub">9 COLIS</span>
                    </div>
                </div>
            </section>

        </div>

        <!-- Colonne Droite : Surveillance Drop, Stocks & Flux Système -->
        <aside class="grid-sidebar-column">

            <!-- 1. Monitoring Drop 01 -->
            <section class="admin-data-card">
                <div class="card-top-bar">
                    <div class="bar-title-group">
                        <span class="live-dot-red">●</span>
                        <h2 class="card-heading">MONITORING DROP 01</h2>
                    </div>
                    <span class="badge-status-red">EN COURS</span>
                </div>
                <div class="drop-monitor-body">
                    <div class="drop-identity-row">
                        <span class="drop-series-name">THE RESISTANCE (SERIES 01)</span>
                        <span class="closure-tag">CLÔTURE DANS:</span>
                    </div>

                    <!-- Horloge compte à rebours -->
                    <div class="countdown-strip">
                        <div class="time-block">
                            <span class="digits">02</span>
                            <span class="unit">JOURS</span>
                        </div>
                        <div class="time-block">
                            <span class="digits">14</span>
                            <span class="unit">HEURES</span>
                        </div>
                        <div class="time-block">
                            <span class="digits alert">32</span>
                            <span class="unit">MINUTES</span>
                        </div>
                    </div>

                    <!-- Jauge de Vente -->
                    <div class="progress-section">
                        <div class="progress-labels">
                            <span class="progress-title">ALLOCATION VENDUE : 246 / 300 PIÈCES</span>
                            <span class="progress-percent">82%</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" style="width: 82%;"></div>
                        </div>
                        <div class="progress-foot">
                            <span>Plafond: 300 ex. exclusifs</span>
                            <span>Restants: 54 pièces</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 2. Niveaux de Stock Critiques -->
            <section class="admin-data-card">
                <div class="card-top-bar">
                    <div class="bar-title-group">
                        <span class="box-glyph">🚨</span>
                        <h2 class="card-heading">NIVEAUX DE STOCK CRITIQUES</h2>
                    </div>
                    <span class="badge-alert-count">3 ALERTES</span>
                </div>
                <div class="critical-stocks-list">
                    <div class="stock-item">
                        <div class="stock-icon">🧥</div>
                        <div class="stock-info">
                            <span class="stock-name">HARRINGTON JACKET</span>
                            <span class="stock-variant">Noir Brut • Taille L</span>
                        </div>
                        <div class="stock-action">
                            <span class="stock-qty-val alert">2 RESTANTS</span>
                            <span class="stock-sub-status">ÉPUISÉ SOUS PEU</span>
                        </div>
                    </div>
                    <div class="stock-item">
                        <div class="stock-icon">👕</div>
                        <div class="stock-info">
                            <span class="stock-name">SIGNATURE HEAVY POLO</span>
                            <span class="stock-variant">Burgundy / Noir • Taille XL</span>
                        </div>
                        <div class="stock-action">
                            <span class="stock-qty-val alert">1 RESTANT</span>
                            <span class="stock-sub-status">DERNIÈRE PIÈCE</span>
                        </div>
                    </div>
                    <div class="stock-item">
                        <div class="stock-icon">👖</div>
                        <div class="stock-info">
                            <span class="stock-name">TECHNICAL CARGO PANT</span>
                            <span class="stock-variant">Olive Concrete • Taille M</span>
                        </div>
                        <div class="stock-action">
                            <span class="stock-qty-val warning">4 RESTANTS</span>
                            <span class="stock-sub-status">RÉSERVE FAIBLE</span>
                        </div>
                    </div>
                </div>
                <div class="stock-footer-action">
                    <a href="{{ route('admin.products.index') }}" class="btn-reajust">
                        RÉAJUSTER LES QUOTAS D'INVENTAIRE
                    </a>
                </div>
            </section>

            <!-- 3. Journal d'Activité Système -->
            <section class="admin-data-card">
                <div class="card-top-bar">
                    <div class="bar-title-group">
                        <span class="terminal-glyph">🗂</span>
                        <h2 class="card-heading">JOURNAL D'ACTIVITÉ SYSTÈME</h2>
                    </div>
                    <span class="live-feed-tag">LIVE_FEED</span>
                </div>
                <div class="activity-timeline">
                    <div class="activity-log-row">
                        <span class="log-time">14:36</span>
                        <div class="log-details">
                            <span class="log-text">Nouvelle commande confirmée <strong class="code">#ND-8942</strong> (34 500 DA) — Alger</span>
                            <span class="log-sub">PAIEMENT CIB VÉRIFIÉ</span>
                        </div>
                    </div>
                    <div class="activity-log-row">
                        <span class="log-time">14:31</span>
                        <div class="log-details">
                            <span class="log-text">Demande Whitelist validée pour <strong class="code">karim.b@...</strong></span>
                            <span class="log-sub">ACCÈS DROP DÉLIVRÉ PAR SMS/MAIL</span>
                        </div>
                    </div>
                    <div class="activity-log-row">
                        <span class="log-time alert-time">14:18</span>
                        <div class="log-details">
                            <span class="log-text alert-text">Alerte stock bas : Harrington Jacket Taille L (2 unités)</span>
                            <span class="log-sub">SEUIL D'ALERTE CRITIQUE DÉPASSÉ</span>
                        </div>
                    </div>
                    <div class="activity-log-row">
                        <span class="log-time">13:54</span>
                        <div class="log-details">
                            <span class="log-text">Colis groupé expédié vers Centre de Tri Yalidine (31 Oran)</span>
                            <span class="log-sub">BORDEREAU #YAL-88219-DZ</span>
                        </div>
                    </div>
                    <div class="activity-log-row">
                        <span class="log-time">13:22</span>
                        <div class="log-details">
                            <span class="log-text">Nouvelle commande confirmée <strong class="code">#ND-8941</strong> (19 000 DA) — Oran</span>
                            <span class="log-sub">PAIEMENT À LA LIVRAISON CONFIRMÉ</span>
                        </div>
                    </div>
                </div>
                <div class="activity-footer-bar">
                    <span class="status-sys-ok">STATUT: SYSTÈME NOMINAL</span>
                    <a href="#" class="link-full-log">VOIR HISTORIQUE COMPLET →</a>
                </div>
            </section>

        </aside>
    </div>
</div>

<style>
/* Scoped Admin Dashboard Styles */
.admin-dashboard-view { width: 100%; max-width: 1440px; margin: 0 auto; padding: 24px 32px 80px 32px; box-sizing: border-box; color: var(--text, #e5e5e5); font-family: 'Barlow Condensed', -apple-system, BlinkMacSystemFont, sans-serif; background-color: #0c0c0c; }
.admin-dashboard-view a { color: inherit; text-decoration: none; }

/* 1. Header / Top Panel */
.admin-top-panel { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border, #242424); padding-bottom: 14px; margin-bottom: 20px; flex-wrap: wrap; gap: 16px; }
.panel-left { display: flex; align-items: center; gap: 20px; }
.system-tag { font-family: monospace; font-size: 11px; font-weight: 700; letter-spacing: 0.15em; color: #fff; }
.system-tag .dept { color: #666; margin-left: 8px; }
.server-status { display: flex; align-items: center; gap: 8px; background-color: #141414; border: 1px solid var(--border, #242424); padding: 4px 10px; }
.pulse-indicator { width: 7px; height: 7px; border-radius: 50%; background-color: var(--accent, #d32f2f); box-shadow: 0 0 0 2px rgba(211, 47, 47, 0.3); }
.server-text { font-family: monospace; font-size: 9px; color: #aaa; letter-spacing: 0.1em; }
.panel-right { display: flex; align-items: center; gap: 24px; }
.admin-profile-badge { display: flex; align-items: center; gap: 10px; background-color: #121212; border: 1px solid var(--border, #242424); padding: 6px 12px; }
.admin-shield-icon { color: var(--accent, #d32f2f); font-size: 13px; }
.admin-identity { display: flex; flex-direction: column; }
.admin-role { font-family: monospace; font-size: 10px; font-weight: 800; color: #ffffff; letter-spacing: 0.1em; }
.admin-hub { font-family: monospace; font-size: 8px; color: #666; letter-spacing: 0.08em; }
.sys-clock { font-family: monospace; font-size: 11px; color: #888; display: flex; gap: 6px; align-items: baseline; }
.clock-time { color: #fff; font-weight: bold; }
.clock-tz { font-size: 9px; color: #555; }

/* 2. Navigation Interne */
.admin-navigation-bar { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border, #242424); margin-bottom: 28px; padding-bottom: 0; overflow-x: auto; }
.nav-links-stack { display: flex; gap: 24px; }
.admin-nav-item { padding: 12px 0 14px 0; font-size: 12px; font-weight: 800; letter-spacing: 0.15em; color: #777; position: relative; display: flex; align-items: center; gap: 6px; white-space: nowrap; transition: color 0.2s; }
.admin-nav-item:hover { color: #ffffff; }
.admin-nav-item.active { color: #ffffff; }
.admin-nav-item.active::after { content: ''; position: absolute; bottom: -1px; left: 0; width: 100%; height: 2px; background-color: var(--accent, #d32f2f); }
.nav-pill-badge { background-color: #1a1a1a; border: 1px solid var(--border, #242424); font-family: monospace; font-size: 9px; padding: 1px 5px; color: var(--accent, #d32f2f); font-weight: bold; }
.nav-dot-red { width: 6px; height: 6px; border-radius: 50%; background-color: var(--accent, #d32f2f); }
.nav-pill-dim { background-color: #1a1a1a; border: 1px solid var(--border, #242424); font-family: monospace; font-size: 9px; padding: 1px 5px; color: #888; }
.btn-config { font-family: monospace; font-size: 11px; color: #666; letter-spacing: 0.12em; display: flex; align-items: center; gap: 6px; padding: 8px 12px; border: 1px solid var(--border, #242424); background-color: #101010; transition: all 0.2s; }
.btn-config:hover { color: #fff; border-color: #555; }

/* 3. KPIs Cards */
.kpi-metrics-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 32px; }
@media (max-width: 1100px) { .kpi-metrics-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 600px) { .kpi-metrics-grid { grid-template-columns: 1fr; } }
.metric-card { background-color: #101010; border: 1px solid var(--border, #242424); padding: 20px 22px; display: flex; flex-direction: column; justify-content: space-between; min-height: 140px; }
.metric-card.alert-mode { border-left: 3px solid var(--accent, #d32f2f); }
.metric-header { display: flex; justify-content: space-between; align-items: center; }
.metric-label { font-family: monospace; font-size: 10px; letter-spacing: 0.12em; color: #888; text-transform: uppercase; }
.metric-glyph { font-size: 13px; opacity: 0.6; }
.metric-glyph.alert-icon { color: var(--accent, #d32f2f); font-size: 11px; opacity: 1; }
.metric-figure { margin: 10px 0; display: flex; align-items: baseline; gap: 8px; }
.metric-figure .val { font-family: monospace; font-size: 34px; font-weight: 900; color: #ffffff; line-height: 1; }
.metric-figure .currency { font-family: monospace; font-size: 15px; font-weight: 800; color: #888; }
.val.alert-val { color: var(--accent, #d32f2f); }
.sub-alert-tag { font-family: monospace; font-size: 9px; color: #777; letter-spacing: 0.1em; }
.metric-footer { display: flex; justify-content: space-between; align-items: center; font-family: monospace; font-size: 10px; border-top: 1px solid #1a1a1a; padding-top: 10px; }
.growth-tag.positive { color: var(--accent, #d32f2f); font-weight: bold; }
.cadence-tag { color: #666; }
.meta-tag { color: #777; }
.highlight-txt { color: #fff; }
.alert-txt { color: #f39c12; }
.critical-tag { color: var(--accent, #d32f2f); font-weight: bold; }
.action-link-red { color: var(--accent, #d32f2f); font-weight: bold; letter-spacing: 0.08em; }

/* 4. Layout Principal 2 Colonnes */
.dashboard-main-grid { display: grid; grid-template-columns: 1.3fr 0.7fr; gap: 28px; align-items: flex-start; }
@media (max-width: 1100px) { .dashboard-main-grid { grid-template-columns: 1fr; } }

/* Blocs Communs */
.admin-data-card { background-color: #101010; border: 1px solid var(--border, #242424); margin-bottom: 24px; }
.card-top-bar { display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; border-bottom: 1px solid var(--border, #242424); background-color: #121212; }
.bar-title-group { display: flex; align-items: center; gap: 8px; }
.dot-square-red { color: var(--accent, #d32f2f); font-size: 10px; }
.live-dot-red { color: var(--accent, #d32f2f); font-size: 10px; }
.flash-glyph, .box-glyph, .terminal-glyph { font-size: 12px; }
.card-heading { font-size: 13px; font-weight: 800; letter-spacing: 0.12em; margin: 0; color: #ffffff; text-transform: uppercase; }
.queue-tag { background-color: #1a1a1a; border: 1px solid var(--border, #242424); font-family: monospace; font-size: 9px; padding: 2px 6px; color: #aaa; margin-left: 6px; }
.bar-actions-group { display: flex; gap: 8px; }
.btn-tool-sm { border: 1px solid var(--border, #242424); background-color: #0c0c0c; color: #888; padding: 4px 10px; font-family: monospace; font-size: 9px; letter-spacing: 0.1em; cursor: pointer; transition: all 0.2s; }
.btn-tool-sm:hover { color: #fff; border-color: #666; }
.mono-meta { font-family: monospace; font-size: 9px; color: #666; letter-spacing: 0.12em; }

/* 5. Tableau des Commandes */
.table-responsive { width: 100%; overflow-x: auto; }
.dashboard-orders-table { width: 100%; border-collapse: collapse; font-family: monospace; font-size: 11px; text-align: left; }
.dashboard-orders-table th { background-color: #0c0c0c; padding: 12px 18px; color: #666; font-size: 9px; letter-spacing: 0.12em; border-bottom: 1px solid var(--border, #242424); font-weight: 600; }
.dashboard-orders-table td { padding: 14px 18px; border-bottom: 1px solid #181818; vertical-align: middle; }
.dashboard-orders-table tr:hover td { background-color: #141414; }
.mono-code { color: #ddd; font-weight: bold; }
.client-cell { display: flex; flex-direction: column; }
.client-name { color: #fff; font-weight: bold; font-size: 12px; }
.client-email { font-size: 9px; color: #666; }
.wilaya-cell { color: #aaa; font-size: 10px; }
.qty-bubble { color: #aaa; }
.price-cell { font-weight: 800; color: #fff; }
.badge-status { display: inline-block; padding: 3px 6px; font-size: 8px; font-weight: 800; letter-spacing: 0.1em; border: 1px solid transparent; }
.badge-status.to-ship { background-color: rgba(211, 47, 47, 0.12); border-color: var(--accent, #d32f2f); color: var(--accent, #d32f2f); }
.badge-status.in-transit { background-color: #1c1c1c; border-color: #333; color: #aaa; }
.badge-status.delivered { background-color: rgba(46, 204, 113, 0.1); border-color: #2ecc71; color: #2ecc71; }
.btn-detail-order { border: 1px solid var(--border, #242424); background-color: #161616; color: #888; padding: 4px 10px; font-size: 9px; transition: all 0.2s; display: inline-block; }
.btn-detail-order:hover { color: #fff; border-color: #666; }
.card-pagination-bar { display: flex; justify-content: space-between; align-items: center; padding: 12px 20px; font-family: monospace; font-size: 10px; color: #666; }
.pagination-buttons { display: flex; gap: 8px; }
.btn-pag { background-color: #0c0c0c; border: 1px solid var(--border, #242424); color: #aaa; padding: 3px 8px; font-size: 9px; cursor: pointer; }
.btn-pag.disabled { opacity: 0.4; cursor: not-allowed; }

/* 6. Actions Rapides */
.quick-actions-bar { padding: 16px 20px; display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
@media (max-width: 700px) { .quick-actions-bar { grid-template-columns: repeat(2, 1fr); } }
.btn-quick-action { background-color: #0c0c0c; border: 1px solid var(--border, #242424); color: #fff; padding: 12px 10px; font-size: 11px; font-weight: 800; letter-spacing: 0.1em; text-align: center; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; }
.btn-quick-action.primary { background-color: #fff; color: #000; border-color: #fff; }
.btn-quick-action.primary:hover { background-color: var(--accent, #d32f2f); border-color: var(--accent, #d32f2f); color: #fff; }
.btn-quick-action.red-border { border-color: var(--accent, #d32f2f); color: var(--accent, #d32f2f); width: 100%; }
.btn-quick-action.red-border:hover { background-color: var(--accent, #d32f2f); color: #fff; }
.btn-quick-action:hover { border-color: #666; }
.action-form-wrap { margin: 0; }

/* 7. Géographie & Zones */
.geo-breakdown-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; padding: 20px; }
@media (max-width: 650px) { .geo-breakdown-grid { grid-template-columns: repeat(2, 1fr); } }
.geo-cell { background-color: #0c0c0c; border: 1px solid var(--border, #242424); padding: 14px; display: flex; flex-direction: column; gap: 4px; }
.geo-title { font-family: monospace; font-size: 9px; letter-spacing: 0.12em; color: #666; }
.geo-val { font-family: monospace; font-size: 24px; font-weight: 900; color: #fff; }
.geo-sub { font-family: monospace; font-size: 9px; color: #888; }

/* 8. Sidebar Droite : Drop 01 & Stocks */
.badge-status-red { background-color: var(--accent, #d32f2f); color: #fff; font-family: monospace; font-size: 8px; font-weight: 800; padding: 2px 6px; letter-spacing: 0.1em; }
.drop-monitor-body { padding: 20px; }
.drop-identity-row { display: flex; justify-content: space-between; align-items: baseline; font-family: monospace; font-size: 10px; margin-bottom: 14px; }
.drop-series-name { color: #fff; font-weight: 800; letter-spacing: 0.08em; }
.closure-tag { color: #666; font-size: 9px; letter-spacing: 0.1em; }
.countdown-strip { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 20px; }
.time-block { background-color: #0c0c0c; border: 1px solid var(--border, #242424); padding: 12px 6px; text-align: center; display: flex; flex-direction: column; }
.time-block .digits { font-family: monospace; font-size: 26px; font-weight: 900; color: #fff; line-height: 1; }
.time-block .digits.alert { color: var(--accent, #d32f2f); }
.time-block .unit { font-family: monospace; font-size: 8px; letter-spacing: 0.15em; color: #666; margin-top: 6px; }
.progress-section { border-top: 1px solid #1a1a1a; padding-top: 14px; }
.progress-labels { display: flex; justify-content: space-between; font-family: monospace; font-size: 9px; color: #aaa; margin-bottom: 6px; }
.progress-track { width: 100%; height: 4px; background-color: #1a1a1a; overflow: hidden; margin-bottom: 8px; }
.progress-fill { height: 100%; background-color: var(--accent, #d32f2f); }
.progress-foot { display: flex; justify-content: space-between; font-family: monospace; font-size: 9px; color: #666; }

/* Stocks Critiques */
.badge-alert-count { font-family: monospace; font-size: 9px; color: var(--accent, #d32f2f); letter-spacing: 0.12em; font-weight: bold; }
.critical-stocks-list { padding: 16px 20px; display: flex; flex-direction: column; gap: 12px; }
.stock-item { background-color: #0c0c0c; border: 1px solid var(--border, #242424); padding: 12px 14px; display: flex; align-items: center; gap: 14px; }
.stock-icon { font-size: 16px; opacity: 0.7; }
.stock-info { flex: 1; display: flex; flex-direction: column; }
.stock-name { font-size: 12px; font-weight: 800; letter-spacing: 0.08em; color: #fff; text-transform: uppercase; }
.stock-variant { font-family: monospace; font-size: 9px; color: #777; }
.stock-action { text-align: right; display: flex; flex-direction: column; }
.stock-qty-val { font-family: monospace; font-size: 11px; font-weight: 900; }
.stock-qty-val.alert { color: var(--accent, #d32f2f); }
.stock-qty-val.warning { color: #f39c12; }
.stock-sub-status { font-family: monospace; font-size: 8px; color: #555; letter-spacing: 0.08em; }
.stock-footer-action { padding: 0 20px 16px 20px; }
.btn-reajust { display: block; width: 100%; background-color: #0c0c0c; border: 1px solid var(--border, #242424); padding: 10px; text-align: center; font-family: monospace; font-size: 9px; letter-spacing: 0.12em; color: #888; transition: all 0.2s; box-sizing: border-box; }
.btn-reajust:hover { color: #fff; border-color: #666; }

/* Journal d'Activité Live */
.live-feed-tag { font-family: monospace; font-size: 9px; color: #666; letter-spacing: 0.12em; }
.activity-timeline { padding: 16px 20px; display: flex; flex-direction: column; gap: 12px; }
.activity-log-row { display: flex; gap: 14px; font-family: monospace; font-size: 10px; padding-bottom: 10px; border-bottom: 1px solid #161616; }
.activity-log-row:last-child { border-bottom: none; padding-bottom: 0; }
.log-time { color: var(--accent, #d32f2f); font-weight: bold; flex-shrink: 0; }
.log-time.alert-time { color: var(--accent, #d32f2f); }
.log-details { display: flex; flex-direction: column; gap: 2px; }
.log-text { color: #bbb; line-height: 1.35; }
.log-text .code { color: #fff; }
.log-text.alert-text { color: #ff6b6b; }
.log-sub { font-size: 8px; color: #555; letter-spacing: 0.08em; }
.activity-footer-bar { display: flex; justify-content: space-between; align-items: center; padding: 12px 20px; border-top: 1px solid var(--border, #242424); font-family: monospace; font-size: 9px; }
.status-sys-ok { color: #666; }
.link-full-log { color: #888; letter-spacing: 0.08em; }
.link-full-log:hover { color: #fff; }
</style>
@endsection