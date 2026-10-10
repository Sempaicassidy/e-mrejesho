<?php
require_once __DIR__ . '/connect.php';

$success = false;
$errorMsg = '';
$submittedRecord = null;

// Kushughulikia fomu ya maoni inapotumwa na mgonjwa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $aina_ya_kipimo = trim($_POST['aina_ya_kipimo'] ?? '');
    $rating_ujumla = (int)($_POST['rating_ujumla'] ?? 5);
    $rating_muda = (int)($_POST['rating_muda'] ?? 5);
    $rating_wataalamu = (int)($_POST['rating_wataalamu'] ?? 5);
    $rating_usafi = (int)($_POST['rating_usafi'] ?? 5);
    $rating_maelekezo = (int)($_POST['rating_maelekezo'] ?? 5);
    $maoni = trim($_POST['maoni'] ?? '');
    $is_anonymous = isset($_POST['is_anonymous']);
    
    $jina_mgonjwa = $is_anonymous ? 'Bila Jina (Anonymous)' : trim($_POST['jina_mgonjwa'] ?? '');
    if (empty($jina_mgonjwa)) {
        $jina_mgonjwa = 'Bila Jina (Anonymous)';
    }
    
    $simu = $is_anonymous ? null : trim($_POST['simu'] ?? '');

    if (empty($aina_ya_kipimo)) {
        $errorMsg = 'Tafadhali chagua aina ya kipimo ulichofanyiwa leo kabla ya kuwasilisha maoni.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO `maoni_mionzi` 
                (`jina_mgonjwa`, `simu`, `aina_ya_kipimo`, `rating_ujumla`, `rating_muda`, `rating_wataalamu`, `rating_usafi`, `rating_maelekezo`, `maoni`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $jina_mgonjwa,
                $simu,
                $aina_ya_kipimo,
                $rating_ujumla,
                $rating_muda,
                $rating_wataalamu,
                $rating_usafi,
                $rating_maelekezo,
                $maoni
            ]);
            $success = true;
            $submittedRecord = [
                'id' => $pdo->lastInsertId(),
                'kipimo' => $aina_ya_kipimo,
                'rating' => $rating_ujumla,
                'tarehe' => date('d/m/Y H:i')
            ];
        } catch (PDOException $e) {
            $errorMsg = 'Hitilafu ya mfumo: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    
    <!-- PWA Configuration -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#0284c7">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="e-Mrejesho">
    <link rel="apple-touch-icon" href="assets/images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="assets/images/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="assets/images/icon-512.png">

    <title>e-Mrejesho | Idara ya Mionzi</title>
    <link rel="stylesheet" href="assets/css/style.css?v=3.0">
    <style>
        .form-control {
            font-size: 16px !important;
        }
        .medical-cross-icon {
            display: inline-block;
            width: 14px;
            height: 14px;
            background: var(--primary);
            mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath d='M9 3h6v6h6v6h-6v6H9v-6H3V9h6V3z'/%3E%3C/svg%3E") no-repeat center / contain;
            -webkit-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath d='M9 3h6v6h6v6h-6v6H9v-6H3V9h6V3z'/%3E%3C/svg%3E") no-repeat center / contain;
        }
        .proc-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #cbd5e1;
            margin-bottom: 6px;
            transition: all 0.2s ease;
        }
        .procedure-item input[type="radio"]:checked + .procedure-label .proc-indicator {
            background: var(--primary);
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.25);
        }
    </style>
