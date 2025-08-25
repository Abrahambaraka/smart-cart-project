<?php
session_start();
require 'config.php';

// Sécurité : Accès réservé aux admins d'école
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'school') {
    header('Location: login.php');
    exit;
}

$ecole_id = $_SESSION['ecole_id'];
$message = '';
$message_type = '';

// Récupérer les classes de l'école pour le menu déroulant
$stmt_classes = $pdo->prepare("SELECT id, nom_classe FROM classes WHERE ecole_id = ? ORDER BY nom_classe");
$stmt_classes->execute([$ecole_id]);
$classes = $stmt_classes->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $matricule = trim($_POST['matricule']);
    $classe_id = !empty($_POST['classe_id']) ? $_POST['classe_id'] : null;

    if (empty($nom) || empty($prenom) || empty($matricule)) {
        $message = "Les champs Nom, Prénom et Matricule sont obligatoires.";
        $message_type = 'error';
    } else {
        try {
            $sql = "INSERT INTO users (nom, prenom, email, telephone, matricule, type, ecole_id, classe_id) VALUES (?, ?, ?, ?, ?, 'student', ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nom, $prenom, $email, $telephone, $matricule, $ecole_id, $classe_id]);
            header('Location: school_dashboard.php?success=student_added');
            exit;
        } catch (PDOException $e) {
            // Gestion d'erreur améliorée
            if ($e->errorInfo[1] == 1062) { // Code d'erreur pour entrée dupliquée
                $message = "Cet email est déjà utilisé. Veuillez en choisir un autre.";
            } else {
                $message = "Une erreur de base de données est survenue. " . $e->getMessage();
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
    <title>Ajouter un Élève</title>
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
        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            box-sizing: border-box;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        input:focus, select:focus {
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
        <h2><i class="fas fa-user-plus"></i> Ajouter un nouvel élève</h2>
        <?php if ($message): ?><p class="message <?= htmlspecialchars($message_type) ?>"><?= htmlspecialchars($message) ?></p><?php endif; ?>
        <form method="post">
            <div class="form-group"><label for="nom">Nom</label><input type="text" id="nom" name="nom" required></div>
            <div class="form-group"><label for="prenom">Prénom</label><input type="text" id="prenom" name="prenom" required></div>
            <div class="form-group"><label for="email">Email</label><input type="email" id="email" name="email"></div>
            <div class="form-group"><label for="telephone">Téléphone</label><input type="tel" id="telephone" name="telephone"></div>
            <div class="form-group"><label for="matricule">Matricule (Mot de passe)</label><input type="text" id="matricule" name="matricule" required></div>
            <div class="form-group"><label for="classe_id">Classe</label><select id="classe_id" name="classe_id"><option value="">-- Sélectionner une classe --</option><?php foreach ($classes as $classe): ?><option value="<?= $classe['id'] ?>"><?= htmlspecialchars($classe['nom_classe']) ?></option><?php endforeach; ?></select></div>
            <div class="form-actions">
                <a href="school_dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</body>
</html>