<?php
session_start();
require_once 'config.php';
requireLogin(); // Redirige vers login.php si pas connecté

$pdo     = getDB();
$message = '';
$erreur  = '';

/* ══════════════════════════════
   ACTIONS POST
══════════════════════════════ */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf(); // Vérifie le jeton CSRF avant toute action
    $action = $_POST['action'] ?? '';

    /* ── INSÉRER ── */
    if ($action === 'insert') {
        $nom = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mdp = trim($_POST['mot_de_passe'] ?? '');
        $role = $_POST['role'] ?? 'client';

        if ($nom === '' || $email === '' || $mdp === '') {
            $erreur = "Tous les champs sont obligatoires pour l'insertion.";
        } else {
            // Vérifier email unique
            $check = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $check->execute([':email' => $email]);
            if ($check->fetch()) {
                $erreur = "Cet email est déjà utilisé.";
            } else {
                $hash = password_hash($mdp, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare(
                    "INSERT INTO users (nom, email, mot_de_passe, role) VALUES (:nom, :email, :mdp, :role)"
                );
                $stmt->execute([':nom' => $nom, ':email' => $email, ':mdp' => $hash, ':role' => $role]);
                $message = "Utilisateur <strong>" . htmlspecialchars($nom) . "</strong> ajouté avec succès.";
            }
        }
    }

    /* ── MODIFIER ── */
    elseif ($action === 'update') {
        $id    = (int)($_POST['id'] ?? 0);
        $nom   = trim($_POST['nom']   ?? '');
        $email = trim($_POST['email'] ?? '');
        $role  = $_POST['role'] ?? 'client';
        $mdp   = trim($_POST['mot_de_passe'] ?? '');

        if ($nom === '' || $email === '') {
            $erreur = "Le nom et l'email sont obligatoires.";
        } else {
            // Vérifier email unique (sauf pour cet utilisateur)
            $check = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
            $check->execute([':email' => $email, ':id' => $id]);
            if ($check->fetch()) {
                $erreur = "Cet email est déjà utilisé par un autre utilisateur.";
            } else {
                if ($mdp !== '') {
                    // Mettre à jour avec nouveau mot de passe
                    $hash = password_hash($mdp, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare(
                        "UPDATE users SET nom=:nom, email=:email, role=:role, mot_de_passe=:mdp WHERE id=:id"
                    );
                    $stmt->execute([':nom'=>$nom,':email'=>$email,':role'=>$role,':mdp'=>$hash,':id'=>$id]);
                } else {
                    // Mettre à jour sans toucher au mot de passe
                    $stmt = $pdo->prepare(
                        "UPDATE users SET nom=:nom, email=:email, role=:role WHERE id=:id"
                    );
                    $stmt->execute([':nom'=>$nom,':email'=>$email,':role'=>$role,':id'=>$id]);
                }
                $message = "Utilisateur <strong>" . htmlspecialchars($nom) . "</strong> modifié avec succès.";
            }
        }
    }

    /* ── SUPPRIMER ── */
    elseif ($action === 'delete') {
        $id   = (int)($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $message = "Utilisateur supprimé avec succès.";
    }
}

/* ══════════════════════════════
   RÉCUPÉRER TOUS LES USERS
══════════════════════════════ */
$users = $pdo->query("SELECT * FROM users ORDER BY id ASC")->fetchAll();

/* Pré-remplir le formulaire de modification si ?edit=ID */
$edit_user = null;
if (isset($_GET['edit'])) {
    $edit_id = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
    if ($edit_id !== false && $edit_id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $edit_id]);
        $edit_user = $stmt->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des utilisateurs — SNI Hôtel</title>
    <link rel="stylesheet" href="table.css">
</head>
<body>

<div class="page-wrapper">

    <div class="page-header">
        <h1>Gestion des utilisateurs</h1>
        <a href="index.php" class="btn btn-back"> Retour à l'accueil</a>
    </div>

    <!-- ── MESSAGES ── -->
    <?php if ($message !== ''): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>
    <?php if ($erreur !== ''): ?>
        <div class="alert alert-error"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <!-- ══════════════════════════════
         FORMULAIRE INSERTION / MODIFICATION
    ══════════════════════════════ -->
    <div class="form-card">
        <h2><?= $edit_user ? ' Modifier l\'utilisateur #' . $edit_user['id'] : ' Ajouter un utilisateur' ?></h2>

        <form method="POST" action="table.php<?= $edit_user ? '?edit=' . $edit_user['id'] : '' ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
            <input type="hidden" name="action" value="<?= $edit_user ? 'update' : 'insert' ?>">
            <?php if ($edit_user): ?>
                <input type="hidden" name="id" value="<?= $edit_user['id'] ?>">
            <?php endif; ?>

            <div class="form-grid">
                <div class="form-group">
                    <label>Nom complet</label>
                    <input type="text" name="nom" placeholder="Nom complet"
                           value="<?= htmlspecialchars($edit_user['nom'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="email@exemple.com"
                           value="<?= htmlspecialchars($edit_user['email'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label><?= $edit_user ? 'Nouveau mot de passe <span class="optional">(laisser vide = inchangé)</span>' : 'Mot de passe' ?></label>
                    <input type="password" name="mot_de_passe" placeholder="<?= $edit_user ? 'Laisser vide pour ne pas changer' : 'Minimum 6 caractères' ?>"
                           <?= $edit_user ? '' : 'required' ?>>
                </div>
                <div class="form-group">
                    <label>Rôle</label>
                    <select name="role">
                        <option value="client" <?= ($edit_user['role'] ?? '') === 'client' ? 'selected' : '' ?>>Client</option>
                        <option value="admin"  <?= ($edit_user['role'] ?? '') === 'admin'  ? 'selected' : '' ?>>Admin</option>
                        <option value="hôtel"  <?= ($edit_user['role'] ?? '') === 'hôtel'  ? 'selected' : '' ?>>Hôtel</option>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?= $edit_user ? ' Enregistrer les modifications' : ' Ajouter' ?>
                </button>
                <?php if ($edit_user): ?>
                    <a href="table.php" class="btn btn-cancel">✖ Annuler</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- ══════════════════════════════
         TABLEAU DES UTILISATEURS
    ══════════════════════════════ -->
    <div class="table-card">
        <div class="table-header">
            <h2>Liste des utilisateurs</h2>
            <span class="count-badge"><?= count($users) ?> utilisateur(s)</span>
        </div>

        <?php if (empty($users)): ?>
            <p class="no-data">Aucun utilisateur trouvé dans la base de données.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Créé le</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr class="<?= ($edit_user && $edit_user['id'] === $user['id']) ? 'row-editing' : '' ?>">
                            <td><strong>#<?= htmlspecialchars($user['id']) ?></strong></td>
                            <td><?= htmlspecialchars($user['nom']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <span class="role-badge role-<?= htmlspecialchars($user['role']) ?>">
                                    <?= htmlspecialchars($user['role']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($user['created_at']) ?></td>
                            <td class="actions-cell">
                                <!-- Modifier -->
                                <a href="table.php?edit=<?= $user['id'] ?>" class="btn btn-edit" title="Modifier">
                                    Modifier
                                </a>
                                <!-- Supprimer -->
                                <form method="POST" action="table.php"
                                      onsubmit="return confirm('Supprimer l\'utilisateur « <?= htmlspecialchars($user['nom']) ?> » ?\nCette action est irréversible.')">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id"     value="<?= $user['id'] ?>">
                                    <button type="submit" class="btn btn-delete" title="Supprimer">
                                        Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div><!-- /page-wrapper -->
</body>
</html>