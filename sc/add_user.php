<?php
session_start();
require 'config.php';
$message = '';

// Récupérer la liste des classes pour la liste déroulante
$classes = [];
try {
    $stmt_classes = $pdo->query("SELECT id, nom_classe FROM classes ORDER BY nom_classe");
    $classes = $stmt_classes->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message .= "Erreur lors de la récupération des classes : " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    $matricule = $_POST['matricule'];
    $classe_id = empty($_POST['classe_id']) ? null : $_POST['classe_id'];
    $photo = null;

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $photo = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['photo']['tmp_name'], "uploads/$photo");
    }

    $stmt = $pdo->prepare("INSERT INTO users (nom, prenom, email, telephone, matricule, classe_id, photo) VALUES (?, ?, ?, ?, ?, ?, ?)");
    try {
        $stmt->execute([$nom, $prenom, $email, $telephone, $matricule, $classe_id, $photo]);
        header('Location: dashboard.php');
        exit;
    } catch (PDOException $e) {
        $message = "Erreur : " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Formulaire</title>
    <style>
        /* 🌊 FOND ANIMÉ EAU */
        * {
            margin:0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
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
        /* 🧾 Formulaire */
        .form-container {
            max-width: 400px;
            margin: 20px auto;
            background:rgb(52, 6, 53);
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 0 20px  #610e62;
            backdrop-filter: blur(6px);
        }
        h2 {
            text-align: center;
            margin-bottom: 25px;
            font-size: 24px;
            color:rgb(243, 158, 1);
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 300;
            color: #e0dbe0;
        }
        input, select {
            width: 100%;
            padding: 12px 15px;
            border: none;
            border-radius: 8px;
            background: rgba(224, 219, 224, 0.1);
            color: #e0dbe0;
            font-size: 16px;
            border: 1px solid rgba(224, 219, 224, 0.2);
        }
        input:focus, select:focus {
            outline: none;
            background: rgba(224, 219, 224, 0.2);
            box-shadow: 0 0 10px rgba(224, 219, 224, 0.3);
        }
        button {
            background: linear-gradient(45deg,rgb(240, 118, 5),rgb(235, 174, 45));
            color: #4f0949;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            display: block;
            width: 100%;
            margin-top: 30px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            background: linear-gradient(45deg, #a89ea8, #e0dbe0);
        }
        .error {
            color: #ffbaba;
            background: #5c0000;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }
        a {
            display: block;
            margin-top: 15px;
            text-align: center;
            color:rgb(248, 161, 0);
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
        #particles-js {
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
    <div class="form-container">
        <h2>FORMULAIRE D'ENREGISTREMENT</h2>
        <?php if ($message): ?><p class="error"><?= htmlspecialchars($message) ?></p><?php endif; ?>
        <form action="add_user.php" method="post" enctype="multipart/form-data">
            <label for="nom">Nom:<input type="text" id="nom" name="nom" required></label>
            <label for="prenom">Prénom:<input type="text" id="prenom" name="prenom" required></label>
            <label for="email">Email:<input type="email" id="email" name="email" required></label>
            <label for="telephone">Téléphone:<input type="text" id="telephone" name="telephone" required></label>
            <label for="matricule">Matricule:<input type="text" id="matricule" name="matricule" required></label>
            
            <label for="classe">Classe:
                <select id="classe" name="classe_id">
                    <option value="">Non assigné</option>
                    <?php foreach ($classes as $classe): ?>
                        <option value="<?= htmlspecialchars($classe['id']) ?>">
                            <?= htmlspecialchars($classe['nom_classe']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            
            <label for="photo">Photo:<input type="file" id="photo" name="photo" accept="image/*"></label>
            <button type="submit">Enregistrer</button>
        </form>
        <a href="dashboard.php">Retour au tableau de bord</a>
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