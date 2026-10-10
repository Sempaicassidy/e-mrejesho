<?php
session_start();
require_once __DIR__ . '/../connect.php';

$error = '';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Tafadhali jaza barua pepe na nenosiri.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM `watumiaji` WHERE `barua_pepe` = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['nenosiri'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_name'] = $user['jina'];
                $_SESSION['admin_role'] = $user['cheo'];
                header("Location: index.php");
                exit;
            } else {
                $error = 'Barua pepe au nenosiri sio sahihi.';
            }
        } catch (PDOException $e) {
            $error = 'Hitilafu: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuingia Msimamizi | Idara ya Mionzi</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .login-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .login-box {
            width: 100%;
            max-width: 420px;
            background: #ffffff;
            border-radius: var(--radius-lg);
            padding: 2.2rem 2rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border);
        }
        .login-brand {
            text-align: center;
            margin-bottom: 1.8rem;
        }
        .login-icon {
            font-size: 2.8rem;
            margin-bottom: 0.3rem;
        }
        .login-title {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--dark);
        }
        .login-sub {
            font-size: 0.85rem;
            color: var(--muted);
        }
    </style>
</head>
<body style="background: #f8fafc;">
    <div class="bg-decor"></div>

    <div class="login-wrap">
        <div class="login-box">
            <div class="login-brand">
                <div style="display: inline-flex; align-items: center; justify-content: center; width: 56px; height: 56px; background: var(--primary-light); color: var(--primary); border-radius: 14px; margin-bottom: 0.75rem;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M9 3h6v6h6v6h-6v6H9v-6H3V9h6V3z"/>
                    </svg>
                </div>
                <h2 class="login-title">Idara ya Mionzi</h2>
                <p class="login-sub">Usimamizi wa Maoni ya Wagonjwa (e-Mrejesho)</p>
            </div>

            <?php if (!empty($error)): ?>
                <div style="background: var(--danger-light); color: var(--danger); padding: 0.75rem 1rem; border-radius: var(--radius-sm); margin-bottom: 1.2rem; font-size: 0.88rem; font-weight: 600;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="form-group">
                    <label class="form-label" for="email">Barua Pepe (Email):</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="admin@mionzi.go.tz" value="admin@mionzi.go.tz" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Nenosiri (Password):</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Weka nenosiri..." value="admin123" required>
                </div>

                <div style="margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">
                        Ingia Kwenye Mfumo
                    </button>
                </div>
            </form>

            <div style="margin-top: 1.5rem; text-align: center; font-size: 0.82rem; color: var(--muted); border-top: 1px solid var(--border); padding-top: 1rem;">
                <p>Akaunti ya Awali: <strong>admin@mionzi.go.tz</strong> / <strong>admin123</strong></p>
                <div style="margin-top: 0.8rem;">
                    <a href="../index.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">← Rudi Kwenye Fomu ya Maoni</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