</head>
<body>
    <div class="bg-decor"></div>

    <div class="container">
        
        <!-- Hospital Header -->
        <header class="mobile-app-header">
            <div class="mobile-dept-row">
                <span class="dept-pill">
                    <span class="medical-cross-icon"></span> Idara ya Mionzi na Taswira
                </span>
                <span class="live-pill">
                    Fomu Rasmi
                </span>
            </div>
            <h1 class="mobile-title">Tathmini ya Huduma ya Mgonjwa</h1>
            <p class="mobile-subtitle">
                Mpendwa mteja, maoni yako ni muhimu katika kuendelea kuboresha ubora, usalama na ufanisi wa huduma za mionzi.
            </p>
        </header>

        <!-- PWA Install Prompt Banner -->
        <div id="pwaInstallCard" style="display: none; background: #ffffff; border: 1.5px solid #bae6fd; border-radius: var(--radius-md); padding: 0.85rem 1rem; margin-bottom: 0.85rem; box-shadow: var(--shadow-sm); align-items: center; justify-content: space-between; gap: 0.75rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <img src="assets/images/icon-96.png" alt="e-Mrejesho" style="width: 38px; height: 38px; border-radius: 8px; flex-shrink: 0;">
                <div>
                    <div style="font-size: 0.86rem; font-weight: 800; color: var(--dark); line-height: 1.2;">Sakinisha e-Mrejesho App</div>
                    <div style="font-size: 0.74rem; color: var(--muted);">Weka njia ya mkato kwenye simu yako</div>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 0.35rem;">
                <button type="button" id="btnInstallPwa" class="btn btn-primary btn-sm" style="padding: 0.4rem 0.8rem; font-size: 0.78rem; font-weight: 700; border-radius: var(--radius-full); white-space: nowrap;">
                    Sakinisha
                </button>
                <button type="button" id="btnDismissPwa" style="background: none; border: none; color: var(--muted); font-size: 1.1rem; cursor: pointer; padding: 2px 6px;">✕</button>
            </div>
        </div>

        <!-- Progress Indicator -->
        <div class="step-progress-card no-print">
            <div class="step-label-row">
                <span id="progressText">Hatua 1 ya 3: Aina ya Kipimo</span>
                <span id="progressPercent" style="color: var(--primary); font-weight: 800;">35%</span>
            </div>
            <div class="step-progress-bar">
                <div class="step-progress-fill" id="progressFill" style="width: 35%;"></div>
            </div>
        </div>

        <!-- Main Content Card -->
        <main class="form-card">
            <?php if ($success): ?>
                <!-- Ukurasa wa Shukrani baada ya kutuma -->
                <div class="success-card">
                    <div class="success-icon">✓</div>
                    <h2 class="success-title">Maoni Yamewasilishwa Kikamilifu</h2>
                    <p class="success-desc">
                        Asante kwa kushiriki maoni yako kuhusu huduma ya <strong><?= htmlspecialchars($submittedRecord['kipimo'] ?? 'Mionzi') ?></strong>. Mrejesho huu umepokelewa na kitengo cha usimamizi wa ubora wa huduma.
                    </p>
                    
                    <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: var(--radius-md); padding: 0.85rem 1rem; margin-bottom: 1.5rem; text-align: left; font-size: 0.85rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                            <span style="color: var(--muted);">Kumbukumbu Namba:</span>
                            <strong style="color: var(--dark);">#<?= htmlspecialchars($submittedRecord['id'] ?? '1') ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                            <span style="color: var(--muted);">Kipimo:</span>
                            <strong style="color: var(--primary);"><?= htmlspecialchars($submittedRecord['kipimo'] ?? '-') ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--muted);">Tarehe na Saa:</span>
                            <span style="color: var(--slate); font-weight: 600;"><?= htmlspecialchars($submittedRecord['tarehe'] ?? date('d/m/Y H:i')) ?></span>
                        </div>
                    </div>

                    <a href="index.php" class="btn btn-primary" style="height: 48px;">
                        Wasilisha Maoni Mengine
                    </a>
                </div>

            <?php else: ?>

                <?php if (!empty($errorMsg)): ?>
                    <div style="background: var(--danger-light); color: var(--danger); padding: 0.85rem 1rem; border-radius: var(--radius-sm); margin-bottom: 1rem; font-weight: 700; font-size: 0.88rem;">
                        <?= htmlspecialchars($errorMsg) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="index.php" id="feedbackForm">
                    
                    <!-- Hatua ya 1: Aina ya Kipimo -->
                    <div class="section-label">
                        1. Aina ya Kipimo Ulichofanyiwa:
                    </div>
                    <p class="section-desc">Tafadhali chagua kipimo ulichopata katika idara ya mionzi leo:</p>

                    <div class="procedure-grid">
                        <div class="procedure-item">
                            <input type="radio" name="aina_ya_kipimo" id="proc_xray" value="X-Ray (Mionzi ya Kawaida)" required>
                            <label for="proc_xray" class="procedure-label">
                                <span class="proc-indicator"></span>
                                <span class="procedure-name">X-Ray</span>
                                <span class="procedure-sub">Mionzi ya Kawaida</span>
                            </label>
                        </div>

                        <div class="procedure-item">
                            <input type="radio" name="aina_ya_kipimo" id="proc_ultra" value="Ultrasound (Mawimbi ya Sauti)">
                            <label for="proc_ultra" class="procedure-label">
                                <span class="proc-indicator"></span>
                                <span class="procedure-name">Ultrasound</span>
                                <span class="procedure-sub">Mawimbi ya Sauti</span>
                            </label>
                        </div>

                        <div class="procedure-item">
                            <input type="radio" name="aina_ya_kipimo" id="proc_ct" value="CT-Scan (Mionzi ya Tabaka)">
                            <label for="proc_ct" class="procedure-label">
                                <span class="proc-indicator"></span>
                                <span class="procedure-name">CT-Scan</span>
                                <span class="procedure-sub">Mionzi ya Tabaka</span>
                            </label>
                        </div>

                        <div class="procedure-item">
                            <input type="radio" name="aina_ya_kipimo" id="proc_mri" value="MRI (Upigaji Picha wa Sumaku)">
                            <label for="proc_mri" class="procedure-label">
                                <span class="proc-indicator"></span>
                                <span class="procedure-name">MRI</span>
                                <span class="procedure-sub">Taswira ya Sumaku</span>
                            </label>
                        </div>

                        <div class="procedure-item">
                            <input type="radio" name="aina_ya_kipimo" id="proc_mammo" value="Mammography (Mionzi ya Matiti)">
                            <label for="proc_mammo" class="procedure-label">
                                <span class="proc-indicator"></span>
                                <span class="procedure-name">Mammography</span>
                                <span class="procedure-sub">Uchunguzi wa Matiti</span>
                            </label>
                        </div>

                        <div class="procedure-item">
                            <input type="radio" name="aina_ya_kipimo" id="proc_other" value="Kipimo Kingine">
                            <label for="proc_other" class="procedure-label">
                                <span class="proc-indicator"></span>
                                <span class="procedure-name">Kipimo Kingine</span>
                                <span class="procedure-sub">Huduma Nyingine</span>
                            </label>
                        </div>
                    </div>

                    <!-- Hatua ya 2: Viwango vya Huduma -->
                    <div class="section-label" style="margin-top: 1.4rem;">
                        2. Tathmini ya Ubora wa Huduma:
                    </div>
                    <p class="section-desc">Weka alama ya nyota kuanzia nyota 1 (Haikuridhisha) hadi 5 (Inaridhisha Sana):</p>

                    <!-- Swali 1: Ujumla -->
                    <div class="rating-block">
                        <div class="rating-question">
                            <div class="rating-title">
                                Ubora wa Huduma kwa Ujumla
                            </div>
                            <span class="rating-hint" id="hint_ujumla">Inaridhisha Sana (5/5)</span>
                        </div>
                        <div class="star-rating" data-target="hint_ujumla" data-type="general">
                            <input type="radio" id="ujumla_5" name="rating_ujumla" value="5" checked><label for="ujumla_5" title="Inaridhisha Sana">★</label>
                            <input type="radio" id="ujumla_4" name="rating_ujumla" value="4"><label for="ujumla_4" title="Inaridhisha">★</label>
                            <input type="radio" id="ujumla_3" name="rating_ujumla" value="3"><label for="ujumla_3" title="Wastani">★</label>
                            <input type="radio" id="ujumla_2" name="rating_ujumla" value="2"><label for="ujumla_2" title="Hairidhishi">★</label>
                            <input type="radio" id="ujumla_1" name="rating_ujumla" value="1"><label for="ujumla_1" title="Haikuridhisha Kabisa">★</label>
                        </div>
                    </div>

                    <!-- Swali 2: Muda wa kusubiri -->
                    <div class="rating-block">
                        <div class="rating-question">
                            <div class="rating-title">
                                Muda wa Kusubiri Hadi Kuhudumiwa
                            </div>
                            <span class="rating-hint" id="hint_muda">Kwa Wakati Stahiki (5/5)</span>
                        </div>
                        <div class="star-rating" data-target="hint_muda" data-type="time">
                            <input type="radio" id="muda_5" name="rating_muda" value="5" checked><label for="muda_5" title="Kwa Wakati Stahiki">★</label>
                            <input type="radio" id="muda_4" name="rating_muda" value="4"><label for="muda_4" title="Muda Mzuri">★</label>
                            <input type="radio" id="muda_3" name="rating_muda" value="3"><label for="muda_3" title="Muda wa Wastani">★</label>
                            <input type="radio" id="muda_2" name="rating_muda" value="2"><label for="muda_2" title="Muda Mrefu">★</label>
                            <input type="radio" id="muda_1" name="rating_muda" value="1"><label for="muda_1" title="Ucheleweshaji Mkubwa">★</label>
                        </div>
                    </div>

                    <!-- Swali 3: Ukarimu wa Wataalamu -->
                    <div class="rating-block">
                        <div class="rating-question">
                            <div class="rating-title">
                                Ukarimu na Weledi wa Wataalamu
                            </div>
                            <span class="rating-hint" id="hint_wataalamu">Weledi wa Hali ya Juu (5/5)</span>
                        </div>
                        <div class="star-rating" data-target="hint_wataalamu" data-type="staff">
                            <input type="radio" id="wataalamu_5" name="rating_wataalamu" value="5" checked><label for="wataalamu_5" title="Weledi wa Hali ya Juu">★</label>
                            <input type="radio" id="wataalamu_4" name="rating_wataalamu" value="4"><label for="wataalamu_4" title="Nzuri">★</label>
                            <input type="radio" id="wataalamu_3" name="rating_wataalamu" value="3"><label for="wataalamu_3" title="Wastani">★</label>
                            <input type="radio" id="wataalamu_2" name="rating_wataalamu" value="2"><label for="wataalamu_2" title="Chini ya Matarajio">★</label>
                            <input type="radio" id="wataalamu_1" name="rating_wataalamu" value="1"><label for="wataalamu_1" title="Hairidhishi">★</label>
                        </div>
                    </div>

                    <!-- Swali 4: Usafi na Mazingira -->
                    <div class="rating-block">
                        <div class="rating-question">
                            <div class="rating-title">
                                Usafi na Faragha ya Chumba cha Kipimo
                            </div>
                            <span class="rating-hint" id="hint_usafi">Safi na Faragha ya Kutosha (5/5)</span>
                        </div>
                        <div class="star-rating" data-target="hint_usafi" data-type="clean">
                            <input type="radio" id="usafi_5" name="rating_usafi" value="5" checked><label for="usafi_5" title="Safi na Faragha ya Kutosha">★</label>
                            <input type="radio" id="usafi_4" name="rating_usafi" value="4"><label for="usafi_4" title="Nzuri">★</label>
                            <input type="radio" id="usafi_3" name="rating_usafi" value="3"><label for="usafi_3" title="Wastani">★</label>
                            <input type="radio" id="usafi_2" name="rating_usafi" value="2"><label for="usafi_2" title="Inahitaji Maboresho">★</label>
                            <input type="radio" id="usafi_1" name="rating_usafi" value="1"><label for="usafi_1" title="Hairidhishi">★</label>
                        </div>
                    </div>

                    <!-- Swali 5: Maelekezo kabla ya kipimo -->
                    <div class="rating-block">
                        <div class="rating-question">
                            <div class="rating-title">
                                Ufafanuzi na Maelekezo Kuhusu Kipimo
                            </div>
                            <span class="rating-hint" id="hint_maelekezo">Maelekezo Yalikuwa Wazi Sana (5/5)</span>
                        </div>
                        <div class="star-rating" data-target="hint_maelekezo" data-type="instructions">
                            <input type="radio" id="maelekezo_5" name="rating_maelekezo" value="5" checked><label for="maelekezo_5" title="Maelekezo Yalikuwa Wazi Sana">★</label>
                            <input type="radio" id="maelekezo_4" name="rating_maelekezo" value="4"><label for="maelekezo_4" title="Yalieleweka">★</label>
                            <input type="radio" id="maelekezo_3" name="rating_maelekezo" value="3"><label for="maelekezo_3" title="Wastani">★</label>
                            <input type="radio" id="maelekezo_2" name="rating_maelekezo" value="2"><label for="maelekezo_2" title="Hayakueleweka Vizuri">★</label>
                            <input type="radio" id="maelekezo_1" name="rating_maelekezo" value="1"><label for="maelekezo_1" title="Hakuna Maelekezo">★</label>
                        </div>
                    </div>

                    <!-- Hatua ya 3: Kauli za Haraka za Mrejesho -->
                    <div class="section-label" style="margin-top: 1.4rem;">
                        3. Maoni ya Ziada (Hiari):
                    </div>
                    <p class="section-desc">Gusa kauli inayowakilisha mtazamo wako au andika maoni yako:</p>

                    <div class="quick-tags-wrap">
                        <button type="button" class="quick-tag-chip" data-tag="Huduma ilitolewa kwa wakati stahiki">
                            Huduma ilitolewa kwa wakati
                        </button>
                        <button type="button" class="quick-tag-chip" data-tag="Wataalamu walikuwa wasikivu na wenye ukarimu">
                            Wataalamu wakarimu na wasikivu
                        </button>
                        <button type="button" class="quick-tag-chip" data-tag="Chumba cha kipimo kilikuwa safi na chenye faragha">
                            Mazingira safi na faragha nzuri
                        </button>
                        <button type="button" class="quick-tag-chip" data-tag="Nilipata maelekezo ya kutosha na ya wazi kabla ya kipimo">
                            Maelekezo ya kipimo yalikuwa wazi
                        </button>
                        <button type="button" class="quick-tag-chip" data-tag="Muda wa kusubiri kabla ya kipimo ulikuwa mrefu">
                            Muda wa kusubiri ulikuwa mrefu
                        </button>
                    </div>

                    <!-- Sanduku la Maoni ya Maandishi -->
                    <div class="form-group">
                        <textarea name="maoni" id="maoni" class="form-control" placeholder="Andika maoni, ushauri au mapendekezo yako ya kuboresha huduma..." rows="3"></textarea>
                    </div>

                    <!-- Hatua ya 4: Usiri & Utambulisho -->
                    <div class="switch-card" id="toggleAnonymousCard">
                        <div class="switch-label-wrap">
                            <div class="switch-text">
                                <h4>Wasilisha Maoni Bila Kutaja Jina</h4>
                                <p>Usiri unalindwa. Hutatambulika iwapo utaacha chaguo hili likiwa limewashwa.</p>
                            </div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="is_anonymous" id="is_anonymous" checked>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <!-- Taarifa za Hiari -->
                    <div id="patientInfoFields" style="display: none; background: #f8fafc; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; border: 1.5px solid var(--border);">
                        <div class="form-group">
                            <label for="jina_mgonjwa" class="form-label">Jina Kamili (Hiari):</label>
                            <input type="text" name="jina_mgonjwa" id="jina_mgonjwa" class="form-control" placeholder="Mfano: Juma Shabani" autocomplete="name">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="simu" class="form-label">Namba ya Simu (Hiari):</label>
                            <input type="tel" name="simu" id="simu" class="form-control" placeholder="Mfano: 07XXXXXXXX (kwa ajili ya mrejesho)" inputmode="tel" autocomplete="tel">
                        </div>
                    </div>

                    <!-- Kitufe cha Kuwasilisha Ndani ya Ukurasa -->
                    <div style="margin-top: 1rem;">
                        <button type="submit" name="submit_feedback" class="btn btn-primary" id="btnSubmitForm">
                            Wasilisha Maoni
                        </button>
                    </div>
                </form>

            <?php endif; ?>
        </main>

        <!-- Footer Links -->
        <footer style="text-align: center; margin-top: 1.5rem; font-size: 0.8rem; color: var(--muted);" class="no-print">
            <p>Mfumo wa e-Mrejesho &bull; Idara ya Mionzi na Taswira za Kimatibabu</p>
            <div style="margin-top: 0.4rem; display: flex; gap: 0.8rem; justify-content: center; flex-wrap: wrap;">
                <a href="qrcode.php" style="color: var(--primary); text-decoration: none; font-weight: 700;">Bango la QR Code</a>
                <span>&bull;</span>
                <a href="radioadmin/login.php" style="color: var(--muted); text-decoration: none;">Sehemu ya Msimamizi</a>
            </div>
        </footer>

    </div>

    <?php if (!$success): ?>
    <!-- Sticky Bottom Bar kwa ajili ya simu za mkononi -->
    <div class="sticky-bottom-bar no-print" id="stickyBottomBar">
        <div class="sticky-bottom-inner">
            <div id="stickySelectedBadge" style="display: none; font-size: 0.8rem; font-weight: 700; color: var(--primary-dark); background: var(--primary-light); padding: 0.4rem 0.75rem; border-radius: var(--radius-full); white-space: nowrap;">
                <span id="badgeName">X-Ray</span>
            </div>
            <button type="button" class="btn btn-primary sticky-btn" id="stickySubmitBtn" onclick="triggerSubmit()">
                Wasilisha Maoni
            </button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Scripts -->
    <script>
        function triggerHaptic(duration = 15) {
            if ('vibrate' in navigator) {
                try { navigator.vibrate(duration); } catch(e) {}
            }
        }

        // Lebo rasmi za viwango vya nyota (Bila emoji za katuni)
        const ratingLabelsMap = {
            general: {
                "5": "Inaridhisha Sana (5/5)",
                "4": "Inaridhisha (4/5)",
                "3": "Wastani (3/5)",
                "2": "Hairidhishi (2/5)",
                "1": "Haikuridhisha Kabisa (1/5)"
            },
            time: {
                "5": "Kwa Wakati Stahiki (5/5)",
                "4": "Muda Mzuri (4/5)",
                "3": "Muda wa Wastani (3/5)",
                "2": "Muda Mrefu (2/5)",
                "1": "Ucheleweshaji Mkubwa (1/5)"
            },
            staff: {
                "5": "Weledi wa Hali ya Juu (5/5)",
                "4": "Nzuri (4/5)",
                "3": "Wastani (3/5)",
                "2": "Chini ya Matarajio (2/5)",
                "1": "Hairidhishi (1/5)"
            },
            clean: {
                "5": "Safi na Faragha ya Kutosha (5/5)",
                "4": "Nzuri (4/5)",
                "3": "Wastani (3/5)",
                "2": "Inahitaji Maboresho (2/5)",
                "1": "Hairidhishi (1/5)"
            },
            instructions: {
                "5": "Maelekezo Yalikuwa Wazi Sana (5/5)",
                "4": "Yalieleweka (4/5)",
                "3": "Wastani (3/5)",
                "2": "Hayakueleweka Vizuri (2/5)",
                "1": "Hakuna Maelekezo (1/5)"
            }
        };

        // Kusasisha lebo za nyota
        document.querySelectorAll('.star-rating').forEach(group => {
            const targetHintId = group.getAttribute('data-target');
            const type = group.getAttribute('data-type') || 'general';
            const hintElem = document.getElementById(targetHintId);
            const parentBlock = group.closest('.rating-block');
            
            group.querySelectorAll('input').forEach(input => {
                input.addEventListener('change', () => {
                    triggerHaptic(12);
                    if (parentBlock) parentBlock.classList.add('touched');
                    const map = ratingLabelsMap[type] || ratingLabelsMap.general;
                    if (hintElem && map[input.value]) {
                        hintElem.textContent = map[input.value];
                    }
                    updateProgress();
                });
            });
        });

        // Kusikiliza uchaguzi wa Kipimo cha Mionzi
        const procRadios = document.querySelectorAll('input[name="aina_ya_kipimo"]');
        const stickyBadge = document.getElementById('stickySelectedBadge');
        const badgeName = document.getElementById('badgeName');

        procRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                triggerHaptic(15);
                const label = this.nextElementSibling;
                const name = label.querySelector('.procedure-name')?.textContent || this.value;
                
                if (stickyBadge && badgeName) {
                    badgeName.textContent = name;
                    stickyBadge.style.display = 'inline-flex';
                }
                updateProgress();
            });
        });

        // Quick Feedback Tags (Chips)
        const textarea = document.getElementById('maoni');
        document.querySelectorAll('.quick-tag-chip').forEach(chip => {
            chip.addEventListener('click', function() {
                triggerHaptic(15);
                const tagText = this.getAttribute('data-tag');
                this.classList.toggle('active');

                if (!textarea) return;

                let currentVal = textarea.value.trim();
                if (this.classList.contains('active')) {
                    if (currentVal === '') {
                        textarea.value = tagText;
                    } else if (!currentVal.includes(tagText)) {
                        textarea.value = currentVal + '; ' + tagText;
                    }
                } else {
                    textarea.value = currentVal
                        .replace('; ' + tagText, '')
                        .replace(tagText + '; ', '')
                        .replace(tagText, '')
                        .trim();
                }
                updateProgress();
            });
        });

        // Anonymous Switch
        const anonCheckbox = document.getElementById('is_anonymous');
        const patientFields = document.getElementById('patientInfoFields');
        
        if (anonCheckbox && patientFields) {
            anonCheckbox.addEventListener('change', function() {
                triggerHaptic(15);
                patientFields.style.display = this.checked ? 'none' : 'block';
            });
        }

        // Progress Calculation
        function updateProgress() {
            let score = 30;
            const selectedProc = document.querySelector('input[name="aina_ya_kipimo"]:checked');
            if (selectedProc) score += 35;

            const comments = document.getElementById('maoni')?.value.trim();
            if (comments && comments.length > 3) score += 35;

            score = Math.min(score, 100);

            const fill = document.getElementById('progressFill');
            const percent = document.getElementById('progressPercent');
            const text = document.getElementById('progressText');

            if (fill) fill.style.width = score + '%';
            if (percent) percent.textContent = score + '%';
            if (text) {
                if (score < 60) text.textContent = 'Hatua 1: Aina ya Kipimo';
                else if (score < 90) text.textContent = 'Hatua 2: Tathmini ya Huduma';
                else text.textContent = 'Hatua 3: Tayari Kuwasilisha';
            }
        }

        // Submit trigger
        function triggerSubmit() {
            triggerHaptic(20);
            const form = document.getElementById('feedbackForm');
            const selectedProc = document.querySelector('input[name="aina_ya_kipimo"]:checked');
            
            if (!selectedProc) {
                alert('Tafadhali chagua aina ya kipimo ulichofanyiwa kabla ya kuwasilisha maoni.');
                window.scrollTo({ top: 120, behavior: 'smooth' });
                return;
            }

            form.submit();
        }

        // Service Worker Registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('./sw.js')
                    .then(registration => {
                        console.log('e-Mrejesho PWA Service Worker Registered:', registration.scope);
                    })
                    .catch(err => {
                        console.warn('Service Worker registration skipped:', err);
                    });
            });
        }

        // PWA Install Prompt Listener
        let deferredPrompt;
        const pwaCard = document.getElementById('pwaInstallCard');
        const btnInstallPwa = document.getElementById('btnInstallPwa');
        const btnDismissPwa = document.getElementById('btnDismissPwa');

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            if (pwaCard) pwaCard.style.display = 'flex';
        });

        if (btnInstallPwa) {
            btnInstallPwa.addEventListener('click', async () => {
                triggerHaptic(15);
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    const { outcome } = await deferredPrompt.userChoice;
                    deferredPrompt = null;
                }
                if (pwaCard) pwaCard.style.display = 'none';
            });
        }

        if (btnDismissPwa && pwaCard) {
            btnDismissPwa.addEventListener('click', () => {
                pwaCard.style.display = 'none';
            });
        }

        window.addEventListener('appinstalled', () => {
            if (pwaCard) pwaCard.style.display = 'none';
        });
    </script>
</body>
</html>