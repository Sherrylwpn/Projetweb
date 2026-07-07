<?php
// ─── Connexion à la base de données ───────────────────────────────────────────
session_start();
require_once 'config.php';
$pdo = getDB(); // Connexion partagée → base "hotel"

// ─── Traitement du formulaire ──────────────────────────────────────────────────
$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Champs texte / numériques
    $nom_hotel             = trim($_POST['nom_hotel']             ?? '');
    $description           = trim($_POST['description']           ?? '');
    $date_creation         = trim($_POST['date_creation']         ?? '');
    $adresse               = trim($_POST['adresse']               ?? '');
    $province              = trim($_POST['province']              ?? '');
    $latitude              = trim($_POST['latitude']              ?? '');
    $longitude             = trim($_POST['longitude']             ?? '');
    $proximite             = trim($_POST['proximite']             ?? '');
    $nombre_chambres       = trim($_POST['nombre_chambres']       ?? '');
    $type_chambre          = trim($_POST['type_chambre']          ?? '');
    $capacite_chambre      = trim($_POST['capacite_chambre']      ?? '');
    $nom_proprietaire      = trim($_POST['nom_proprietaire']      ?? '');
    $prenom_proprietaire   = trim($_POST['prenom_proprietaire']   ?? '');
    $nom_coproprietaire    = trim($_POST['nom_coproprietaire']    ?? '');
    $prenom_coproprietaire = trim($_POST['prenom_coproprietaire'] ?? '');
    $email                 = trim($_POST['email']                 ?? '');
    $telephone             = trim($_POST['telephone']             ?? '');
    $description_activites = trim($_POST['description_activites'] ?? '');

    // Booléens (checkboxes)
    $jardin      = isset($_POST['jardin'])      ? 1 : 0;
    $wifi_gratuit= isset($_POST['wifi_gratuit'])? 1 : 0;
    $piscine     = isset($_POST['piscine'])     ? 1 : 0;
    $vue_mer     = isset($_POST['vue_mer'])     ? 1 : 0;
    $activites   = isset($_POST['activites'])   ? 1 : 0;

    // Validation minimale
    if ($nom_hotel === '' || $adresse === '' || $province === '' || $email === '') {
        $error = "Veuillez remplir tous les champs obligatoires (marqués d'un *).";
    } else {

        // ── Gestion de la photo ────────────────────────────────────────────
        $photo = '';
        if (!empty($_FILES['photo']['name'])) {
            $ext      = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowed  = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($ext, $allowed)) {
                $error = "Format de photo non autorisé (jpg, jpeg, png, webp uniquement).";
            } else {
                $upload_dir = 'uploads/hotels/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                $photo = $upload_dir . uniqid('hotel_', true) . '.' . $ext;
                if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photo)) {
                    $error = "Échec du téléversement de la photo.";
                    $photo = '';
                }
            }
        }

        if ($error === '') {
            // ── Insertion en base ──────────────────────────────────────────
            $sql = "INSERT INTO formulaire_hotel
                    (nom_hotel, date_creation, adresse, province,
                     latitude, longitude, proximite,
                     nombre_chambres, capacite_chambre, photo,
                     nom_proprietaire, prenom_proprietaire,
                     nom_coproprietaire, prenom_coproprietaire,
                     email, telephone,
                     jardin, wifi_gratuit, piscine, vue_mer,
                     activites, description_activites)
                    VALUES
                    (:nom_hotel, :date_creation, :adresse, :province,
                     :latitude, :longitude, :proximite,
                     :nombre_chambres, :capacite_chambre, :photo,
                     :nom_proprietaire, :prenom_proprietaire,
                     :nom_coproprietaire, :prenom_coproprietaire,
                     :email, :telephone,
                     :jardin, :wifi_gratuit, :piscine, :vue_mer,
                     :activites, :description_activites)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nom_hotel'             => $nom_hotel,
                ':date_creation'         => $date_creation ?: null,
                ':adresse'               => $adresse,
                ':province'              => $province,
                ':latitude'              => $latitude !== '' ? $latitude : null,
                ':longitude'             => $longitude !== '' ? $longitude : null,
                ':proximite'             => $proximite ?: null,
                ':nombre_chambres'       => $nombre_chambres !== '' ? (int)$nombre_chambres : null,
                ':capacite_chambre'      => $capacite_chambre ?: null,
                ':photo'                 => $photo ?: null,
                ':nom_proprietaire'      => $nom_proprietaire ?: null,
                ':prenom_proprietaire'   => $prenom_proprietaire ?: null,
                ':nom_coproprietaire'    => $nom_coproprietaire ?: null,
                ':prenom_coproprietaire' => $prenom_coproprietaire ?: null,
                ':email'                 => $email,
                ':telephone'             => $telephone !== '' ? $telephone : null,
                ':jardin'                => $jardin,
                ':wifi_gratuit'          => $wifi_gratuit,
                ':piscine'               => $piscine,
                ':vue_mer'               => $vue_mer,
                ':activites'             => $activites,
                ':description_activites' => $description_activites ?: null,
            ]);

            $success = "L'hôtel <strong>" . htmlspecialchars($nom_hotel) . "</strong> a bien été enregistré !";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Inscription Hôtel</title>
  <link rel="stylesheet" href="inscription_hotel.css" />
