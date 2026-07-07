<?php
/* ══════════════════════════════════════════
   section_hotels.php
   Inclure dans index.php avec :
   <?php include 'section_hotels.php'; ?>
══════════════════════════════════════════ */

$hotels = [];
try {
    $hotels = getDB()
        ->query("SELECT * FROM formulaire_hotel ORDER BY id ASC LIMIT 20")
        ->fetchAll();
} catch (Exception $e) {
    // BDD pas encore dispo : on affiche l'état vide
}
?>

<section class="section-hotels">

    <div class="hotels-section-header">
        <div>
            <h2>Hôtels disponibles</h2>
            <p>Découvrez nos établissements en Nouvelle-Calédonie</p>
        </div>
        <span class="hotels-badge-count"><?= count($hotels) ?> hôtel(s)</span>
    </div>

    <?php if (empty($hotels)): ?>
        <div class="hotels-empty">
            <svg width="52" height="52" fill="none" stroke="#7C3B9A" stroke-width="1.4" viewBox="0 0 24 24">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            <p>Aucun hôtel trouvé dans la base de données.</p>
        </div>

    <?php else: ?>
        <div class="hotels-grid">
            <?php foreach ($hotels as $h):
                // Équipements actifs
                $amenities = [];
                if (!empty($h['jardin']))       $amenities[] = ['icone' => '🌿', 'label' => 'Jardin'];
                if (!empty($h['wifi_gratuit'])) $amenities[] = ['icone' => '📶', 'label' => 'Wifi gratuit'];
                if (!empty($h['piscine']))      $amenities[] = ['icone' => '🏊', 'label' => 'Piscine'];
                if (!empty($h['vue_mer']))      $amenities[] = ['icone' => '🌊', 'label' => 'Vue sur mer'];
                if (!empty($h['activites']))    $amenities[] = ['icone' => '🎯', 'label' => 'Activités'];

                // ⚠️ Remplace rand(1,5) par $h['note'] quand tu auras une colonne note dans ta table
                $note = rand(1, 5);
            ?>
            <div class="hotel-card">

                <!-- Photo -->
                <div class="hotel-card-img">
                    <?php if (!empty($h['photo'])): ?>
                        <img src="<?= htmlspecialchars($h['photo']) ?>"
                             alt="Photo de <?= htmlspecialchars($h['nom_hotel']) ?>">
                    <?php else: ?>
                        <span class="hotel-card-placeholder">🏨</span>
                    <?php endif; ?>
                </div>

                <!-- Corps -->
                <div class="hotel-card-body">

                    <div class="hotel-card-name">
                        <?= htmlspecialchars($h['nom_hotel']) ?>
                    </div>

                    <?php if (!empty($h['adresse'])): ?>
                    <div class="hotel-card-adresse">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        <?= htmlspecialchars($h['adresse']) ?>
                    </div>
                    <?php endif; ?>

                    <!-- Étoiles -->
                    <div class="hotel-stars">
                        <div class="stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star <?= $i <= $note ? 'star--full' : 'star--empty' ?>">★</span>
                            <?php endfor; ?>
                        </div>
                        <span class="hotel-note-label"><?= $note ?>/5</span>
                    </div>

                    <!-- Équipements -->
                    <?php if (!empty($amenities)): ?>
                    <div class="hotel-amenities">
                        <?php foreach ($amenities as $a): ?>
                            <span class="amenity-badge">
                                <?= $a['icone'] ?> <?= $a['label'] ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($h['nombre_chambres'])): ?>
                    <div class="hotel-chambres">
                        🛏 <?= (int)$h['nombre_chambres'] ?> chambre(s)
                        <?php if (!empty($h['capacite_chambre'])): ?>
                            · <?= htmlspecialchars($h['capacite_chambre']) ?> pers.
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                </div>

                <!-- Pied de carte -->
                <div class="hotel-card-footer">
                    <span class="hotel-province">
                        <?= htmlspecialchars($h['province']) ?>
                    </span>
                    <a href="hotel.php?id=<?= (int)$h['id'] ?>" class="btn-voir-tarif">
                        Voir tarif
                    </a>
                </div>

            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</section>