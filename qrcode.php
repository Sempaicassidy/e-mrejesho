<?php
// Kutambua kiotomatiki anwani ya mtandao (Host & URL)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$currentDir = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$defaultUrl = $protocol . $host . $currentDir . '/index.php';

// Kupata IP halisi ya LAN ya kompyuta hii (kusaidia watumiaji wa simu kwenye Wi-Fi ya hospitali)
$localIp = gethostbyname(gethostname());
$lanUrl = $protocol . $localIp . $currentDir . '/index.php';
?>
<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="theme-color" content="#0284c7">
    <link rel="manifest" href="manifest.json">
    <link rel="apple-touch-icon" href="assets/images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="assets/images/icon-192.png">
    <title>Bango la QR Code | Idara ya Mionzi (e-Mrejesho)</title>
    <link rel="stylesheet" href="assets/css/style.css?v=2.0">
    <script src="assets/js/qrcode.min.js"></script>
    <style>
        .control-bar {
            background: #ffffff;
            border-bottom: 1px solid var(--border);
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
        }
        .control-flex {
            max-width: 900px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .url-box {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex: 1;
            min-width: 100%;
        }
        @media (min-width: 600px) {
            .url-box {
                min-width: 280px;
            }
        }
        .url-input {
            width: 100%;
            padding: 0.55rem 0.8rem;
            font-size: 0.88rem;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-sm);
        }
        .btn-group {
            display: flex;
            gap: 0.6rem;
        }
        .helper-text {
            font-size: 0.78rem;
            color: var(--muted);
            margin-top: 0.4rem;
        }
    </style>
</head>
<body style="background: #f1f5f9;">

    <!-- Control bar (Inaonekana tu kwenye kioo, haitokei wakati wa ku-print) -->
    <div class="control-bar no-print">
        <div class="control-flex">
            <div>
                <a href="index.php" style="color: var(--primary); text-decoration: none; font-weight: 700; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 4px;">
                    ← Rudi Kwenye Fomu
                </a>
                <div style="font-size: 1.1rem; font-weight: 800; color: var(--dark); margin-top: 2px;">
                    Bango Rasmi la QR Code (Idara ya Mionzi)
                </div>
            </div>

            <div class="url-box">
                <div style="width: 100%;">
                    <div style="font-size: 0.8rem; font-weight: 700; color: var(--slate); margin-bottom: 3px;">
                        Kiungo (URL) ya Kufungua Fomu Simuni:
                    </div>
                    <input type="text" id="qrTargetUrl" class="url-input" value="<?= htmlspecialchars($defaultUrl) ?>" placeholder="Ingiza URL hapa...">
                    <div class="helper-text">
                        💡 <strong>Kidokezo:</strong> Kwa wagonjwa wanaotumia simu kupitia Wi-Fi ya hospitali, tumia IP: 
                        <a href="javascript:void(0)" onclick="setUrl('<?= htmlspecialchars($lanUrl) ?>')" style="color: var(--primary); font-weight: 700; text-decoration: underline;">
                            <?= htmlspecialchars($lanUrl) ?>
                        </a>
                    </div>
                </div>
            </div>

            <div class="btn-group">
                <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
                    Chapisha Bango (Print)
                </button>
                <a href="<?= htmlspecialchars($defaultUrl) ?>" target="_blank" id="testLink" class="btn btn-secondary btn-sm">
                    Fungua Fomu
                </a>
            </div>
        </div>
    </div>

    <!-- Bango la Kuchapisha (Printable Poster) -->
    <div class="poster-container">
        <div class="poster-sheet" id="posterArea">
            
            <div style="margin-bottom: 0.8rem;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="#0284c7" style="display: inline-block;">
                    <path d="M9 3h6v6h6v6h-6v6H9v-6H3V9h6V3z"/>
                </svg>
            </div>
            
            <div class="poster-header-small">
                JAMHURI YA MUUNGANO WA TANZANIA &bull; WIZARA YA AFYA
            </div>
            
            <h1 class="poster-title">
                IDARA YA MIONZI NA TASWIRA
            </h1>

            <div class="poster-subtitle">
                "Mrejesho Wako Ni Dira Yetu Ya Kuboresha Huduma"
            </div>

            <div style="display: flex; justify-content: center; gap: 0.6rem; margin-bottom: 1.2rem; flex-wrap: wrap;">
                <span class="badge badge-xray">X-Ray</span>
                <span class="badge badge-ultrasound">Ultrasound</span>
                <span class="badge badge-ct">CT-Scan</span>
                <span class="badge badge-mri">MRI</span>
                <span class="badge badge-other">Mammography</span>
            </div>

            <!-- Eneo la QR Code -->
            <div class="qr-box-wrap">
                <div id="qrcodeDisplay"></div>
            </div>

            <div style="font-size: 1.2rem; font-weight: 800; color: #0284c7; letter-spacing: 0.5px; text-transform: uppercase;">
                SCAN HAPA KWA SIMU YAKO KUTOA MAONI
            </div>

            <div style="color: #64748b; font-size: 0.95rem; margin-top: 0.3rem;">
                Inachukua chini ya dakika 1 &bull; Huhitaji kutoa jina lako (Anonymous)
            </div>

            <!-- Hatua 3 za mgonjwa -->
            <div class="qr-steps">
                <div class="qr-step-card">
                    <div class="step-num">1</div>
                    <div class="step-text">
                        Fungua kamera ya simu yako ya mkononi (au app ya QR Scanner).
                    </div>
                </div>

                <div class="qr-step-card">
                    <div class="step-num">2</div>
                    <div class="step-text">
                        Elekeza kamera kwenye QR Code iliyopo juu hapa.
                    </div>
                </div>

                <div class="qr-step-card">
                    <div class="step-num">3</div>
                    <div class="step-text">
                        Gusa kiungo (link) itakayotokea, kisha jaza tathmini ya huduma uliyopata.
                    </div>
                </div>
            </div>

            <!-- Poster Footer -->
            <div class="poster-footer">
                🔒 <strong>Usiri Unalindwa:</strong> Maoni yako yanapokelewa na kusimamiwa moja kwa moja na Uongozi wa Idara ya Mionzi kwa lengo la kuboresha ubora wa huduma na usalama wa wagonjwa.
                <div style="margin-top: 0.4rem; font-size: 0.78rem; color: #94a3b8;">
                    Mfumo wa e-Mrejesho &bull; Radiolojia na Taswira za Kimatibabu
                </div>
            </div>

        </div>
    </div>

    <script>
        let qrcodeInstance = null;

        function generateQR(text) {
            const container = document.getElementById("qrcodeDisplay");
            container.innerHTML = "";
            
            qrcodeInstance = new QRCode(container, {
                text: text,
                width: 230,
                height: 230,
                colorDark: "#0284c7",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        }

        function setUrl(url) {
            document.getElementById('qrTargetUrl').value = url;
            updateQr();
        }

        function updateQr() {
            const url = document.getElementById('qrTargetUrl').value.trim();
            if (url) {
                generateQR(url);
                document.getElementById('testLink').href = url;
            }
        }

        document.getElementById('qrTargetUrl').addEventListener('input', updateQr);

        // Anzisha QR code wakati ukurasa unapofunguka
        window.addEventListener('DOMContentLoaded', () => {
            const initialUrl = document.getElementById('qrTargetUrl').value;
            generateQR(initialUrl);
        });
    </script>
</body>
</html>
