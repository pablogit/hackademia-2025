<?php
/**
 * RefCheck - API Integration Example
 * Exemple d'intégration avec le backend Python
 * 
 * Ce fichier démontre comment intégrer l'interface avec
 * les modules Python de traitement (Extraction, Parsing, Retrieve, Compare)
 */

// ===== CONFIGURATION =====
define('BACKEND_URL', 'http://localhost:5000/api');
define('UPLOAD_DIR', './uploads/');
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB

// Créer le dossier uploads s'il n'existe pas
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// ===== CLASSE D'API INTÉGRATION =====
class RefCheckAPI {
    
    private $backend_url;
    private $upload_dir;
    
    public function __construct($backend_url, $upload_dir) {
        $this->backend_url = $backend_url;
        $this->upload_dir = $upload_dir;
    }
    
    /**
     * Traite un fichier PDF
     * 
     * @param string $file_path Chemin du fichier
     * @return array Résultats du traitement
     */
    public function processPDF($file_path) {
        // Étape 1 : Extraction des références
        $extraction_result = $this->extractReferences($file_path);
        if (!$extraction_result['success']) {
            return [
                'success' => false,
                'error' => 'Erreur lors de l\'extraction'
            ];
        }
        
        // Étape 2 : Parsing des références
        $parsing_result = $this->parseReferences($extraction_result['data']);
        if (!$parsing_result['success']) {
            return [
                'success' => false,
                'error' => 'Erreur lors du parsing'
            ];
        }
        
        // Étape 3 : Récupération des métadonnées
        $retrieve_result = $this->retrieveMetadata($parsing_result['data']);
        
        // Étape 4 : Comparaison et scoring
        $comparison_result = $this->compareReferences($retrieve_result['data']);
        
        return [
            'success' => true,
            'data' => $comparison_result['data'],
            'stats' => $this->calculateStats($comparison_result['data'])
        ];
    }
    
    /**
     * Traite du texte brut
     * 
     * @param string $text Texte à analyser
     * @return array Résultats du traitement
     */
    public function processText($text) {
        // Étape 1 : Extraction des références du texte
        $extraction_result = $this->extractReferencesFromText($text);
        if (!$extraction_result['success']) {
            return [
                'success' => false,
                'error' => 'Erreur lors de l\'extraction'
            ];
        }
        
        // Étapes 2-4 : Identiques au PDF
        $parsing_result = $this->parseReferences($extraction_result['data']);
        $retrieve_result = $this->retrieveMetadata($parsing_result['data']);
        $comparison_result = $this->compareReferences($retrieve_result['data']);
        
        return [
            'success' => true,
            'data' => $comparison_result['data'],
            'stats' => $this->calculateStats($comparison_result['data'])
        ];
    }
    
