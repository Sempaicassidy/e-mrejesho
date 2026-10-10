<?php
session_start();
require_once __DIR__ . '/../connect.php';

// Hakikisha mtumiaji ameingia
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Kushughulikia kusafirisha taarifa (Export to CSV)
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=maoni_idara_ya_mionzi_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM kwa ajili ya Excel UTF-8
    
    fputcsv($output, ['ID', 'Tarehe', 'Jina la Mgonjwa', 'Namba ya Simu', 'Kipimo', 'Ujumla', 'Muda', 'Wataalamu', 'Usafi', 'Maelekezo', 'Maoni']);
    
    $stmt = $pdo->query("SELECT * FROM `maoni_mionzi` ORDER BY `id` DESC");
    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $row['id'],
            $row['tarehe_iliyoundwa'],
            $row['jina_mgonjwa'],
            $row['simu'] ?? '-',
            $row['aina_ya_kipimo'],
            $row['rating_ujumla'],
            $row['rating_muda'],
            $row['rating_wataalamu'],
            $row['rating_usafi'],
            $row['rating_maelekezo'],
            $row['maoni'] ?? ''
        ]);
    }
    fclose($output);
    exit;
}

// Kuchuja maoni (Filtering)
$filterProc = trim($_GET['proc'] ?? '');
$filterRating = trim($_GET['rating'] ?? '');

$sql = "SELECT * FROM `maoni_mionzi` WHERE 1=1";
$params = [];

if (!empty($filterProc)) {
    $sql .= " AND `aina_ya_kipimo` LIKE ?";
    $params[] = "%$filterProc%";
}

if (!empty($filterRating)) {
    $sql .= " AND `rating_ujumla` = ?";
    $params[] = (int)$filterRating;
}

$sql .= " ORDER BY `id` DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$feedbackList = $stmt->fetchAll();

// Takwimu za Jumla (Overall Statistics)
$statTotal = $pdo->query("SELECT COUNT(*) FROM `maoni_mionzi`")->fetchColumn();
$statAvgOverall = $pdo->query("SELECT ROUND(AVG(rating_ujumla), 1) FROM `maoni_mionzi`")->fetchColumn() ?: 0;
$statAvgWait = $pdo->query("SELECT ROUND(AVG(rating_muda), 1) FROM `maoni_mionzi`")->fetchColumn() ?: 0;
$statAvgStaff = $pdo->query("SELECT ROUND(AVG(rating_wataalamu), 1) FROM `maoni_mionzi`")->fetchColumn() ?: 0;
$statAvgClean = $pdo->query("SELECT ROUND(AVG(rating_usafi), 1) FROM `maoni_mionzi`")->fetchColumn() ?: 0;

