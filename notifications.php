<?php
session_start();
require 'config.php';

// Sécurité : Accès réservé aux admins d'école
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'school') {
    header('Location: login.php');
    exit;
}

$ecole_id = $_SESSION['ecole_id'];
$school_name = 'Votre École'; // Valeur par défaut

// Récupérer les données (élèves, classes et nom de l'école)
$classes = [];
try {
    // Récupérer le nom de l'école pour la signature
    $stmt_school = $pdo->prepare("SELECT nom_ecole FROM ecoles WHERE id = ?");
    $stmt_school->execute([$ecole_id]);
    $school = $stmt_school->fetch(PDO::FETCH_ASSOC);
    if ($school) {
        $school_name = $school['nom_ecole'];
    }

    // Récupérer les classes
    $stmt_classes = $pdo->prepare("SELECT id, nom_classe FROM classes WHERE ecole_id = ? ORDER BY nom_classe");
    $stmt_classes->execute([$ecole_id]);
    while ($row = $stmt_classes->fetch(PDO::FETCH_ASSOC)) {
        $row['students'] = [];
        $classes[$row['id']] = $row;
    }

    // Récupérer les élèves et les assigner à leurs classes
    $stmt_students = $pdo->prepare("SELECT id, nom, prenom, email, telephone, classe_id FROM users WHERE type = 'student' AND ecole_id = ? ORDER BY nom, prenom");
    $stmt_students->execute([$ecole_id]);
    while ($student = $stmt_students->fetch(PDO::FETCH_ASSOC)) {
        if ($student['classe_id'] && isset($classes[$student['classe_id']])) {
            $classes[$student['classe_id']]['students'][] = $student;
        }
    }
} catch (PDOException $e) {
    die("Erreur lors de la récupération des données : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Envoyer des Notifications</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #610e62;
            --secondary-color: #a828aa;
            --danger-color: #c81d25;
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
            padding: 40px 20px;
            color: var(--text-dark);
        }
        .container { 
            max-width: 800px; 
            margin: auto; 
            background: var(--card-bg); 
            padding: 30px 40px; 
            border-radius: 12px; 
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 15px rgba(0,0,0,0.07);
        }
        h2 { 
            text-align: center; 
            color: var(--primary-color); 
            margin-top: 0; 
            margin-bottom: 30px; 
            font-size: 2rem;
        }
        .form-step { margin-bottom: 25px; }
        label { display: block; font-weight: 600; margin-bottom: 8px; }
        select, textarea { width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 1rem; box-sizing: border-box; transition: background-color 0.3s; }
        textarea { min-height: 120px; resize: vertical; }
        textarea[readonly] { background-color: #e9ecef; cursor: not-allowed; }
        .message-templates { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
        .template-btn { background: #e9ecef; border: 1px solid var(--border-color); padding: 8px 15px; border-radius: 20px; cursor: pointer; transition: all 0.2s; }
        .template-btn:hover, .template-btn.active { background: var(--secondary-color); color: white; border-color: var(--secondary-color); }
        .student-list-container { max-height: 200px; overflow-y: auto; border: 1px solid var(--border-color); padding: 10px; border-radius: 8px; margin-top: 10px; }
        .student-item { display: flex; align-items: center; gap: 10px; padding: 5px; }
        .student-item input { width: auto; }
        .send-actions { display: flex; justify-content: center; gap: 15px; margin-top: 20px; }
        .send-btn { padding: 12px 25px; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; text-decoration: none; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 8px; }
        .send-btn:hover { filter: brightness(1.1); transform: translateY(-1px); }
        .send-btn i { font-size: 1.2rem; }
        .send-btn.email { background-color: var(--primary-color); color: white; }
        .send-btn.whatsapp { background-color: #25d366; color: white; }
        .footer-actions { text-align: center; margin-top: 30px; }
        .back-link { 
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--text-light);
            color: white;
            text-decoration: none; 
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .back-link:hover {
            background-color: var(--text-dark);
        }
    </style>
</head>
<body>
    <div class="container" data-school-name="<?= htmlspecialchars($school_name) ?>">
        <h2><i class="fas fa-paper-plane"></i> Envoyer une Notification</h2>

        <div class="form-step">
            <label for="classSelect">1. Sélectionner une classe</label>
            <select id="classSelect">
                <option value="" disabled selected>-- Choisir une classe --</option>
                <?php foreach ($classes as $classe): ?>
                    <option value="<?= $classe['id'] ?>"><?= htmlspecialchars($classe['nom_classe']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-step" id="studentSelection" style="display: none;">
            <label>2. Sélectionner le(s) élève(s)</label>
            <div class="student-list-container" id="studentListContainer"></div>
        </div>

        <div class="form-step">
            <label>3. Choisir un modèle de message</label>
            <div class="message-templates">
                <button class="template-btn" data-msg="Bonjour, nous vous informons que votre enfant est arrivé en retard aujourd'hui.">Retard</button>
                <button class="template-btn" data-msg="Bonjour, nous souhaitons discuter du comportement de votre enfant. Veuillez nous contacter.">Comportement</button>
                <button class="template-btn" data-msg="Bonjour, ceci est un rappel concernant le paiement des frais de scolarité. ">Paiement</button>
                <button class="template-btn" data-msg="Bonjour, nous vous confirmons la bonne présence de votre enfant aujourd'hui.">Présence</button>
            </div>
        </div>

        <div class="form-step">
            <label for="messageText">4. Message à envoyer</label>
            <textarea id="messageText" placeholder="Sélectionnez un modèle pour voir le message..." readonly></textarea>
        </div>

        <div class="send-actions">
            <button class="send-btn email" id="sendEmail"><i class="fas fa-envelope"></i> Email</button>
            <button class="send-btn whatsapp" id="sendWhatsApp"><i class="fab fa-whatsapp"></i> WhatsApp</button>
        </div>

        <div class="footer-actions">
            <a href="school_dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Retour au tableau de bord</a>
        </div>
    </div>

    <script>
        // On passe les données PHP à JavaScript de manière sécurisée
        const classData = <?= json_encode(array_values($classes), JSON_UNESCAPED_UNICODE); ?>;

        document.addEventListener('DOMContentLoaded', function() {
            const container = document.querySelector('.container');
            const classSelect = document.getElementById('classSelect');
            const studentSelectionDiv = document.getElementById('studentSelection');
            const studentListContainer = document.getElementById('studentListContainer');
            const messageText = document.getElementById('messageText');
            const templateButtons = document.querySelectorAll('.template-btn');
            const sendEmailBtn = document.getElementById('sendEmail');
            const sendWhatsAppBtn = document.getElementById('sendWhatsApp');

            const schoolName = container.dataset.schoolName;
            const signature = `\n\nCordialement,\n${schoolName}`;

            classSelect.addEventListener('change', function() {
                studentListContainer.innerHTML = '';
                const selectedClassId = this.value;
                const selectedClass = classData.find(c => c.id == selectedClassId);

                if (selectedClass && selectedClass.students.length > 0) {
                    selectedClass.students.forEach(student => {
                        const studentItem = document.createElement('div');
                        studentItem.className = 'student-item';
                        studentItem.innerHTML = `
                            <input type="checkbox" id="student-${student.id}" 
                                   data-phone="${student.telephone || ''}" 
                                   data-email="${student.email || ''}">
                            <label for="student-${student.id}">${student.nom} ${student.prenom}</label>
                        `;
                        studentListContainer.appendChild(studentItem);
                    });
                    studentSelectionDiv.style.display = 'block';
                } else {
                    studentListContainer.innerHTML = '<p style="text-align:center; padding:10px;">Aucun élève dans cette classe.</p>';
                    studentSelectionDiv.style.display = 'block';
                }
            });

            templateButtons.forEach(button => {
                button.addEventListener('click', function() {
                    templateButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    const baseMsg = this.getAttribute('data-msg');
                    messageText.value = baseMsg + signature;
                    messageText.readOnly = (this.textContent.trim().toLowerCase() !== 'paiement');
                    messageText.style.backgroundColor = messageText.readOnly ? '#e9ecef' : '#fff';
                });
            });

            function getSelectedStudents() {
                const checkedBoxes = document.querySelectorAll('#studentListContainer input[type="checkbox"]:checked');
                if (checkedBoxes.length === 0) {
                    alert("Veuillez sélectionner au moins un élève.");
                    return null;
                }
                let students = [];
                checkedBoxes.forEach(box => {
                    students.push({
                        email: box.dataset.email,
                        phone: box.dataset.phone
                    });
                });
                return students;
            }

            sendEmailBtn.addEventListener('click', function() {
                const students = getSelectedStudents();
                if (!students) return;

                const emails = students.map(s => s.email).filter(Boolean);
                if (emails.length > 0) {
                    const subject = "Notification de l'école";
                    const body = encodeURIComponent(messageText.value);
                    window.location.href = `mailto:${emails.join(',')}?subject=${subject}&body=${body}`;
                } else {
                    alert("Aucun des élèves sélectionnés n'a d'adresse email enregistrée.");
                }
            });

            sendWhatsAppBtn.addEventListener('click', function() {
                const students = getSelectedStudents();
                if (!students) return;

                const validPhones = students.filter(s => s.phone);
                if (validPhones.length === 0) {
                    alert("Aucun des élèves sélectionnés n'a de numéro de téléphone enregistré.");
                    return;
                }

                if (validPhones.length > 1) {
                    if (!confirm(`Vous allez ouvrir ${validPhones.length} onglets WhatsApp. Continuer ?`)) {
                        return;
                    }
                }

                const text = encodeURIComponent(messageText.value);
                validPhones.forEach(student => {
                    const internationalPhone = student.phone.replace(/\D/g, '');
                    window.open(`https://wa.me/${internationalPhone}?text=${text}`, '_blank');
                });
            });
        });
    </script>
</body>
</html>