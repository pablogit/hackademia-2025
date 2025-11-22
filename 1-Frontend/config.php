<?php
/**
 * RefCheck - Configuration
 * Fichier centralisé pour configurer l'interface
 */

// ===== ENVIRONNEMENT =====
define('DEBUG_MODE', true);
define('ENVIRONMENT', 'development'); // production, development, test

// ===== BACKEND API =====
define('BACKEND_URL', getenv('BACKEND_URL') ?? 'http://localhost:5000/api');
define('API_TIMEOUT', 30);

// ===== STOCKAGE =====
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB
define('MAX_TEXT_LENGTH', 1000000); // 1MB

// ===== BASE DE DONNÉES (optionnel) =====
define('DB_HOST', getenv('DB_HOST') ?? 'localhost');
define('DB_USER', getenv('DB_USER') ?? 'refcheck');
define('DB_PASS', getenv('DB_PASS') ?? 'password');
define('DB_NAME', getenv('DB_NAME') ?? 'refcheck_db');

// ===== LIMITE D'ANALYSE =====
define('MAX_REFERENCES', 1000);
define('MIN_WORD_COUNT', 10);

// ===== COULEURS ET THÈME =====
define('COLORS', [
    'primary' => '#667eea',
    'secondary' => '#764ba2',
    'success' => '#28a745',
    'warning' => '#ffc107',
    'danger' => '#dc3545',
    'light' => '#f0f0f0',
    'dark' => '#333',
]);

// ===== SEUILS DE SCORE =====
define('SCORE_THRESHOLDS', [
    'excellent' => 90,    // >= 90%
    'uncertain' => 60,    // 60-89%
    'hallucination' => 0  // < 60%
]);

// ===== MESSAGES =====
define('MESSAGES', [
    'file_invalid_type' => 'Seuls les fichiers PDF sont acceptés',
    'file_too_large' => 'Le fichier dépasse la limite de 50MB',
    'file_upload_error' => 'Erreur lors du téléchargement du fichier',
    'text_too_short' => 'Veuillez entrer au moins 10 mots',
    'text_too_long' => 'Le texte dépasse la limite de 1MB',
    'analysis_error' => 'Erreur lors de l\'analyse',
    'api_error' => 'Erreur de connexion au serveur',
    'success' => 'Analyse réussie',
]);

// ===== FONCTIONS UTILITAIRES =====

/**
 * Crée le dossier uploads s'il n'existe pas
 */
function ensureUploadDir() {
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
}

/**
 * Nettoie les anciens fichiers uploadés
 */
function cleanupOldUploads($max_age = 3600) { // 1 heure
    if (!is_dir(UPLOAD_DIR)) return;
    
    $files = glob(UPLOAD_DIR . '*');
    $now = time();
    
    foreach ($files as $file) {
        if (is_file($file) && ($now - filemtime($file)) > $max_age) {
            unlink($file);
        }
    }
}

/**
 * Formate les erreurs en fonction du mode debug
 */
function formatError($error) {
    if (DEBUG_MODE) {
        return $error;
    } else {
        return 'Une erreur est survenue';
    }
}

/**
 * Log les erreurs
 */
function logError($message, $context = []) {
    $log_file = __DIR__ . '/logs/error.log';
    
    if (!is_dir(dirname($log_file))) {
        mkdir(dirname($log_file), 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] $message";
    
    if (!empty($context)) {
        $log_entry .= ' ' . json_encode($context);
    }
    
    file_put_contents($log_file, $log_entry . PHP_EOL, FILE_APPEND);
}

/**
 * Valide un fichier PDF
 */
function validatePDF($file) {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return [
            'valid' => false,
            'error' => MESSAGES['file_upload_error']
        ];
    }
    
    if ($file['type'] !== 'application/pdf') {
        return [
            'valid' => false,
            'error' => MESSAGES['file_invalid_type']
        ];
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return [
            'valid' => false,
            'error' => MESSAGES['file_too_large']
        ];
    }
    
    return ['valid' => true];
}

/**
 * Valide du texte brut
 */
function validateText($text) {
    if (strlen($text) < MIN_WORD_COUNT) {
        return [
            'valid' => false,
            'error' => MESSAGES['text_too_short']
        ];
    }
    
    if (strlen($text) > MAX_TEXT_LENGTH) {
        return [
            'valid' => false,
            'error' => MESSAGES['text_too_long']
        ];
    }
    
    return ['valid' => true];
}

/**
 * Génère un ID unique pour les analyses
 */
function generateAnalysisID() {
    return uniqid('analysis_', true);
}

/**
 * Obtient le score color
 */
function getScoreColor($score) {
    if ($score >= SCORE_THRESHOLDS['excellent']) {
        return COLORS['success'];
    } elseif ($score >= SCORE_THRESHOLDS['uncertain']) {
        return COLORS['warning'];
    } else {
        return COLORS['danger'];
    }
}

/**
 * Obtient le statut du score
 */
function getScoreStatus($score) {
    if ($score >= SCORE_THRESHOLDS['excellent']) {
        return 'Excellent';
    } elseif ($score >= SCORE_THRESHOLDS['uncertain']) {
        return 'Incertain';
    } else {
        return 'Hallucination';
    }
}

// ===== INITIALISATION =====

// Assurer que le dossier uploads existe
ensureUploadDir();

// Nettoyer les anciens fichiers
cleanupOldUploads();

// Log la session
if (DEBUG_MODE) {
    logError('Session started', [
        'user' => $_SERVER['surname'] ?? 'guest',
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'time' => date('Y-m-d H:i:s')
    ]);
}

?>