// Mgawanyo wa maoni kwa vipimo (Procedures Breakdown)
$procStats = $pdo->query("SELECT aina_ya_kipimo, COUNT(*) as idadi FROM `maoni_mionzi` GROUP BY aina_ya_kipimo ORDER BY idadi DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashibodi ya Maoni | Idara ya Mionzi (RadioAdmin)</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=2.0">
    <style>
        .badge-rating {
            display: inline-flex;
            align-items: center;
            gap: 2px;
            padding: 3px 8px;
            border-radius: var(--radius-full);
            font-size: 0.8rem;
            font-weight: 700;
        }
        .rating-high { background: #dcfce7; color: #15803d; }
        .rating-mid { background: #fef3c7; color: #b45309; }
        .rating-low { background: #fee2e2; color: #b91c1c; }
        
        /* Modal for Viewing Full Feedback */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .modal-content {
            background: #ffffff;
            border-radius: var(--radius-lg);
            width: 100%;
            max-width: 550px;
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            position: relative;
        }
        .close-modal {
            position: absolute;
            top: 1.2rem;
            right: 1.2rem;
            background: #f1f5f9;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            font-size: 1.1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body style="background: #f8fafc;">

    <!-- Admin Navigation Bar -->
    <header class="admin-navbar no-print">
        <a href="index.php" class="admin-logo">
            <span style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; background: var(--primary-light); color: var(--primary); border-radius: 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M9 3h6v6h6v6h-6v6H9v-6H3V9h6V3z"/>
                </svg>
            </span>
            <div>
                <span>Idara ya Mionzi</span>
                <span style="font-size: 0.75rem; color: var(--muted); font-weight: 500; display: block;">Msimamizi wa e-Mrejesho</span>
            </div>
        </a>

        <div style="display: flex; align-items: center; gap: 0.8rem;">
            <a href="../qrcode.php" target="_blank" class="btn btn-secondary btn-sm">
                Bango la QR Code
            </a>
            <a href="../index.php" target="_blank" class="btn btn-secondary btn-sm">
                Fomu ya Wagonjwa
            </a>
            <div style="height: 24px; width: 1px; background: var(--border);"></div>
            <span style="font-size: 0.85rem; font-weight: 600; color: var(--slate); display: none; sm:display: inline;">
                <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Msimamizi') ?>
            </span>
            <a href="logout.php" class="btn btn-sm" style="background: #fee2e2; color: #dc2626; font-weight: 700;">
                Toka
            </a>
        </div>
    </header>

    <div class="container-wide">
        
        <!-- Welcome Banner -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--dark);">
                    Takwimu za Maoni ya Idara ya Mionzi
                </h1>
                <p style="font-size: 0.9rem; color: var(--muted);">
                    Tathmini ya kuridhika kwa wagonjwa waliopata huduma za X-Ray, Ultrasound, CT-Scan, MRI na Mammography.
                </p>
            </div>
            <div>
                <a href="index.php?export=csv" class="btn btn-primary btn-sm">
                    Pakua Ripoti (Excel / CSV)
                </a>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">#</div>
                <div class="stat-info">
                    <h3><?= number_format($statTotal) ?></h3>
                    <p>Jumla ya Maoni Yaliyopokelewa</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon amber">★</div>
                <div class="stat-info">
                    <h3><?= $statAvgOverall ?> <span style="font-size: 0.9rem; color: var(--muted); font-weight: 600;">/ 5.0</span></h3>
                    <p>Wastani wa Kuridhika (Ujumla)</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">★</div>
                <div class="stat-info">
                    <h3><?= $statAvgStaff ?> <span style="font-size: 0.9rem; color: var(--muted); font-weight: 600;">/ 5.0</span></h3>
                    <p>Ukarimu na Weledi</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon blue">★</div>
                <div class="stat-info">
                    <h3><?= $statAvgWait ?> <span style="font-size: 0.9rem; color: var(--muted); font-weight: 600;">/ 5.0</span></h3>
                    <p>Kasi ya Kuhudumiwa (Muda)</p>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="form-card" style="padding: 1rem 1.4rem; margin-bottom: 1.5rem;">
            <form method="GET" action="index.php" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label" style="font-size: 0.8rem; margin-bottom: 2px;">Chuja kwa Aina ya Kipimo:</label>
                    <select name="proc" class="form-control" style="padding: 0.5rem 0.7rem; font-size: 0.88rem;">
                        <option value="">Vipimo Vyote</option>
                        <option value="X-Ray" <?= $filterProc === 'X-Ray' ? 'selected' : '' ?>>X-Ray</option>
                        <option value="Ultrasound" <?= $filterProc === 'Ultrasound' ? 'selected' : '' ?>>Ultrasound</option>
                        <option value="CT-Scan" <?= $filterProc === 'CT-Scan' ? 'selected' : '' ?>>CT-Scan</option>
                        <option value="MRI" <?= $filterProc === 'MRI' ? 'selected' : '' ?>>MRI</option>
                        <option value="Mammography" <?= $filterProc === 'Mammography' ? 'selected' : '' ?>>Mammography</option>
                    </select>
                </div>

                <div style="flex: 1; min-width: 180px;">
                    <label class="form-label" style="font-size: 0.8rem; margin-bottom: 2px;">Kiwango cha Nyota:</label>
                    <select name="rating" class="form-control" style="padding: 0.5rem 0.7rem; font-size: 0.88rem;">
                        <option value="">Viwango Vyote</option>
                        <option value="5" <?= $filterRating === '5' ? 'selected' : '' ?>>Nyota 5 (Inaridhisha Sana)</option>
                        <option value="4" <?= $filterRating === '4' ? 'selected' : '' ?>>Nyota 4 (Inaridhisha)</option>
                        <option value="3" <?= $filterRating === '3' ? 'selected' : '' ?>>Nyota 3 (Wastani)</option>
                        <option value="2" <?= $filterRating === '2' ? 'selected' : '' ?>>Nyota 2 (Hairidhishi)</option>
                        <option value="1" <?= $filterRating === '1' ? 'selected' : '' ?>>Nyota 1 (Haikuridhisha Kabisa)</option>
                    </select>
                </div>

                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary btn-sm" style="height: 38px;">
                        Chuja
                    </button>
                    <?php if (!empty($filterProc) || !empty($filterRating)): ?>
                        <a href="index.php" class="btn btn-secondary btn-sm" style="height: 38px;">
                            Weka Upya
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Table of Feedback -->
        <div class="table-card">
            <div class="table-header">
                <div>
                    <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--dark);">
                        Orodha ya Maoni ya Wagonjwa
                    </h3>
                    <p style="font-size: 0.82rem; color: var(--muted);">
                        Jumla iliyopatikana kwenye orodha hii: <?= count($feedbackList) ?>
                    </p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Tarehe & Saa</th>
                            <th>Mgonjwa</th>
                            <th>Kipimo Alichofanya</th>
                            <th>Tathmini (Nyota)</th>
                            <th>Maoni Yaliyoandikwa</th>
                            <th>Kitendo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($feedbackList)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 2.5rem; color: var(--muted);">
                                    Hakuna maoni yoyote yaliyopatikana kwa sasa. Wagonjwa wakiscan QR Code na kutuma maoni, yataonekana hapa moja kwa moja.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($feedbackList as $row): ?>
                                <?php
                                    $ratingClass = ($row['rating_ujumla'] >= 4) ? 'rating-high' : (($row['rating_ujumla'] == 3) ? 'rating-mid' : 'rating-low');
                                    $procClass = 'badge-other';
                                    if (stripos($row['aina_ya_kipimo'], 'X-Ray') !== false) $procClass = 'badge-xray';
                                    elseif (stripos($row['aina_ya_kipimo'], 'Ultrasound') !== false) $procClass = 'badge-ultrasound';
                                    elseif (stripos($row['aina_ya_kipimo'], 'CT') !== false) $procClass = 'badge-ct';
                                    elseif (stripos($row['aina_ya_kipimo'], 'MRI') !== false) $procClass = 'badge-mri';
                                ?>
                                <tr>
                                    <td style="font-weight: 700; color: var(--slate);">#<?= $row['id'] ?></td>
                                    <td style="font-size: 0.82rem; white-space: nowrap;">
                                        <?= date('d/m/Y H:i', strtotime($row['tarehe_iliyoundwa'])) ?>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--dark);">
                                            <?= htmlspecialchars($row['jina_mgonjwa']) ?>
                                        </div>
                                        <?php if (!empty($row['simu'])): ?>
                                            <div style="font-size: 0.78rem; color: var(--muted);">Simu: <?= htmlspecialchars($row['simu']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $procClass ?>">
                                            <?= htmlspecialchars($row['aina_ya_kipimo']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge-rating <?= $ratingClass ?>">
                                            ★ <?= $row['rating_ujumla'] ?>/5
                                        </span>
                                    </td>
                                    <td style="max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <?= !empty($row['maoni']) ? htmlspecialchars($row['maoni']) : '<span style="color: var(--muted); font-style: italic;">Bila ujumbe wa ziada</span>' ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-secondary btn-sm" onclick='viewDetails(<?= json_encode($row) ?>)'>
                                            Tazama
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Modal ya Kuona Maoni Kamili -->
    <div class="modal" id="detailsModal">
        <div class="modal-content">
            <button class="close-modal" onclick="closeModal()">✕</button>
            <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 1.2rem;">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; background: var(--primary-light); color: var(--primary); border-radius: 8px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                    </svg>
                </span>
                <div>
                    <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--dark);" id="modalTitle">Maelezo ya Maoni</h3>
                    <div style="font-size: 0.8rem; color: var(--muted);" id="modalDate"></div>
                </div>
            </div>

            <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 1rem; margin-bottom: 1.2rem;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.8rem; font-size: 0.88rem;">
                    <div>
                        <strong style="color: var(--muted); font-size: 0.78rem; display: block;">Mgonjwa:</strong>
                        <span id="modalPatient" style="font-weight: 700; color: var(--dark);"></span>
                    </div>
                    <div>
                        <strong style="color: var(--muted); font-size: 0.78rem; display: block;">Simu:</strong>
                        <span id="modalPhone"></span>
                    </div>
                    <div style="grid-column: span 2;">
                        <strong style="color: var(--muted); font-size: 0.78rem; display: block;">Kipimo Alichofanyiwa:</strong>
                        <span id="modalProc" style="font-weight: 700; color: var(--primary);"></span>
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 1.2rem;">
                <h4 style="font-size: 0.9rem; font-weight: 700; color: var(--dark); margin-bottom: 0.6rem;">Alama za Viwango (Ratings):</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.6rem; font-size: 0.85rem;">
                    <div style="display: flex; justify-content: space-between; background: #fff; padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border);">
                        <span>Huduma kwa Ujumla:</span>
                        <strong id="modalRatUjumla" style="color: #f59e0b;"></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; background: #fff; padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border);">
                        <span>Kasi ya Muda:</span>
                        <strong id="modalRatMuda" style="color: #f59e0b;"></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; background: #fff; padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border);">
                        <span>Wataalamu:</span>
                        <strong id="modalRatStaff" style="color: #f59e0b;"></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; background: #fff; padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border);">
                        <span>Usafi/Faragha:</span>
                        <strong id="modalRatClean" style="color: #f59e0b;"></strong>
                    </div>
                    <div style="grid-column: span 2; display: flex; justify-content: space-between; background: #fff; padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border);">
                        <span>Ufafanuzi wa Kipimo:</span>
                        <strong id="modalRatGuide" style="color: #f59e0b;"></strong>
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <h4 style="font-size: 0.9rem; font-weight: 700; color: var(--dark); margin-bottom: 0.4rem;">Maoni ya Mgonjwa:</h4>
                <div id="modalComment" style="background: #f1f5f9; padding: 0.9rem; border-radius: var(--radius-sm); font-size: 0.9rem; color: var(--slate); line-height: 1.5; min-height: 60px;"></div>
            </div>

            <div style="text-align: right;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal()">Funga</button>
            </div>
        </div>
    </div>

    <script>
        function viewDetails(data) {
            document.getElementById('modalTitle').textContent = 'Maoni #' + data.id;
            document.getElementById('modalDate').textContent = 'Tarehe: ' + data.tarehe_iliyoundwa;
            document.getElementById('modalPatient').textContent = data.jina_mgonjwa;
            document.getElementById('modalPhone').textContent = data.simu ? data.simu : 'Haikuwekwa (Bila jina)';
            document.getElementById('modalProc').textContent = data.aina_ya_kipimo;
            
            document.getElementById('modalRatUjumla').textContent = '★ ' + data.rating_ujumla + '/5';
            document.getElementById('modalRatMuda').textContent = '★ ' + data.rating_muda + '/5';
            document.getElementById('modalRatStaff').textContent = '★ ' + data.rating_wataalamu + '/5';
            document.getElementById('modalRatClean').textContent = '★ ' + data.rating_usafi + '/5';
            document.getElementById('modalRatGuide').textContent = '★ ' + data.rating_maelekezo + '/5';
            
            document.getElementById('modalComment').textContent = data.maoni ? data.maoni : 'Mgonjwa hakutoa maelezo ya ziada ya maandishi.';
            
            document.getElementById('detailsModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('detailsModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('detailsModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
