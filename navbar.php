<?php
/* ══════════════════════════════════════════
   COMPOSANT NAVBAR — navbar.php
   À inclure APRÈS session_start() et require_once 'config.php'
════════════════════════════════════════════ */
?>
<nav class="navbar">
    <a href="index.php" class="logo">SNI Hôtel</a>

    <div class="nav-links">
        <?php if (!empty($_SESSION['user_id'])): ?>
            <a href="index.php">Accueil</a>
            <a href="dashboard.php">Mon espace</a>
            <a href="logout.php" class="btn-connect">Se déconnecter</a>
        <?php else: ?>
            <a href="login.php" class="btn-connect">Se connecter</a>
        <?php endif; ?>
    </div>
</nav>