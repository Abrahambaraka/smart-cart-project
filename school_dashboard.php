<?php
session_start();
require 'config.php';
require_once 'auth_check.php';
check_auth(['school']); // Seuls les admins d'école ('school') sont autorisés

// On peut maintenant utiliser $_SESSION en toute sécurité
$ecole_id = $_SESSION['ecole_id'];

// Récupérer les infos de l'école
$stmt_school = $pdo->prepare("SELECT nom_ecole FROM ecoles WHERE id = ?");
$stmt_school->execute([$ecole_id]);
$school = $stmt_school->fetch(PDO::FETCH_ASSOC);

// Récupérer les élèves de cette école
$stmt_students = $pdo->prepare("SELECT * FROM users WHERE ecole_id = ? AND type = 'student' ORDER BY nom");
$stmt_students->execute([$ecole_id]);
$students = $stmt_students->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les classes de cette école
$stmt_classes = $pdo->prepare("SELECT * FROM classes WHERE ecole_id = ? ORDER BY nom_classe");
$stmt_classes->execute([$ecole_id]);
$classes = $stmt_classes->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard École - <?= htmlspecialchars($school['nom_ecole'] ?? 'École') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #610e62;
            --secondary-color: #a828aa;
            --danger-color: #c81d25;
            --danger-hover: #a3151b;
            --light-bg: #f8f9fa;
            --card-bg: #ffffff;
            --border-color: #dee2e6;
            --text-dark: #212529;
            --text-light: #6c757d;
        }
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: var(--light-bg);
            margin: 0;
            color: var(--text-dark);
        }
        .main-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 25px;
            background: var(--card-bg);
            border-radius: 12px;
            border: 1px solid var(--border-color);
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
            background: var(--danger-color);
            color: white;
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .logout-btn:hover {
            background-color: var(--danger-hover);
            transform: translateY(-2px);
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }
        .action-btn {
            background: var(--primary-color);
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .action-btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 30px;
        }
        .card {
            background: var(--card-bg);
            padding: 25px 30px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }
        .card h2 {
            font-size: 1.5rem;
            color: var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
            margin-top: 0;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
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
        th {
            font-weight: 600;
            color: var(--text-light);
        }
        tbody tr:last-child td {
            border-bottom: none;
        }
        tbody tr:hover {
            background-color: #f1f5f9;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            color: var(--text-light);
        }
    </style>
</head>
<body>
    <div class="main-container">
        <header class="dashboard-header">
            <h1><i class="fas fa-school"></i> <?= htmlspecialchars($school['nom_ecole'] ?? 'Tableau de bord') ?></h1>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </header>

        <div class="actions">
            <a href="add_student.php" class="action-btn"><i class="fas fa-user-plus"></i> Ajouter un Élève</a>
            <a href="add_class.php" class="action-btn"><i class="fas fa-chalkboard-teacher"></i> Ajouter une Classe</a>
            <a href="notifications.php" class="action-btn"><i class="fas fa-bell"></i> Envoyer Notifications</a>
        </div>

        <div class="dashboard-grid">
            <div class="card">
                <h2><i class="fas fa-users"></i> Liste des Élèves</h2>
                <table>
                    <thead><tr><th>Nom</th><th>Prénom</th><th>Email</th><th>Téléphone</th></tr></thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr><td colspan="4" class="no-data">Aucun élève n'a été ajouté.</td></tr>
                        <?php else: ?>
                            <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?= htmlspecialchars($student['nom']) ?></td>
                                <td><?= htmlspecialchars($student['prenom']) ?></td>
                                <td><?= htmlspecialchars($student['email']) ?></td>
                                <td><?= htmlspecialchars($student['telephone'] ?? 'N/A') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="card">
                <h2><i class="fas fa-list-alt"></i> Liste des Classes</h2>
                <table>
                    <thead><tr><th>Nom de la classe</th></tr></thead>
                    <tbody>
                        <?php if (empty($classes)): ?>
                            <tr><td class="no-data">Aucune classe n'a été ajoutée.</td></tr>
                        <?php else: ?>
                            <?php foreach ($classes as $classe): ?>
                            <tr><td><?= htmlspecialchars($classe['nom_classe']) ?></td></tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>