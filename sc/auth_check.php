<?php
// --- CORRECTION : Démarrer la session seulement si elle n'est pas déjà active ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

/**
 * Vérifie l'authentification et l'autorisation de l'utilisateur.
 *
 * @param array $allowed_roles Les rôles autorisés à accéder à la page (ex: ['superadmin', 'school']).
 */
function check_auth($allowed_roles = []) {
    // 1. Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
        header('Location: login.php');
        exit;
    }

    $user_type = $_SESSION['user_type'];

    // 2. Vérifier si le rôle de l'utilisateur est dans la liste des rôles autorisés
    if (!in_array($user_type, $allowed_roles)) {
        // Si l'utilisateur n'a pas le bon rôle, on le redirige vers son propre tableau de bord
        switch ($user_type) {
            case 'superadmin':
                header('Location: superadmin_dashboard.php');
                break;
            case 'school':
                header('Location: school_dashboard.php');
                break;
            case 'admin':
                header('Location: dashboard.php');
                break;
            default:
                header('Location: login.php'); // Sécurité par défaut
                break;
        }
        exit;
    }

    // 3. Pour les admins d'école, vérifier que leur ecole_id est valide
    if ($user_type === 'school') {
        if (empty($_SESSION['ecole_id'])) {
            // Si l'ecole_id est manquant, forcer la déconnexion pour réinitialiser la session
            header('Location: logout.php?error=session_expired');
            exit;
        }
    }
}
?>