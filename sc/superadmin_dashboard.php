<?php
session_start();
require 'config.php';
require_once 'auth_check.php';
check_auth(['superadmin']); // Seuls les 'superadmin' sont autorisés

$message = '';
$message_type = ''; // 'error' ou 'success'

// --- Logique d'ajout d'école ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_ecole = trim($_POST['nom_ecole']);
    $adresse = trim($_POST['adresse']);
    $admin_nom = trim($_POST['admin_nom']);
    $admin_email = trim($_POST['admin_email']);
    $admin_password = $_POST['admin_password'];

    if (empty($nom_ecole) || empty($admin_nom) || empty($admin_email) || empty($admin_password)) {
        $message = "Veuillez remplir tous les champs obligatoires.";
        $message_type = 'error';
    } else {
        try {
            $pdo->beginTransaction();

            $stmt_ecole = $pdo->prepare("INSERT INTO ecoles (nom_ecole, adresse) VALUES (?, ?)");
            $stmt_ecole->execute([$nom_ecole, $adresse]);
            $ecole_id = $pdo->lastInsertId();

            // NOTE: Pour une application réelle, il faut HASHER le mot de passe ici.
            // $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
            $stmt_user = $pdo->prepare("INSERT INTO users (nom, prenom, email, matricule, type, ecole_id) VALUES (?, 'Admin', ?, ?, 'school', ?)");
            $stmt_user->execute([$admin_nom, $admin_email, $admin_password, $ecole_id]);

            $pdo->commit();
            $message = "L'école \"".htmlspecialchars($nom_ecole)."\" a été créée avec succès !";
            $message_type = 'success';
        } catch (PDOException $e) {
            $pdo->rollBack();
            if ($e->errorInfo[1] == 1062) {
                $message = "Erreur : L'email ou le nom de l'école existe déjà.";
            } else {
                $message = "Erreur lors de la création : " . $e->getMessage();
            }
            $message_type = 'error';
        }
    }
}

// --- Récupération des écoles ET de leurs admins pour affichage ---
$stmt = $pdo->query("
    SELECT 
        e.nom_ecole, e.adresse, e.created_at,
        u.nom AS admin_nom, u.email AS admin_email, u.matricule AS admin_password
    FROM ecoles e
    LEFT JOIN users u ON e.id = u.ecole_id AND u.type = 'school'
    ORDER BY e.nom_ecole
");
$ecoles_admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Super Admin - SMART CONGO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #610e62;
            --secondary-color: #a828aa;
            --light-bg: #f4f7f6;
            --border-color: #e1e1e1;
            --text-color: #333;
            --success-bg: #e4f8f0;
            --success-border: #28a745;
            --error-bg: #fbeaea;
            --error-border: #c0392b;
        }
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: var(--light-bg);
            margin: 0;
            color: var(--text-color);
        }
        .main-container {
            max-width: 1400px;
            margin: 20px auto;
            padding: 20px;
        }
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 25px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.07);
            margin-bottom: 30px;
        }
        .dashboard-header h1 {
            margin: 0;
            font-size: 1.8rem;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .logout-btn {
            background: #d9534f;
            color: white;
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.3s;
        }
        .logout-btn:hover { background: #c9302c; }
        .dashboard-grid {
            display: grid;
            grid-template-columns: 400px 1fr;
            gap: 30px;
        }
        .card {
            background: #fff;
            padding: 25px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.07);
        }
        .card h2 {
            font-size: 1.5rem;
            color: var(--primary-color);
            border-bottom: 2px solid var(--secondary-color);
            padding-bottom: 10px;
            margin-top: 0;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 8px; color: #555; }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            box-sizing: border-box;
        }
        .btn-primary {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            background-color: var(--primary-color);
            color: white;
            transition: all 0.3s ease;
        }
        .btn-primary:hover { background-color: var(--secondary-color); transform: translateY(-2px); }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            text-align: center;
            font-weight: 500;
            border: 1px solid;
        }
        .message.success { background-color: var(--success-bg); color: var(--success-border); border-color: var(--success-border); }
        .message.error { background-color: var(--error-bg); color: var(--error-border); border-color: var(--error-border); }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            text-align: left;
            padding: 12px 15px;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }
        th { background-color: #f9f9f9; font-weight: 600; }
        tbody tr:hover { background-color: #f5f5f5; }
        .password-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .toggle-password {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--primary-color);
            font-size: 1.1rem;
        }
        @media (max-width: 1200px) {
            .dashboard-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <header class="dashboard-header">
            <h1><i class="fas fa-user-shield"></i> Super Administrateur</h1>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </header>

        <?php if ($message): ?>
            <div class="message <?= htmlspecialchars($message_type) ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="dashboard-grid">
            <div class="card" id="add-school-form">
                <h2><i class="fas fa-plus-circle"></i> Créer une École</h2>
                <form method="post" action="superadmin_dashboard.php">
                    <div class="form-group">
                        <label for="nom_ecole">Nom de l'école</label>
                        <input type="text" id="nom_ecole" name="nom_ecole" required>
                    </div>
                    <div class="form-group">
                        <label for="adresse">Adresse</label>
                        <input type="text" id="adresse" name="adresse">
                    </div>
                    <div class="form-group">
                        <label for="admin_nom">Nom de l'admin</label>
                        <input type="text" id="admin_nom" name="admin_nom" required>
                    </div>
                    <div class="form-group">
                        <label for="admin_email">Email de l'admin</label>
                        <input type="email" id="admin_email" name="admin_email" required>
                    </div>
                    <div class="form-group">
                        <label for="admin_password">Mot de passe</label>
                        <input type="password" id="admin_password" name="admin_password" required>
                    </div>
                    <button type="submit" class="btn-primary">Enregistrer l'école</button>
                </form>
            </div>

            <div class="card">
                <h2><i class="fas fa-school"></i> Liste des Écoles et Administrateurs</h2>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>École</th>
                                <th>Admin</th>
                                <th>Email</th>
                                <th>Mot de passe</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($ecoles_admins)): ?>
                                <tr><td colspan="4" style="text-align:center;">Aucune école n'a été créée.</td></tr>
                            <?php else: ?>
                                <?php foreach ($ecoles_admins as $data): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($data['nom_ecole']) ?></strong><br>
                                        <small><?= htmlspecialchars($data['adresse'] ?? 'N/A') ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($data['admin_nom']) ?></td>
                                    <td><?= htmlspecialchars($data['admin_email']) ?></td>
                                    <td>
                                        <div class="password-cell">
                                            <span class="password-text" data-password="<?= htmlspecialchars($data['admin_password']) ?>">••••••••</span>
                                            <button type="button" class="toggle-password" title="Afficher/Masquer le mot de passe">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('click', function(e) {
            if (e.target.closest('.toggle-password')) {
                const button = e.target.closest('.toggle-password');
                const passwordSpan = button.previousElementSibling;
                const icon = button.querySelector('i');
                
                if (passwordSpan.textContent === '••••••••') {
                    passwordSpan.textContent = passwordSpan.dataset.password;
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    passwordSpan.textContent = '••••••••';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            }
        });
    </script>
</body>
</html>