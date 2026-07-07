<?php
session_start();
require_once 'config.php';

// Si déjà connecté → rediriger
if (!empty($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$erreur  = '';
$succes  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    // ── Sanitisation EN PREMIER ──
    $nom          = sanitizeString($_POST['nom']          ?? '');
    $email        = sanitizeEmail($_POST['email']         ?? '');
    $mot_de_passe = trim($_POST['mot_de_passe'] ?? '');
    $confirmation = trim($_POST['confirmation']  ?? '');

    // Validations
    if ($nom === '' || $email === '' || $mot_de_passe === '') {
        $erreur = "Veuillez remplir tous les champs.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = "L'adresse email n'est pas valide.";
    } elseif (strlen($mot_de_passe) < 6) {
        $erreur = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif ($mot_de_passe !== $confirmation) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } else {
        $pdo = getDB();

        // Vérifier si l'email existe déjà
        $check = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $check->execute([':email' => $email]);

        if ($check->fetch()) {
            $erreur = "Cet email est déjà utilisé.";
        } else {
            // Insérer le nouvel utilisateur
            $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            $insert = $pdo->prepare(
                "INSERT INTO users (nom, email, mot_de_passe, role) VALUES (:nom, :email, :mdp, 'client')"
            );
            $insert->execute([
                ':nom'   => $nom,
                ':email' => $email,
                ':mdp'   => $hash,
            ]);

            $succes = "Compte créé avec succès ! Vous pouvez maintenant vous connecter.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un compte — SNI Hôtel</title>
    <link rel="stylesheet" href="login.css">
    <style>
        .success-msg {
            margin-top: 15px;
            padding: 10px 12px;
            background: #e8f5e9;
            border: 1px solid #4caf50;
            border-radius: 8px;
            color: #2e7d32;
            font-size: 13px;
            text-align: left;
        }
        .login-container { width: 360px; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Créer un compte</h2>

        <?php if ($erreur !== ''): ?>
            <div class="error-msg"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>

        <?php if ($succes !== ''): ?>
            <div class="success-msg"><?= htmlspecialchars($succes) ?></div>
            <p style="margin-top: 12px; font-size: 14px; text-align:center;">
                <a href="login.php" style="color: #7C3B9A;">Se connecter →</a>
            </p>
        <?php else: ?>

        <form method="POST" action="register.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
            <div class="form-group">
                <label for="nom">Nom complet</label>
                <input type="text" id="nom" name="nom"
                       placeholder="Votre nom"
                       value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"
                       required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                       placeholder="votre@email.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       required>
            </div>

            <div class="form-group">
                <label for="mot_de_passe">Mot de passe</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe"
                       placeholder="Minimum 6 caractères"
                       required>
            </div>

            <div class="form-group">
                <label for="confirmation">Confirmer le mot de passe</label>
                <input type="password" id="confirmation" name="confirmation"
                       placeholder="Répétez le mot de passe"
                       required>
            </div>

            <button type="submit" class="login-btn">Créer mon compte</button>
        </form>

        <p style="margin-top: 15px; font-size: 14px; color: #555; text-align:center;">
            Déjà un compte ?
            <a href="login.php" style="color: #7C3B9A;">Se connecter</a>
        </p>

        <?php endif; ?>

        <p style="margin-top: 8px; font-size: 14px; text-align:center;">
            <a href="index.php" style="color: #7C3B9A;">← Retour à l'accueil</a>
        </p>
    </div>
</body>
</html>