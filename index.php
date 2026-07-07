<?php
session_start();
require_once 'config.php';

/* ── Paramètres de filtrage ── */
$search    = trim($_GET['search']    ?? '');
$province  = trim($_GET['province']  ?? '');
$personnes = trim($_GET['personnes'] ?? '');
$dateDebut = trim($_GET['date_debut'] ?? '');
$dateFin   = trim($_GET['date_fin']   ?? '');

/* ── Utilisateurs ── */
$users = [];
try {
    $pdo   = getDB();
    $stmt  = $pdo->query("SELECT * FROM users ORDER BY id ASC LIMIT 20");
    $users = $stmt->fetchAll();
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SNI Hôtel — Nouvelle-Calédonie</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="index.css">
</head>
<body>

<?php require_once 'navbar.php'; ?>

<!-- ══════════════════════════════
     SECTION PRINCIPALE
══════════════════════════════ -->
<section class="principal">

    <form method="GET" action="" id="filterForm">

        <!-- Barre de recherche -->
        <div class="search-bar-wrapper">
            <div class="search-bar">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input
                    type="text"
                    name="search"
                    id="searchInput"
                    placeholder="Rechercher un hôtel, une destination…"
                    aria-label="Rechercher"
                    value="<?= htmlspecialchars($search) ?>"
                    autocomplete="off"
                >
            </div>
        </div>

        <!-- Titre -->
        <h1 class="principal-title">
            Bienvenue sur notre plateforme de<br>
            réservation d'hôtels en Nouvelle-Calédonie
        </h1>

        <!-- Filtres -->
        <div class="filters-row">

            <!-- Province -->
            <select class="filter-select" name="province" aria-label="Province">
                <option value="">Province</option>
                <option value="Province Sud"      <?= $province === 'Province Sud'      ? 'selected' : '' ?>>Province Sud</option>
                <option value="Province Nord"     <?= $province === 'Province Nord'     ? 'selected' : '' ?>>Province Nord</option>
                <option value="Province des Îles" <?= $province === 'Province des Îles' ? 'selected' : '' ?>>Province des Îles</option>
            </select>

            <!-- Calendrier double -->
            <div class="date-picker-wrapper" id="datePicker">
                <button type="button" class="filter-select date-trigger" id="dateTrigger" aria-haspopup="true" aria-expanded="false">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0">
                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    <span id="dateLabel">
                        <?php
                        if ($dateDebut && $dateFin) {
                            $d1 = date('d/m', strtotime($dateDebut));
                            $d2 = date('d/m', strtotime($dateFin));
                            echo htmlspecialchars("$d1 → $d2");
                        } else {
                            echo 'Date';
                        }
                        ?>
                    </span>
                </button>

                <div class="calendar-dropdown" id="calendarDropdown" role="dialog" aria-label="Sélection de dates">
                    <div class="cal-nav-bar">
                        <button type="button" class="cal-nav" id="prevMonth">&#8249;</button>
                        <div class="cal-months-container">
                            <div class="cal-month-panel">
                                <div class="cal-month-title" id="calMonthLeft"></div>
                                <div class="cal-grid-head"><span>lu</span><span>ma</span><span>me</span><span>je</span><span>ve</span><span>sa</span><span>di</span></div>
                                <div class="cal-days" id="calDaysLeft"></div>
                            </div>
                            <div class="cal-month-panel">
                                <div class="cal-month-title" id="calMonthRight"></div>
                                <div class="cal-grid-head"><span>lu</span><span>ma</span><span>me</span><span>je</span><span>ve</span><span>sa</span><span>di</span></div>
                                <div class="cal-days" id="calDaysRight"></div>
                            </div>
                        </div>
                        <button type="button" class="cal-nav" id="nextMonth">&#8250;</button>
                    </div>
                    <div class="cal-footer">
                        <span id="calSelection">Date d'arrivée — Date de départ</span>
                        <button type="button" class="cal-reset" id="calReset">Effacer</button>
                    </div>
                </div>
            </div>

            <input type="hidden" name="date_debut" id="dateDebutInput" value="<?= htmlspecialchars($dateDebut) ?>">
            <input type="hidden" name="date_fin"   id="dateFinInput"   value="<?= htmlspecialchars($dateFin) ?>">

            <!-- Nombre de personnes -->
            <select class="filter-select" name="personnes" aria-label="Nombre de personnes">
                <option value="">Personnes</option>
                <option value="2-4" <?= $personnes === '2-4' ? 'selected' : '' ?>>2 – 4 personnes</option>
                <option value="4+"  <?= $personnes === '4+'  ? 'selected' : '' ?>>4+ personnes</option>
            </select>

            <button class="btn-rechercher" type="submit">Rechercher</button>

            <?php if ($search || $province || $personnes || $dateDebut): ?>
                <a href="?" class="btn-reset-filters" title="Effacer les filtres">✕ Réinitialiser</a>
            <?php endif; ?>
        </div>

    </form>
</section>

<!-- ══════════════════════════════
     SECTION HÔTELS (avec filtres)
══════════════════════════════ -->
<section>
    <?php require_once 'affich_hotels.php'; ?>
</section>

<!-- ══════════════════════════════
     SECTION UTILISATEURS
══════════════════════════════ -->
<section class="section-table">
    <div class="section-header">
        <div>
            <h2>Utilisateurs enregistrés</h2>
            <p>Liste des comptes présents dans la base de données</p>
        </div>
        <div class="section-actions">
            <span class="badge-count"><?= count($users) ?> utilisateur(s)</span>
            <a href="table.php" class="link-table">
                Gérer la base
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <line x1="5" y1="12" x2="19" y2="12"/>
                    <polyline points="12 5 19 12 12 19"/>
                </svg>
            </a>
        </div>
    </div>

    <?php if (empty($users)): ?>
        <div class="empty-state">
            <svg width="52" height="52" fill="none" stroke="#7C3B9A" stroke-width="1.4" viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            <p>Aucun utilisateur trouvé dans la base de données.</p>
        </div>
    <?php else: ?>
        <div class="cards-grid">
            <?php foreach ($users as $user): ?>
            <div class="user-card">
                <div class="card-top">
                    <div class="card-avatar">
                        <?= mb_strtoupper(mb_substr($user['nom'], 0, 1, 'UTF-8'), 'UTF-8') ?>
                    </div>
                    <div>
                        <div class="card-name"><?= htmlspecialchars($user['nom']) ?></div>
                        <div class="card-email"><?= htmlspecialchars($user['email']) ?></div>
                    </div>
                </div>
                <div class="card-meta">
                    <span class="role-pill role-<?= htmlspecialchars($user['role']) ?>">
                        <?= htmlspecialchars($user['role']) ?>
                    </span>
                    <span class="card-id">#<?= htmlspecialchars($user['id']) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<footer>
    &copy; <?= date('Y') ?> SNI Hôtel — Nouvelle-Calédonie
</footer>

<!-- ══════════════════════════════
     CALENDRIER — JS corrigé
══════════════════════════════ -->
<script>
(function () {
    const trigger      = document.getElementById('dateTrigger');
    const dropdown     = document.getElementById('calendarDropdown');
    const daysLeft     = document.getElementById('calDaysLeft');
    const daysRight    = document.getElementById('calDaysRight');
    const lblLeft      = document.getElementById('calMonthLeft');
    const lblRight     = document.getElementById('calMonthRight');
    const prevBtn      = document.getElementById('prevMonth');
    const nextBtn      = document.getElementById('nextMonth');
    const resetBtn     = document.getElementById('calReset');
    const selLabel     = document.getElementById('calSelection');
    const dateLabel    = document.getElementById('dateLabel');
    const inputDebut   = document.getElementById('dateDebutInput');
    const inputFin     = document.getElementById('dateFinInput');

    const MOIS = ['Janvier','Février','Mars','Avril','Mai','Juin',
                  'Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

    const today = new Date(); today.setHours(0,0,0,0);
    let viewYear  = today.getFullYear();
    let viewMonth = today.getMonth();

    /* ── Restaure les dates depuis les inputs cachés (rechargement page) ── */
    let startDate = inputDebut.value ? parseISO(inputDebut.value) : null;
    let endDate   = inputFin.value   ? parseISO(inputFin.value)   : null;
    let hovering  = null;
    let clickCount = 0; /* CORRECTIF BUG : compteur de clics */

    /* ── Ouvrir / fermer ── */
    trigger.addEventListener('click', e => {
        e.stopPropagation();
        const open = dropdown.classList.toggle('open');
        trigger.setAttribute('aria-expanded', open);
        if (open) render();
    });
    document.addEventListener('click', e => {
        if (!dropdown.contains(e.target) && e.target !== trigger) {
            dropdown.classList.remove('open');
            trigger.setAttribute('aria-expanded', 'false');
        }
    });
    /* Empêche la fermeture quand on clique DANS le dropdown */
    dropdown.addEventListener('click', e => e.stopPropagation());

    /* ── Navigation ── */
    prevBtn.addEventListener('click', e => {
        e.stopPropagation();
        viewMonth--;
        if (viewMonth < 0) { viewMonth = 11; viewYear--; }
        render();
    });
    nextBtn.addEventListener('click', e => {
        e.stopPropagation();
        viewMonth++;
        if (viewMonth > 11) { viewMonth = 0; viewYear++; }
        render();
    });

    /* ── Reset ── */
    resetBtn.addEventListener('click', e => {
        e.stopPropagation();
        startDate = endDate = hovering = null;
        clickCount = 0;
        inputDebut.value = inputFin.value = '';
        dateLabel.textContent = 'Date';
        selLabel.textContent  = "Date d'arrivée — Date de départ";
        render();
    });

    /* ── Rendu ── */
    function render() {
        renderPanel(daysLeft,  lblLeft,  viewYear, viewMonth);
        let ry = viewYear, rm = viewMonth + 1;
        if (rm > 11) { rm = 0; ry++; }
        renderPanel(daysRight, lblRight, ry, rm);
        updateFooter();
    }

    function renderPanel(container, label, year, month) {
        label.textContent = MOIS[month] + ' ' + year;
        container.innerHTML = '';

        const firstDay    = new Date(year, month, 1).getDay();
        const offset      = firstDay === 0 ? 6 : firstDay - 1;
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        /* Cases vides avant le 1er */
        for (let i = 0; i < offset; i++) {
            const sp = document.createElement('span');
            sp.className = 'cal-day cal-day--empty';
            container.appendChild(sp);
        }

        for (let d = 1; d <= daysInMonth; d++) {
            const date = new Date(year, month, d);
            const btn  = document.createElement('button');
            btn.type = 'button';
            btn.textContent = d;
            btn.className = 'cal-day';

            if (date < today) {
                btn.classList.add('cal-day--disabled');
                btn.disabled = true;
            } else {
                const isStart  = startDate && isSame(date, startDate);
                const isEnd    = endDate   && isSame(date, endDate);
                const rangeEnd = endDate || hovering;
                const inRange  = startDate && rangeEnd
                                 && date > startDate && date < rangeEnd
                                 && !isSame(date, startDate);

                if (isStart) btn.classList.add('cal-day--start');
                if (isEnd)   btn.classList.add('cal-day--end');
                if (inRange) btn.classList.add('cal-day--range');

                /* CORRECTIF : mouseenter/leave uniquement en mode "attente départ" */
                btn.addEventListener('mouseenter', () => {
                    if (clickCount === 1) { hovering = date; render(); }
                });
                btn.addEventListener('mouseleave', () => {
                    if (clickCount === 1) { hovering = null; render(); }
                });

                btn.addEventListener('click', e => {
                    e.stopPropagation();
                    pick(date);
                });
            }
            container.appendChild(btn);
        }
    }

    function pick(date) {
        if (clickCount === 0) {
            /* 1er clic → date d'arrivée */
            startDate  = date;
            endDate    = null;
            hovering   = null;
            clickCount = 1;
            inputDebut.value = '';
            inputFin.value   = '';
            render();
        } else {
            /* 2e clic → date de départ */
            if (isSame(date, startDate) || date < startDate) {
                /* Clic invalide : recommencer */
                startDate  = date;
                endDate    = null;
                hovering   = null;
                clickCount = 1;
            } else {
                endDate    = date;
                clickCount = 0;
                inputDebut.value = iso(startDate);
                inputFin.value   = iso(endDate);
                dateLabel.textContent = fmtShort(startDate) + ' → ' + fmtShort(endDate);
                render();
                /* Ferme le calendrier après un court délai */
                setTimeout(() => {
                    dropdown.classList.remove('open');
                    trigger.setAttribute('aria-expanded', 'false');
                }, 350);
            }
        }
    }

    function updateFooter() {
        if (!startDate) {
            selLabel.textContent = "Date d'arrivée — Date de départ";
        } else if (!endDate) {
            selLabel.innerHTML = '<strong>' + fmtLong(startDate) + '</strong> — Date de départ';
        } else {
            selLabel.innerHTML = '<strong>' + fmtLong(startDate) + '</strong> — <strong>' + fmtLong(endDate) + '</strong>';
        }
    }

    /* ── Utilitaires ── */
    function isSame(a, b)  { return a.toDateString() === b.toDateString(); }
    function fmtShort(d)   { return String(d.getDate()).padStart(2,'0') + '/' + String(d.getMonth()+1).padStart(2,'0'); }
    function fmtLong(d)    { return d.getDate() + ' ' + MOIS[d.getMonth()].slice(0,3) + '. ' + d.getFullYear(); }
    function iso(d)        { return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0'); }
    function parseISO(s)   { const [y,m,d] = s.split('-').map(Number); return new Date(y, m-1, d); }

    /* Rendu initial si dates déjà présentes (rechargement) */
    render();
    if (startDate && endDate) {
        dateLabel.textContent = fmtShort(startDate) + ' → ' + fmtShort(endDate);
    }
})();
</script>

</body>
</html>