    /**
     * Extrait les références d'un PDF
     * Appelle le module 2-Extraction
     */
    private function extractReferences($file_path) {
        try {
            $response = $this->callAPI(
                '/extract',
                'POST',
                ['file' => new CURLFile($file_path)]
            );
            return [
                'success' => true,
                'data' => $response['references'] ?? []
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Extrait les références du texte
     * Appelle le module 2-Extraction avec texte
     */
    private function extractReferencesFromText($text) {
        try {
            $response = $this->callAPI(
                '/extract/text',
                'POST',
                ['text' => $text]
            );
            return [
                'success' => true,
                'data' => $response['references'] ?? []
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Parse les références extraites
     * Appelle le module 3-Parsing
     */
    private function parseReferences($references) {
        try {
            $response = $this->callAPI(
                '/parse',
                'POST',
                ['references' => $references]
            );
            return [
                'success' => true,
                'data' => $response['parsed'] ?? []
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Récupère les métadonnées des références
     * Appelle le module 4-Retrieve
     */
    private function retrieveMetadata($references) {
        try {
            $response = $this->callAPI(
                '/retrieve',
                'POST',
                ['references' => $references]
            );
            return [
                'success' => true,
                'data' => $response['enriched'] ?? []
            ];
        } catch (Exception $e) {
            // En cas d'erreur, retourner les données originales
            return [
                'success' => true,
                'data' => $references
            ];
        }
    }
    
    /**
     * Compare et score les références
     * Appelle le module 5-Compare
     */
    private function compareReferences($references) {
        try {
            $response = $this->callAPI(
                '/compare',
                'POST',
                ['references' => $references]
            );
            return [
                'success' => true,
                'data' => $response['scored'] ?? []
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Appel HTTP générique à l'API backend
     */
    private function callAPI($endpoint, $method = 'GET', $data = null) {
        $url = $this->backend_url . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            
            if (is_array($data)) {
                // Vérifier s'il y a des fichiers
                $has_file = false;
                foreach ($data as $value) {
                    if ($value instanceof CURLFile) {
                        $has_file = true;
                        break;
                    }
                }
                
                if ($has_file) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                } else {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/json'
                    ]);
                }
            }
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code !== 200) {
            throw new Exception("API Error: HTTP $http_code");
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Calcule les statistiques des résultats
     */
    private function calculateStats($results) {
        $total = count($results);
        $excellent = 0;
        $uncertain = 0;
        $hallucination = 0;
        
        foreach ($results as $ref) {
            $score = $ref['score'] ?? 0;
            
            if ($score >= 90) {
                $excellent++;
            } elseif ($score >= 60) {
                $uncertain++;
            } else {
                $hallucination++;
            }
        }
        
        return [
            'total' => $total,
            'excellent' => $excellent,
            'uncertain' => $uncertain,
            'hallucination' => $hallucination,
            'percentage_excellent' => $total > 0 ? round(($excellent / $total) * 100) : 0,
            'percentage_uncertain' => $total > 0 ? round(($uncertain / $total) * 100) : 0,
            'percentage_hallucination' => $total > 0 ? round(($hallucination / $total) * 100) : 0
        ];
    }
}

// ===== ENDPOINT HANDLER =====
// Ce code gère les requêtes AJAX depuis l'interface

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $api = new RefCheckAPI(BACKEND_URL, UPLOAD_DIR);
    $action = $_POST['action'];
    
    try {
        if ($action === 'upload' && isset($_FILES['file'])) {
            // Valider le fichier
            $file = $_FILES['file'];
            
            if ($file['size'] > MAX_FILE_SIZE) {
                throw new Exception('Fichier trop volumineux');
            }
            
            if ($file['type'] !== 'application/pdf') {
                throw new Exception('Seuls les fichiers PDF sont acceptés');
            }
            
            // Déplacer le fichier
            $filename = uniqid() . '_' . basename($file['name']);
            $filepath = UPLOAD_DIR . $filename;
            
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new Exception('Erreur lors du téléchargement');
            }
            
            // Traiter le fichier
            $result = $api->processPDF($filepath);
            
            // Nettoyer le fichier temporaire
            unlink($filepath);
            
            echo json_encode($result);
            
        } elseif ($action === 'analyze_text' && isset($_POST['text'])) {
            $text = $_POST['text'];
            
            if (strlen($text) < 10) {
                throw new Exception('Le texte doit contenir au moins 10 caractères');
            }
            
            $result = $api->processText($text);
            echo json_encode($result);
            
        } else {
            throw new Exception('Action non valide');
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    
    exit;
}

// ===== EXEMPLE D'UTILISATION =====
/*

// Depuis JavaScript (refcheck.php):

document.getElementById('analyzeBtn').addEventListener('click', async () => {
    const formData = new FormData();
    
    const activeTab = document.querySelector('.tab-content.active');
    
    if (activeTab.id === 'file-tab') {
        // Upload mode
        const file = document.getElementById('fileInput').files[0];
        formData.append('action', 'upload');
        formData.append('file', file);
        
    } else {
        // Text mode
        const text = document.getElementById('textInput').value;
        formData.append('action', 'analyze_text');
        formData.append('text', text);
    }
    
    try {
        const response = await fetch('api_integration.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            displayResults(data.data, data.stats);
        } else {
            showError(data.error);
        }
        
    } catch (error) {
        showError('Erreur lors de l\'analyse');
    }
});

*/

?>
