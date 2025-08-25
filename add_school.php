<?php
session_start();
require 'config.php';

// Sécurité : Accès réservé au Super Admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'superadmin') {
    header('Location: login.php');
    exit;
}

$message = '';
$message_type = ''; // 'error' ou 'success'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Infos pour la table 'ecoles'
    $nom_ecole = trim($_POST['nom_ecole']);
    $adresse = trim($_POST['adresse']);

    // Infos pour le compte admin de l'école (table 'users')
    $admin_nom = trim($_POST['admin_nom']);
    $admin_email = trim($_POST['admin_email']);
    $admin_password = $_POST['admin_password']; // Le matricule/mot de passe

    if (empty($nom_ecole) || empty($admin_nom) || empty($admin_email) || empty($admin_password)) {
        $message = "Veuillez remplir tous les champs obligatoires.";
        $message_type = 'error';
    } else {
        try {
            $pdo->beginTransaction();

            // 1. Créer l'école
            $stmt_ecole = $pdo->prepare("INSERT INTO ecoles (nom_ecole, adresse) VALUES (?, ?)");
            $stmt_ecole->execute([$nom_ecole, $adresse]);
            $ecole_id = $pdo->lastInsertId();

            // 2. Créer l'utilisateur admin pour cette école
            $stmt_user = $pdo->prepare("INSERT INTO users (nom, prenom, email, matricule, type, ecole_id) VALUES (?, 'Admin', ?, ?, 'school', ?)");
            $stmt_user->execute([$admin_nom, $admin_email, $admin_password, $ecole_id]);

            $pdo->commit();
            // Rediriger avec un message de succès
            header('Location: superadmin_dashboard.php?success=1');
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            // Vérifier si c'est une erreur de duplicata
            if ($e->errorInfo[1] == 1062) {
                $message = "Erreur : L'email ou le nom de l'école existe déjà.";
            } else {
                $message = "Erreur lors de la création de l'école : " . $e->getMessage();
            }
            $message_type = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer une École - SMART CONGO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #610e62;
            --secondary-color: #a828aa;
            --light-bg: #f4f7f6;
            --border-color: #ddd;
        }
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: var(--light-bg);
            margin: 0;
            padding: 40px 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            box-sizing: border-box;
        }
        .form-container {
            width: 100%;
            max-width: 600px;
            background: #fff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .form-header {
            text-align: center;
            margin-bottom: 30px;
            color: var(--primary-color);
        }
        .form-header h2 {
            margin: 0;
            font-size: 2rem;
        }
        .form-section {
            margin-bottom: 25px;
        }
        .form-section h3 {
            font-size: 1.2rem;
            color: var(--primary-color);
            border-bottom: 2px solid var(--secondary-color);
            padding-bottom: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #555;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            box-sizing: border-box;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(168, 40, 170, 0.1);
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            text-align: center;
            font-weight: 500;
        }
        .message.error {
            background-color: #ffeaea;
            color: #c0392b;
            border: 1px solid #c0392b;
        }
        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
        }
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        .btn-secondary {
            background-color: #f0f0f0;
            color: #555;
            border: 1px solid #ddd;
        }
        .btn-secondary:hover {
            background-color: #e5e5e5;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <h2><i class="fas fa-school-flag"></i> Créer un Compte École</h2>
        </div>

        <?php if ($message): ?>
            <div class="message <?= htmlspecialchars($message_type) ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="post" action="add_school.php">
            <div class="form-section">
                <h3><i class="fas fa-info-circle"></i> Informations sur l'école</h3>
                <div class="form-group">
                    <label for="nom_ecole">Nom de l'école</label>
                    <input type="text" id="nom_ecole" name="nom_ecole" placeholder="Ex: Lycée Saint-Pierre" required>
                </div>
                <div class="form-group">
                    <label for="adresse">Adresse de l'école</label>
                    <input type="text" id="adresse" name="adresse" placeholder="Ex: 123 Avenue de la République">
                </div>
            </div>

            <div class="form-section">
                <h3><i class="fas fa-user-shield"></i> Compte Administrateur de l'école</h3>
                <div class="form-group">
                    <label for="admin_nom">Nom de l'administrateur</label>
                    <input type="text" id="admin_nom" name="admin_nom" placeholder="Ex: J. Dupont" required>
                </div>
                <div class="form-group">
                    <label for="admin_email">Email de connexion</label>
                    <input type="email" id="admin_email" name="admin_email" placeholder="Ex: admin.dupont@email.com" required>
                </div>
                <div class="form-group">
                    <label for="admin_password">Mot de passe</label>
                    <input type="password" id="admin_password" name="admin_password" placeholder="Choisissez un mot de passe sécurisé" required>
                </div>
            </div>
            
            <div class="form-actions">
                <a href="superadmin_dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
                <button type="submit" class="btn btn-primary">Créer l'école <i class="fas fa-check"></i></button>
            </div>
        </form>
    </div>
</body>
</html>