<?php
session_start();
require_once 'config.php';

/* ── Récupération de l'hôtel ── */
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: index.php");
    exit;
}

try {
    $pdo  = getDB();
    $stmt = $pdo->prepare("SELECT * FROM formulaire_hotel WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $hotel = $stmt->fetch();
} catch (Exception $e) {
    $hotel = null;
}

if (!$hotel) {
    header("Location: index.php");
    exit;
}

/* ── Helpers ── */
$equipements = [
    'jardin'       => ['🌿', 'Jardin'],
    'wifi_gratuit' => ['📶', 'Wifi gratuit'],
    'piscine'      => ['🏊', 'Piscine'],
    'vue_mer'      => ['🌊', 'Vue sur mer'],
    'activites'    => ['🎯', 'Activités'],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($hotel['nom_hotel']) ?> — SNI Hôtel</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="descr_hotel.css">


</head>
<body>

<?php require_once 'navbar.php'; ?>

<!-- Bouton retour -->
<a href="index.php" class="btn-retour">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <line x1="19" y1="12" x2="5" y2="12"/>
        <polyline points="12 5 5 12 12 19"/>
    </svg>
    Retour aux hôtels
</a>

<!-- ══ HERO ══ -->
<div class="hero">
    <?php if (!empty($hotel['photo'])): ?>
        <img src="<?= htmlspecialchars($hotel['photo']) ?>" alt="Photo de <?= htmlspecialchars($hotel['nom_hotel']) ?>">
    <?php else: ?>
        <div class="hero-placeholder">🏨</div>
    <?php endif; ?>
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <?php if (!empty($hotel['province'])): ?>
            <span class="hero-province"><?= htmlspecialchars($hotel['province']) ?></span><br>
        <?php endif; ?>
        <h1 class="hero-title"><?= htmlspecialchars($hotel['nom_hotel']) ?></h1>
    </div>
</div>

<!-- ══ CONTENU ══ -->
<div class="page-wrapper">

    <!-- ── Colonne gauche ── -->
    <div class="main-col">

        <!-- Localisation -->
        <div class="section-block">
            <h2 class="block-title">📍 Localisation</h2>

            <?php if (!empty($hotel['adresse'])): ?>
            <div class="info-row">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/>
                    <circle cx="12" cy="10" r="3"/>
                </svg>
                <?= htmlspecialchars($hotel['adresse']) ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($hotel['proximite'])): ?>
            <div class="info-row">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                À proximité : <strong style="margin-left:4px"><?= htmlspecialchars($hotel['proximite']) ?></strong>
            </div>
            <?php endif; ?>

            <?php if (!empty($hotel['latitude']) && !empty($hotel['longitude'])): ?>
            <div class="info-row">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="2" y1="12" x2="22" y2="12"/>
                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                </svg>
                <a href="https://www.google.com/maps?q=<?= $hotel['latitude'] ?>,<?= $hotel['longitude'] ?>"
                   target="_blank" style="color:#7C3B9A;">
                   Voir sur Google Maps
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Équipements -->
        <div class="section-block">
            <h2 class="block-title">🛎 Équipements & services</h2>
            <div class="equip-grid">
                <?php foreach ($equipements as $key => [$icone, $label]):
                    $actif = !empty($hotel[$key]); ?>
                    <div class="equip-item <?= $actif ? '' : 'inactive' ?>">
                        <span class="equip-icon"><?= $icone ?></span>
                        <?= $label ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Activités -->
        <?php if (!empty($hotel['description_activites'])): ?>
        <div class="section-block">
            <h2 class="block-title">🎯 Activités proposées</h2>
            <p class="activites-text"><?= htmlspecialchars($hotel['description_activites']) ?></p>
        </div>
        <?php endif; ?>

        <!-- Propriétaires -->
        <?php
        $hasProp   = !empty($hotel['nom_proprietaire']) || !empty($hotel['prenom_proprietaire']);
        $hasCoprop = !empty($hotel['nom_coproprietaire']) || !empty($hotel['prenom_coproprietaire']);
        ?>
        <?php if ($hasProp || $hasCoprop): ?>
        <div class="section-block">
            <h2 class="block-title">👤 Propriétaires</h2>
            <div class="owners-grid">
                <?php if ($hasProp): ?>
                <div class="owner-card">
                    <div class="owner-label">Propriétaire</div>
                    <div class="owner-name">
                        <?= htmlspecialchars(trim($hotel['prenom_proprietaire'] . ' ' . $hotel['nom_proprietaire'])) ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($hasCoprop): ?>
                <div class="owner-card">
                    <div class="owner-label">Co-propriétaire</div>
                    <div class="owner-name">
                        <?= htmlspecialchars(trim($hotel['prenom_coproprietaire'] . ' ' . $hotel['nom_coproprietaire'])) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

    </div><!-- /.main-col -->

    <!-- ── Sidebar ── -->
    <aside class="sidebar">

        <!-- Bouton réservation -->
        <div class="sidebar-card">
            <p style="font-size:0.88rem;color:#6b7280;margin-bottom:12px;">
                Intéressé par cet établissement ?
            </p>
            <a href="reservation.php?hotel_id=<?= (int)$hotel['id'] ?>" class="btn-reserver">
                Réserver maintenant →
            </a>
        </div>

        <!-- Contact -->
        <div class="sidebar-card">
            <h3 class="contact-title">Contact</h3>

            <?php if (!empty($hotel['telephone'])): ?>
            <a href="tel:<?= htmlspecialchars($hotel['telephone']) ?>" class="contact-item">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.6 3.41 2 2 0 0 1 3.58 1.25h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.8a16 16 0 0 0 6.29 6.29l.93-.93a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
                </svg>
                <?= htmlspecialchars($hotel['telephone']) ?>
            </a>
            <?php endif; ?>

            <?php if (!empty($hotel['email'])): ?>
            <a href="mailto:<?= htmlspecialchars($hotel['email']) ?>" class="contact-item">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                </svg>
                <?= htmlspecialchars($hotel['email']) ?>
            </a>
            <?php endif; ?>
        </div>

        <!-- Infos rapides -->
        <div class="sidebar-card">
            <h3 class="contact-title">Infos rapides</h3>

            <?php if (!empty($hotel['nombre_chambres'])): ?>
            <div class="meta-item">
                <span>Chambres</span>
                <strong><?= (int)$hotel['nombre_chambres'] ?></strong>
            </div>
            <?php endif; ?>

            <?php if (!empty($hotel['capacite_chambre'])): ?>
            <div class="meta-item">
                <span>Capacité</span>
                <strong><?= htmlspecialchars($hotel['capacite_chambre']) ?> pers.</strong>
            </div>
            <?php endif; ?>

            <?php if (!empty($hotel['province'])): ?>
            <div class="meta-item">
                <span>Province</span>
                <strong><?= htmlspecialchars($hotel['province']) ?></strong>
            </div>
            <?php endif; ?>

            <?php if (!empty($hotel['date_creation'])): ?>
            <div class="meta-item">
                <span>Enregistré le</span>
                <strong><?= date('d/m/Y', strtotime($hotel['date_creation'])) ?></strong>
            </div>
            <?php endif; ?>
        </div>

    </aside>

</div><!-- /.page-wrapper -->

<footer style="text-align:center;padding:30px;color:#9ca3af;font-size:0.85rem;font-family:'DM Sans',sans-serif;">
    &copy; <?= date('Y') ?> SNI Hôtel — Nouvelle-Calédonie
</footer>

</body>
</html>