<?php

/* ══════════════════════════════════════════
   COMPOSANT HÔTELS — affich_hotels.php
   À inclure dans index.php après require_once 'config.php'
════════════════════════════════════════════ */

$hotels = [];
try {
    $pdo = getDB();
    $hotels = $pdo->query("
        SELECT id, nom_hotel, adresse, province, proximite, photo,
               email, telephone, jardin, wifi_gratuit, piscine,
               vue_mer, activites, description_activites
        FROM formulaire_hotel
        ORDER BY id ASC
    ")->fetchAll();
} catch (Exception $e) {
    // BDD indisponible : affichage de l'état vide
}

/* ── Icônes équipements ── */
function equipementIcon(string $nom): array {
    $icons = [
        'jardin'       => ['🌿', 'Jardin'],
        'wifi_gratuit' => ['📶', 'Wifi gratuit'],
        'piscine'      => ['🏊', 'Piscine'],
        'vue_mer'      => ['🌊', 'Vue mer'],
        'activites'    => ['🎯', 'Activités'],
    ];
    return $icons[$nom] ?? ['❓', $nom];
}
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="affich_hotels.css">

<section class="hotels-section">

    <h2 class="hotels-section-title">Nos hôtels en Nouvelle-Calédonie</h2>
    <p class="hotels-section-sub">
        <?= count($hotels) ?> établissement<?= count($hotels) > 1 ? 's' : '' ?> disponible<?= count($hotels) > 1 ? 's' : '' ?>
    </p>

    <?php if (empty($hotels)): ?>
        <div class="hotels-empty">
            <svg width="56" height="56" fill="none" stroke="#7C3B9A" stroke-width="1.3" viewBox="0 0 24 24">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            <p>Aucun hôtel trouvé dans la base de données.</p>
        </div>

    <?php else: ?>
        <div class="hotels-grid">
            <?php foreach ($hotels as $hotel): ?>
            <div class="hotel-card">

                <!-- Photo -->
                <div class="hotel-photo">
                    <?php if (!empty($hotel['photo'])): ?>
                        <img src="<?= htmlspecialchars($hotel['photo']) ?>"
                             alt="Photo de <?= htmlspecialchars($hotel['nom_hotel']) ?>"
                             loading="lazy">
                    <?php else: ?>
                        <div class="hotel-photo-placeholder">
                            <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.3" viewBox="0 0 24 24">
                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                <polyline points="21 15 16 10 5 21"/>
                            </svg>
                            Aucune photo
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($hotel['province'])): ?>
                        <span class="province-badge"><?= htmlspecialchars($hotel['province']) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Corps -->
                <div class="hotel-body">
                    <h3 class="hotel-name"><?= htmlspecialchars($hotel['nom_hotel']) ?></h3>

                    <?php if (!empty($hotel['adresse'])): ?>
                    <div class="hotel-adresse">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        <?= htmlspecialchars($hotel['adresse']) ?>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($hotel['proximite'])): ?>
                        <span class="hotel-proximite">📍 <?= htmlspecialchars($hotel['proximite']) ?></span>
                    <?php endif; ?>

                    <!-- Équipements -->
                    <?php
                    $equipements = ['jardin', 'wifi_gratuit', 'piscine', 'vue_mer', 'activites'];
                    $actifs = array_filter($equipements, fn($e) => !empty($hotel[$e]));
                    ?>
                    <?php if (!empty($actifs)): ?>
                    <div class="hotel-equipements">
                        <?php foreach ($actifs as $eq):
                            [$icone, $label] = equipementIcon($eq); ?>
                            <span class="equip-tag"><?= $icone ?> <?= $label ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($hotel['description_activites'])): ?>
                        <div class="hotel-activites">
                            <?= htmlspecialchars($hotel['description_activites']) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Footer carte -->
                    <div class="hotel-card-footer">
                        <div class="hotel-contact">
                            <?php if (!empty($hotel['telephone'])): ?>
                            <span>
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.6 3.41 2 2 0 0 1 3.58 1.25h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.8a16 16 0 0 0 6.29 6.29l.93-.93a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
                                </svg>
                                <?= htmlspecialchars($hotel['telephone']) ?>
                            </span>
                            <?php endif; ?>
                            <?php if (!empty($hotel['email'])): ?>
                            <a href="mailto:<?= htmlspecialchars($hotel['email']) ?>">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                    <polyline points="22,6 12,13 2,6"/>
                                </svg>
                                <?= htmlspecialchars($hotel['email']) ?>
                            </a>
                            <?php endif; ?>
                        </div>

                        <a href="descr_hotel.php?id=<?= (int)$hotel['id'] ?>" class="btn-voir-tarif">
                            Voir le détail →
                        </a>
                    </div>

                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</section>