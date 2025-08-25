<?php
session_start();
require 'config.php';
require_once 'auth_check.php';
check_auth(['admin']); // Seul l'admin général est autorisé

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: site_public.php');
    exit;
}

try {
    // Récupérer les utilisateurs avec le nom de leur classe
    $stmt = $pdo->query("SELECT u.*, c.nom_classe FROM users u LEFT JOIN classes c ON u.classe_id = c.id ORDER BY u.nom, u.prenom");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des utilisateurs : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord - Admin</title>
    <style>
        /* 🌊 Fond animé type eau */
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            color: #610e62;
            position: relative;
            height: 100vh;
            background: #000;
            overflow: hidden;
        }

        body::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle,rgb(52, 6, 53) 0%,rgb(52, 6, 53));
            animation: waterFlow 25s linear infinite;
            z-index: -1;
            opacity: 0.6;
        }

        @keyframes waterFlow {
            0% { transform: rotate(0deg) scale(1.2); }
            50% { transform: rotate(180deg) scale(1.25); }
            100% { transform: rotate(360deg) scale(1.2); }
        }

        .dashboard-container {
            max-width: 1000px;
            margin: 50px auto;
            background:rgb(52, 6, 53);
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 0 20px  #610e62;
            backdrop-filter: blur(6px);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            font-size: 26px;
            color:rgb(255, 255, 255);
        }

        .top-links {
            text-align: center;
            margin-bottom: 25px;
        }

        .top-links a, .btn-site {
            display: inline-block;
            margin: 8px 10px;
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .top-links a {
            background-color:rgb(241, 111, 5);
            color: white;
        }

        .top-links a:hover {
            background-color: #610e62;
        }

        .btn-site {
            background-color: #610e62;
            color: white;
        }

        .btn-site:hover {
            background-color: #1e7e34;
        }

        table {
            width: 100%;
            background-color:rgb(255, 255, 255);
        }

        th, td {
            padding: 10px;
            color:rgb(52, 6, 53);
        }

        th {
            background-color: rgba(255, 238, 238, 0.3);
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        tr:hover {
            background-color: rgb(214, 208, 214);
            transition: background 0.3s;
        }

        img {
            border-radius: 6px;
            width: 50px;
            height: 50px;
            object-fit: cover;
        }

        .actions a {
            color: #610e62;
            text-decoration: none;
            margin-right: 10px;
        }

        .actions a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 20px;
                margin: 20px;
            }

            table, thead, tbody, th, td, tr {
                font-size: 13px;
            }
        }

        #particles-js 
        {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 0;
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Effet de lueur */
        .glow {
            animation: glow 3s ease-in-out infinite alternate;
        }

        @keyframes glow {
            from { text-shadow: 0 0 5px #e0dbe0, 0 0 10px #e0dbe0; }
            to { text-shadow: 0 0 10px #e0dbe0, 0 0 20px rgba(224, 219, 224, 0.5), 0 0 30px rgba(224, 219, 224, 0.3); }
        }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <div class="dashboard-container">
        <h2>Gestion des utilisateurs</h2>
        <div class="top-links">
            <a href="add_user.php">Ajouter un utilisateur</a>
            <a href="add_class.php">Ajouter une classe</a>
            <a href="site_public.php" class="btn-site">Accéder au site public</a>
            <a href="logout.php">Déconnexion</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Nom et Prénom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Matricule</th>
                    <th>Classe</th>
                    <th>Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($users) === 0): ?>
                    <tr><td colspan="8" style="text-align:center;">Aucun utilisateur trouvé.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <?php if ($user['photo']): ?>
                                <img src="uploads/<?= htmlspecialchars($user['photo']) ?>" alt="Photo de <?= htmlspecialchars($user['nom']) ?>">
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($user['nom']) . ' ' . htmlspecialchars($user['prenom']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['telephone']) ?></td>
                        <td><?= htmlspecialchars($user['matricule']) ?></td>
                        <td><?= htmlspecialchars($user['nom_classe'] ?? 'Non assigné') ?></td>
                        <td><?= htmlspecialchars($user['type']) ?></td>
                        <td class="actions">
                            <a href="edit_user.php?id=<?= $user['id'] ?>">Modifier</a>
                            <a href="delete_user.php?id=<?= $user['id'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">Supprimer</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script>
        particlesJS("particles-js", {
            "particles": {
                "number": {
                    "value": 80,
                    "density": {
                        "enable": true,
                        "value_area": 800
                    }
                },
                "color": {
                    "value": "#e0dbe0"
                },
                "shape": {
                    "type": "circle",
                    "stroke": {
                        "width": 0,
                        "color": "#e0dbe0"
                    },
                    "polygon": {
                        "nb_sides": 5
                    },
                },
                "opacity": {
                    "value": 0.5,
                    "random": false,
                    "anim": {
                        "enable": false,
                        "speed": 1,
                        "opacity_min": 0.1,
                        "sync": false
                    }
                },
                "size": {
                    "value": 3,
                    "random": true,
                    "anim": {
                        "enable": false,
                        "speed": 40,
                        "size_min": 0.1,
                        "sync": false
                    }
                },
                "line_linked": {
                    "enable": true,
                    "distance": 150,
                    "color": "#e0dbe0",
                    "opacity": 0.3,
                    "width": 1
                },
                "move": {
                    "enable": true,
                    "speed": 1,
                    "direction": "none",
                    "random": true,
                    "straight": false,
                    "out_mode": "out",
                    "bounce": false,
                    "attract": {
                        "enable": true,
                        "rotateX": 600,
                        "rotateY": 1200
                    }
                }
            },
            "interactivity": {
                "detect_on": "canvas",
                "events": {
                    "onhover": {
                        "enable": true,
                        "mode": "grab"
                    },
                    "onclick": {
                        "enable": true,
                        "mode": "push"
                    },
                    "resize": true
                },
                "modes": {
                    "grab": {
                        "distance": 140,
                        "line_linked": {
                            "opacity": 0.8
                        }
                    },
                    "push": {
                        "particles_nb": 4
                    }
                }
            },
            "retina_detect": true
        });
    </script>
</body>
</html>