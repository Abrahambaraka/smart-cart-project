<?php
session_start();
require 'config.php';
$message = '';

// --- CORRECTION : Logique de redirection complète pour les utilisateurs déjà connectés ---
if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
    switch ($_SESSION['user_type']) {
        case 'superadmin':
            header('Location: superadmin_dashboard.php');
            exit;
        case 'school':
            header('Location: school_dashboard.php');
            exit;
        case 'admin':
            header('Location: dashboard.php');
            exit;
        default:
            // Pour les autres types (ex: student), on les redirige vers la page publique
            header('Location: site_public.php');
            exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $matricule = trim($_POST['matricule']);

    if (empty($nom) || empty($matricule)) {
        $message = "Veuillez remplir tous les champs.";
    } else {
        $stmt = $pdo->prepare("SELECT id, nom, type, ecole_id FROM users WHERE nom = :nom AND matricule = :matricule");
        $stmt->execute([':nom' => $nom, ':matricule' => $matricule]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Stocker les informations essentielles dans la session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['user_type'] = $user['type'];

            // --- CORRECTION : Redirection basée sur le type d'utilisateur (ajout du cas 'admin') ---
            switch ($user['type']) {
                case 'superadmin':
                    header('Location: superadmin_dashboard.php');
                    break;
                case 'school':
                    $_SESSION['ecole_id'] = $user['ecole_id']; // TRÈS IMPORTANT
                    header('Location: school_dashboard.php');
                    break;
                case 'admin':
                    header('Location: dashboard.php');
                    break;
                default:
                    $message = "Type de compte non autorisé à se connecter.";
                    break;
            }
            exit;
        } else {
            $message = "Identifiants incorrects. Veuillez réessayer.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - SMART CONGO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #610e62;
            --secondary-color: #a828aa;
            --accent-color: #f36e01;
            --light-bg: #f8fafc;
            --light-purple-bg: #e9e3f7;
            --border-color: #d1c6e0;
        }
        body {
            min-height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--light-bg) 0%, var(--light-purple-bg) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        .background-anim {
            position: absolute; top: 0; left: 0; width: 100vw; height: 100vh;
            z-index: 0; pointer-events: none; overflow: hidden;
        }
        .circle {
            position: absolute; border-radius: 50%; opacity: 0.15;
            animation: float 12s infinite alternate ease-in-out;
        }
        .circle1 { width: 400px; height: 400px; background: var(--secondary-color); left: -120px; top: -80px; animation-delay: 0s;}
        .circle2 { width: 250px; height: 250px; background: var(--primary-color); right: -80px; top: 60px; animation-delay: 2s;}
        .circle3 { width: 180px; height: 180px; background: var(--accent-color); left: 60vw; bottom: -60px; animation-delay: 4s;}
        @keyframes float {
            0% { transform: translateY(0) scale(1); }
            100% { transform: translateY(40px) scale(1.08); }
        }
        .login-container {
            position: relative; z-index: 1; max-width: 380px; width: 100%;
            background: #fff; border-radius: 20px;
            box-shadow: 0 10px 40px rgba(97,14,98,0.12), 0 2px 6px rgba(97,14,98,0.08);
            padding: 40px 32px 32px 32px; animation: fadeIn 0.8s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .login-header { text-align: center; margin-bottom: 24px; }
        .logo {
            width: 75px; height: 75px; border-radius: 18px; object-fit: cover;
            box-shadow: 0 4px 12px rgba(97,14,98,0.15); margin-bottom: 12px;
        }
        .login-header h1 {
            font-size: 1.8rem; color: var(--primary-color); margin: 0;
            font-weight: 700; letter-spacing: 1px;
        }
        .form-group { margin-bottom: 24px; position: relative; }
        .form-group label {
            display: block; margin-bottom: 8px; font-weight: 600;
            color: var(--primary-color); font-size: 0.95rem;
        }
        .input-wrapper { position: relative; }
        .form-group input {
            width: 100%; padding: 12px 40px; box-sizing: border-box;
            border: 2px solid var(--border-color); border-radius: 10px;
            font-size: 1rem; background: #faf8fc; color: #333;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .form-group input:focus {
            outline: none; border-color: var(--secondary-color);
            background: #fff; box-shadow: 0 0 0 4px rgba(168, 40, 170, 0.1);
        }
        .form-group .input-icon {
            position: absolute; left: 15px; top: 50%; transform: translateY(-50%);
            color: var(--secondary-color); font-size: 1.1em;
        }
        .toggle-matricule {
            position: absolute; right: 15px; top: 50%; transform: translateY(-50%);
            cursor: pointer; color: var(--secondary-color); font-size: 1.1em;
            transition: color 0.2s;
        }
        .toggle-matricule:hover { color: var(--accent-color); }
        .login-button {
            width: 100%; padding: 14px;
            background: linear-gradient(90deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white; border: none; border-radius: 10px; font-size: 1.1rem;
            font-weight: 600; cursor: pointer;
            box-shadow: 0 4px 15px rgba(97,14,98,0.2);
            transition: all 0.3s ease;
            display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .login-button:hover:not(:disabled) {
            transform: translateY(-3px); box-shadow: 0 6px 20px rgba(97,14,98,0.3);
        }
        .login-button:disabled { background: var(--secondary-color); cursor: wait; opacity: 0.8; }
        .login-button .button-text { transition: opacity 0.2s; }
        .login-button .spinner { display: none; }
        .login-button.loading .spinner { display: block; animation: spin 1s linear infinite; }
        .login-button.loading .button-text, .login-button.loading .fa-arrow-right { display: none; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .message.error {
            background: #ffeaea; color: #c0392b; border-left: 4px solid #c0392b;
            padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;
            font-size: 1rem; text-align: center; animation: slideDown 0.5s ease-out;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .login-footer { text-align: center; margin-top: 24px; color: #888; font-size: 0.9rem; }
        
        /* --- Mobile Design Improvements --- */
        @media (max-width: 600px) {
            body {
                align-items: flex-start; /* Align container to the top */
                padding-top: 5vh;
            }
            .login-container { 
                padding: 32px 24px; 
                max-width: 90vw;
                box-shadow: 0 8px 30px rgba(97,14,98,0.1);
            }
            .logo {
                width: 65px; 
                height: 65px;
            }
            .login-header h1 {
                font-size: 1.5rem; 
            }
            .form-group {
                margin-bottom: 20px;
            }
            .form-group input {
                padding: 11px 40px;
                font-size: 0.95rem;
            }
            .login-button {
                padding: 12px;
                font-size: 1rem;
            }
            .login-footer {
                font-size: 0.85rem;
            }
            .circle1 {
                width: 300px; height: 300px; left: -150px;
            }
            .circle2 {
                width: 200px; height: 200px; right: -100px;
            }
            .circle3 {
                display: none; /* Hide one circle to declutter */
            }
        }
    </style>
</head>
<body>
    <div class="background-anim">
        <div class="circle circle1"></div>
        <div class="circle circle2"></div>
        <div class="circle circle3"></div>
    </div>
    <div class="login-container">
        <header class="login-header">
            <img src="sc4.png" alt="Logo Smart Congo" class="logo">
            <h1>SMART CONGO</h1>
        </header>
        <div class="login-form-container">
            <?php if ($message): ?>
                <div class="message error"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form method="post" action="login.php" autocomplete="off" id="loginForm">
                <div class="form-group">
                    <label for="nom">Nom d'utilisateur</label>
                    <div class="input-wrapper">
                        <i class="fa fa-user input-icon"></i>
                        <input type="text" id="nom" name="nom" required placeholder="Entrez votre nom" autocomplete="username">
                    </div>
                </div>
                <div class="form-group">
                    <label for="matricule">Mot de passe</label>
                    <div class="input-wrapper">
                        <i class="fa fa-lock input-icon"></i>
                        <input type="password" id="matricule" name="matricule" required placeholder="Entrez votre mot de passe" autocomplete="current-password">
                        <i class="fas fa-eye toggle-matricule" id="toggleMatricule"></i>
                    </div>
                </div>
                <button type="submit" class="login-button" id="loginButton">
                    <span class="button-text">Se connecter</span>
                    <i class="fas fa-arrow-right"></i>
                    <i class="fas fa-spinner fa-spin spinner"></i>
                </button>
            </form>
        </div>
        <footer class="login-footer">
            <p>SMART CONGO &copy; 2025</p>
        </footer>
    </div>
    <script>
        const toggleBtn = document.getElementById('toggleMatricule');
        toggleBtn.addEventListener('click', function() {
            const input = document.getElementById('matricule');
            const icon = this;
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        const loginForm = document.getElementById('loginForm');
        loginForm.addEventListener('submit', function() {
            const loginButton = document.getElementById('loginButton');
            loginButton.classList.add('loading');
            loginButton.disabled = true;
        });
    </script>
</body>
</html>