</head>
<body>

<div class="inscription-container">
  <h2>Création d'un nouvel hôtel</h2>

  <?php if ($error):   ?>
    <div class="error-msg"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="success-msg"><?= $success ?></div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data" novalidate>

    <!-- ══════════════════════════════════════════
         SECTION 1 — Informations générales
    ══════════════════════════════════════════ -->
    <div class="form-section">
      <h3>Informations générales</h3>

      <div class="form-row cols-2">
        <div class="form-group">
          <label for="nom_hotel">Nom de l'hôtel <span class="required">*</span></label>
          <input type="text" id="nom_hotel" name="nom_hotel"
                 value="<?= htmlspecialchars($_POST['nom_hotel'] ?? '') ?>"
                 required />
        </div>
        <div class="form-group">
          <label for="date_creation">Date de création de l'hôtel</label>
          <input type="date" id="date_creation" name="date_creation"
                 value="<?= htmlspecialchars($_POST['date_creation'] ?? '') ?>" />
        </div>
      </div>

      <div class="form-row cols-1">
        <div class="form-group">
          <label for="description">Description</label>
          <textarea id="description" name="description"
                    placeholder="Décrivez brièvement votre établissement…"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════════
         SECTION 2 — Localisation
    ══════════════════════════════════════════ -->
    <div class="form-section">
      <h3>Localisation</h3>

      <div class="form-row cols-1">
        <div class="form-group">
          <label for="adresse">Adresse complète <span class="required">*</span></label>
          <input type="text" id="adresse" name="adresse"
                 placeholder="Ex : 31 Rue…"
                 value="<?= htmlspecialchars($_POST['adresse'] ?? '') ?>"
                 required />
        </div>
      </div>

      <div class="form-row cols-2">
        <div class="form-group">
          <label for="province">Province <span class="required">*</span></label>
          <select id="province" name="province" required>
            <option value="" disabled <?= empty($_POST['province']) ? 'selected' : '' ?>> Sélectionner </option>
            <option value="Province Nord"  <?= (($_POST['province'] ?? '') === 'Province Nord')  ? 'selected' : '' ?>>Province Nord</option>
            <option value="Province Sud"   <?= (($_POST['province'] ?? '') === 'Province Sud')   ? 'selected' : '' ?>>Province Sud</option>
            <option value="Province des Îles" <?= (($_POST['province'] ?? '') === 'Province des Îles') ? 'selected' : '' ?>>Province des Îles</option>
          </select>
        </div>
        <div class="form-group">
          <label for="proximite">Proximité d'un lieu important</label>
          <select id="proximite" name="proximite">
            <option value="" <?= empty($_POST['proximite']) ? 'selected' : '' ?>> Aucune </option>
            <option value="aéroport" <?= (($_POST['proximite'] ?? '') === 'aéroport') ? 'selected' : '' ?>>Aéroport</option>
            <option value="plage"    <?= (($_POST['proximite'] ?? '') === 'plage')    ? 'selected' : '' ?>>Plage</option>
            <option value="rivière"  <?= (($_POST['proximite'] ?? '') === 'rivière')  ? 'selected' : '' ?>>Rivière</option>
            <option value="centre"   <?= (($_POST['proximite'] ?? '') === 'centre')   ? 'selected' : '' ?>>Centre-ville</option>
          </select>
        </div>
      </div>

      <div class="form-row cols-2">
        <div class="form-group">
          <label for="latitude">Latitude (GPS)</label>
          <input type="number" id="latitude" name="latitude" step="any"
                 placeholder="-22.2758…"
                 value="<?= htmlspecialchars($_POST['latitude'] ?? '') ?>" />
        </div>
        <div class="form-group">
          <label for="longitude">Longitude (GPS)</label>
          <input type="number" id="longitude" name="longitude" step="any"
                 placeholder="166.4580…"
                 value="<?= htmlspecialchars($_POST['longitude'] ?? '') ?>" />
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════════
         SECTION 3 — Chambres & capacités
    ══════════════════════════════════════════ -->
    <div class="form-section">
      <h3>Chambres &amp; capacités</h3>

      <div class="form-row cols-3">
        <div class="form-group">
          <label for="nombre_chambres">Nombre total de chambres</label>
          <input type="number" id="nombre_chambres" name="nombre_chambres" min="1"
                 placeholder="Ex : 20"
                 value="<?= htmlspecialchars($_POST['nombre_chambres'] ?? '') ?>" />
        </div>
        <div class="form-group">
          <label for="type_chambre">Type de chambres</label>
          <select id="type_chambre" name="type_chambre">
            <option value="" <?= empty($_POST['type_chambre']) ? 'selected' : '' ?>> Sélectionner </option>
            <option value="Simple">Simple</option>
            <option value="Double">Double</option>
            <option value="Suite">Suite</option>
            <option value="Familiale">Familiale</option>
          </select>
        </div>
        <div class="form-group">
          <label for="capacite_chambre">Capacité par chambre</label>
          <select id="capacite_chambre" name="capacite_chambre">
            <option value=""  <?= empty($_POST['capacite_chambre']) ? 'selected' : '' ?>> Sélectionner </option>
            <option value="2-4" <?= (($_POST['capacite_chambre'] ?? '') === '2-4') ? 'selected' : '' ?>>Entre 2 et 4 personnes</option>
            <option value="4+"  <?= (($_POST['capacite_chambre'] ?? '') === '4+')  ? 'selected' : '' ?>>Plus de 4 personnes</option>
          </select>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════════
         SECTION 4 — Équipements & services
    ══════════════════════════════════════════ -->
    <div class="form-section">
      <h3>Équipements &amp; services</h3>

      <div class="checkbox-group">
        <label>
          <input type="checkbox" name="jardin"       value="1" <?= !empty($_POST['jardin'])       ? 'checked' : '' ?>>
          Jardin
        </label>
        <label>
          <input type="checkbox" name="wifi_gratuit" value="1" <?= !empty($_POST['wifi_gratuit']) ? 'checked' : '' ?>>
          Wifi gratuit
        </label>
        <label>
          <input type="checkbox" name="piscine"      value="1" <?= !empty($_POST['piscine'])      ? 'checked' : '' ?>>
          Piscine
        </label>
        <label>
          <input type="checkbox" name="vue_mer"      value="1" <?= !empty($_POST['vue_mer'])      ? 'checked' : '' ?>>
          Vue sur mer
        </label>
        <label>
          <input type="checkbox" name="activites"    value="1" <?= !empty($_POST['activites'])    ? 'checked' : '' ?>>
          Activités
        </label>
      </div>

      <div class="form-row cols-1" style="margin-top:16px;">
        <div class="form-group">
          <label for="description_activites">Description des activités proposées</label>
          <textarea id="description_activites" name="description_activites"
                    placeholder="Ex : plongée, randonnée, kayak…"><?= htmlspecialchars($_POST['description_activites'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════════
         SECTION 5 — Photos & médias
    ══════════════════════════════════════════ -->
    <div class="form-section">
      <h3>Photos &amp; médias</h3>

      <div class="form-row cols-1">
        <div class="form-group">
          <label>Photo principale de l'hôtel</label>
          <label class="upload-area" for="photo">
            <p>Cliquez pour choisir une image (jpg, png, webp)</p>
            <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png,.webp" />
          </label>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════════
         SECTION 6 — Contact & gestion
    ══════════════════════════════════════════ -->
    <div class="form-section">
      <h3>Contact &amp; gestion</h3>

      <div class="form-row cols-2">
        <div class="form-group">
          <label for="nom_proprietaire">Nom du propriétaire</label>
          <input type="text" id="nom_proprietaire" name="nom_proprietaire"
                 value="<?= htmlspecialchars($_POST['nom_proprietaire'] ?? '') ?>" />
        </div>
        <div class="form-group">
          <label for="prenom_proprietaire">Prénom du propriétaire</label>
          <input type="text" id="prenom_proprietaire" name="prenom_proprietaire"
                 value="<?= htmlspecialchars($_POST['prenom_proprietaire'] ?? '') ?>" />
        </div>
      </div>

      <div class="form-row cols-2">
        <div class="form-group">
          <label for="nom_coproprietaire">Nom du co-propriétaire</label>
          <input type="text" id="nom_coproprietaire" name="nom_coproprietaire"
                 value="<?= htmlspecialchars($_POST['nom_coproprietaire'] ?? '') ?>" />
        </div>
        <div class="form-group">
          <label for="prenom_coproprietaire">Prénom du co-propriétaire</label>
          <input type="text" id="prenom_coproprietaire" name="prenom_coproprietaire"
                 value="<?= htmlspecialchars($_POST['prenom_coproprietaire'] ?? '') ?>" />
        </div>
      </div>

      <div class="form-row cols-2">
        <div class="form-group">
          <label for="email">Email <span class="required">*</span></label>
          <input type="email" id="email" name="email"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                 required />
        </div>
        <div class="form-group">
          <label for="telephone">Téléphone</label>
          <input type="tel" id="telephone" name="telephone"
                 placeholder="Ex : xx xx xx"
                 value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>" />
        </div>
      </div>
    </div>

    <!-- ── Bouton de soumission ── -->
    <button type="submit" class="submit-btn">Enregistrer l'hôtel</button>

  </form>
</div>

</body>
</html>