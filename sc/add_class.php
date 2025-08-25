<?php
session_start();
require 'config.php';

// Sécurité : Accès réservé aux admins d'école
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'school') {
    header('Location: login.php');
    exit;
}

// --- CORRECTION PRINCIPALE : Vérification stricte de l'ID de l'école ---
// On s'assure que l'ID de l'école est bien un entier positif.
if (!isset($_SESSION['ecole_id']) || !is_numeric($_SESSION['ecole_id']) || $_SESSION['ecole_id'] <= 0) {
    die("Erreur critique : Identifiant de l'école invalide dans la session. Veuillez vous déconnecter et vous reconnecter.");
}

$ecole_id = $_SESSION['ecole_id'];

// 2. On vérifie que cet ID correspond bien à une école existante dans la BDD.
try {
    $stmt_check = $pdo->prepare("SELECT id FROM ecoles WHERE id = ?");
    $stmt_check->execute([$ecole_id]);
    if ($stmt_check->fetch() === false) {
        // Si l'école n'existe pas, on arrête tout.
        die("Erreur de cohérence : L'école associée à votre compte (ID: $ecole_id) n'a pas été trouvée dans la base de données. Veuillez contacter le super-administrateur.");
    }
} catch (PDOException $e) {
    die("Erreur de base de données lors de la vérification de l'école : " . $e->getMessage());
}
// --- FIN DE LA VÉRIFICATION ---


$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_classe = trim($_POST['nom_classe']);

    if (empty($nom_classe)) {
        $message = "Veuillez entrer un nom de classe.";
        $message_type = 'error';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO classes (nom_classe, ecole_id) VALUES (?, ?)");
            $stmt->execute([$nom_classe, $ecole_id]);
            header('Location: school_dashboard.php?success=class_added');
            exit;
        } catch (PDOException $e) {
            $message = "Erreur lors de l'ajout de la classe. Détails : " . $e->getMessage();
            $message_type = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter une Classe</title>
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
            max-width: 500px;
            background: #fff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin: 0 0 30px 0;
            font-size: 2rem;
            color: var(--primary-color);
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #555;
        }
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            box-sizing: border-box;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        input:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(168, 40, 170, 0.1);
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
            display: inline-flex;
            align-items: center;
            gap: 8px;
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
    </style>
</head>
<body>
    <div class="form-container">
        <h2><i class="fas fa-chalkboard-teacher"></i> Ajouter une classe</h2>
        <?php if ($message): ?><p class="message <?= htmlspecialchars($message_type) ?>"><?= htmlspecialchars($message) ?></p><?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label for="nom_classe">Nom de la classe</label>
                <input type="text" id="nom_classe" name="nom_classe" placeholder="Ex: 6ème B" required>
            </div>
            <div class="form-actions">
                <a href="school_dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</body>
</html>