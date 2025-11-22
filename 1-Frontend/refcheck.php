<?php

/**
 * RefCheck - Vérificateur d'Hallucinations de Références IA
 * Interface principale du système
 */

// Variables d'initialisation
$page = "refcheck";
$user_name = isset($_SERVER['surname']) ? strtolower($_SERVER['surname']) : 'guest';
$user_mail = isset($_SERVER['mail']) ? strtolower($_SERVER['mail']) : '';

// Inclusion du header
include "header.php";
?>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #E83131 0%, #D71E5E 50%, #C41689 100%);
        min-height: 100vh;
        overflow-x: hidden;
    }

    .container-main {
        display: flex;
        height: calc(100vh - 100px);
        gap: 20px;
        padding: 20px;
        max-width: 100%;
    }

    /* ZONE GAUCHE - INPUT */
    .zone-input {
        flex: 1;
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        gap: 20px;
        overflow-y: auto;
    }

    .zone-input h2 {
        color: #333;
        font-size: 24px;
        margin-bottom: 10px;
    }

    .zone-input p {
        color: #666;
        font-size: 14px;
        margin-bottom: 20px;
    }

    /* DRAG AND DROP */
    .dropzone {
        border: 3px dashed #E83131;
        border-radius: 8px;
        padding: 40px 20px;
        text-align: center;
        cursor: pointer;
        background: linear-gradient(135deg, rgba(232, 49, 49, 0.05) 0%, rgba(215, 30, 94, 0.05) 100%);
        transition: all 0.3s ease;
        margin-bottom: 20px;
    }

    .dropzone:hover {
        border-color: #D71E5E;
        background: linear-gradient(135deg, rgba(232, 49, 49, 0.1) 0%, rgba(215, 30, 94, 0.1) 100%);
        transform: translateY(-2px);
    }

    .dropzone.active {
        border-color: #D71E5E;
        background: linear-gradient(135deg, rgba(232, 49, 49, 0.15) 0%, rgba(215, 30, 94, 0.15) 100%);
    }

    .dropzone-icon {
        font-size: 48px;
        margin-bottom: 10px;
    }

    .dropzone-text {
        color: #333;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .dropzone-subtext {
        color: #999;
        font-size: 12px;
    }

    .file-input {
        display: none;
    }

    /* VALIDATION INDICATORS */
    .validation-item {
        display: none;
        padding: 15px;
        border-radius: 6px;
        margin: 10px 0;
        font-size: 14px;
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .validation-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .validation-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .validation-item::before {
        font-weight: bold;
        margin-right: 8px;
    }

    .validation-success::before {
        content: "✓";
        color: #28a745;
    }

    .validation-error::before {
        content: "✕";
        color: #dc3545;
    }

    /* TABS */
    .tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        border-bottom: 2px solid #eee;
    }

    .tab-button {
        padding: 12px 20px;
        background: none;
        border: none;
        color: #999;
        cursor: pointer;
        font-weight: 600;
        border-bottom: 3px solid transparent;
        transition: all 0.3s ease;
        font-size: 14px;
    }

    .tab-button.active {
        color: #E83131;
        border-bottom-color: #E83131;
    }

    .tab-button:hover {
        color: #333;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    /* TEXTAREA */
    .textarea-input {
        width: 100%;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-family: 'Courier New', monospace;
        font-size: 13px;
        resize: vertical;
        min-height: 150px;
        margin-bottom: 15px;
    }

    .textarea-input:focus {
        outline: none;
        border-color: #E83131;
        box-shadow: 0 0 0 3px rgba(232, 49, 49, 0.1);
    }

    /* BUTTON */
    .btn-analyze {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, #E83131 0%, #D71E5E 100%);
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: none;
    }

    .btn-analyze.visible {
        display: block;
    }

    .btn-analyze:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(232, 49, 49, 0.3);
    }

    .btn-analyze:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .btn-analyze:active:not(:disabled) {
        transform: translateY(0);
    }

    /* ZONE DROITE - OUTPUT */
    .zone-output {
        flex: 1;
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        overflow-y: auto;
    }

    .zone-output h2 {
        color: #333;
        font-size: 24px;
        margin-bottom: 20px;
    }

    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: #999;
        text-align: center;
    }

    .empty-state-icon {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.3;
    }

    .empty-state-text {
        font-size: 16px;
        color: #999;
    }

    /* LOADING */
    .loading {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .spinner {
        border: 3px solid rgba(102, 126, 234, 0.2);
        border-top: 3px solid #667eea;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    /* RESULTS LIST */
    .results-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .result-item {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .result-item:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .result-header {
        display: flex;
        align-items: center;
        padding: 15px;
        cursor: pointer;
        background: #f9f9f9;
        transition: all 0.3s ease;
    }

    .result-header:hover {
        background: #f0f0f0;
    }

    .result-score {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: white;
        font-size: 18px;
        flex-shrink: 0;
        margin-right: 15px;
    }

    .score-excellent {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .score-uncertain {
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
    }

    .score-hallucination {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    }

    .result-content {
        flex: 1;
    }

    .result-title {
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
        font-size: 14px;
    }

    .result-meta {
        font-size: 12px;
        color: #999;
    }

    .result-toggle {
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        cursor: pointer;
        flex-shrink: 0;
        transition: transform 0.3s ease;
    }

    .result-toggle.open {
        transform: rotate(90deg);
    }

    .result-details {
        display: none;
        padding: 20px 15px;
        background: #f0f0f0;
        border-top: 1px solid #e0e0e0;
    }

    .result-details.open {
        display: block;
    }

    .detail-section {
        margin-bottom: 15px;
    }

    .detail-section:last-child {
        margin-bottom: 0;
    }

    .detail-label {
        font-weight: 600;
        color: #333;
        font-size: 13px;
        margin-bottom: 5px;
    }

    .detail-value {
        color: #666;
        font-size: 13px;
        line-height: 1.5;
    }

    .ai-explanation {
        background: white;
        padding: 12px;
        border-left: 4px solid #E83131;
        border-radius: 4px;
        font-style: italic;
        color: #555;
    }

    /* RESPONSIVE */
    @media (max-width: 1200px) {
        .container-main {
            flex-direction: column;
            height: auto;
            min-height: calc(100vh - 100px);
        }

        .zone-input,
        .zone-output {
            min-height: 400px;
        }
    }

    .stats-bar {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid #e0e0e0;
    }

    .stat-item {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .stat-number {
        font-size: 24px;
        font-weight: bold;
        color: #333;
    }

    .stat-label {
        font-size: 12px;
        color: #999;
        margin-top: 5px;
    }

    .file-indicator {
        background: #e7f5e7;
        border-left: 4px solid #28a745;
        padding: 12px 15px;
        border-radius: 4px;
        margin-bottom: 15px;
        font-size: 13px;
        color: #155724;
        display: none;
        align-items: center;
        gap: 10px;
    }

    .file-indicator.show {
        display: flex;
    }

    .file-indicator-icon {
        font-size: 18px;
    }

    .file-indicator-text {
        flex: 1;
    }

    .file-indicator-remove {
        cursor: pointer;
        font-weight: bold;
        color: #155724;
        background: none;
        border: none;
        padding: 0;
        font-size: 16px;
    }
</style>

<div class="container-main">
    <!-- ZONE GAUCHE: SOUMISSION -->
    <div class="zone-input">
        <h2>📤 Soumettre un Document</h2>
        <p>Déposez un fichier PDF ou collez du texte pour analyser les références</p>

        <!-- TABS: File vs Text -->
        <div class="tabs">
            <button class="tab-button active" data-tab="file-tab">📎 Fichier</button>
            <button class="tab-button" data-tab="text-tab">📝 Texte</button>
        </div>

        <!-- TAB 1: FILE UPLOAD -->
        <div id="file-tab" class="tab-content active">
            <div class="dropzone" id="dropzone">
                <div class="dropzone-icon">📄</div>
                <div class="dropzone-text">Déposez votre PDF ici</div>
                <div class="dropzone-subtext">ou cliquez pour parcourir (Max: 50MB)</div>
            </div>
            <input type="file" id="fileInput" class="file-input" accept=".pdf">
        </div>

        <!-- TAB 2: TEXT INPUT -->
        <div id="text-tab" class="tab-content">
            <textarea id="textInput" class="textarea-input" placeholder="Collez votre texte ou vos références ici..."></textarea>
        </div>

        <!-- VALIDATION MESSAGES -->
        <div id="validationContainer"></div>

        <!-- FILE/TEXT INDICATOR -->
        <div id="fileIndicator" class="file-indicator">
            <span class="file-indicator-icon">✓</span>
            <span class="file-indicator-text" id="indicatorText"></span>
            <button class="file-indicator-remove" id="clearIndicatorBtn">✕</button>
        </div>

        <!-- BUTTON ACTION -->
        <button class="btn-analyze" id="analyzeBtn">🚀 Lancer l'Analyse</button>
    </div>

    <!-- ZONE DROITE: RÉSULTATS -->
    <div class="zone-output">
        <h2>📊 Résultats</h2>

        <!-- EMPTY STATE -->
        <div id="emptyState" class="empty-state">
            <div class="empty-state-icon">✨</div>
            <div class="empty-state-text">Les résultats apparaîtront ici après l'analyse</div>
        </div>

        <!-- LOADING STATE -->
        <div id="loadingState" class="loading" style="display: none;">
            <div class="spinner"></div>
            <span>Analyse en cours...</span>
        </div>

        <!-- STATS BAR -->
        <div id="statsBar" class="stats-bar" style="display: none;">
            <div class="stat-item">
                <div class="stat-number" id="totalCount">0</div>
                <div class="stat-label">Total</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="excellentCount">0</div>
                <div class="stat-label">Vérifiées</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="uncertainCount">0</div>
                <div class="stat-label">Incertaines</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="hallucCount">0</div>
                <div class="stat-label">Hallucinations</div>
            </div>
        </div>

        <!-- RESULTS LIST -->
        <div id="resultsList" class="results-list"></div>
    </div>
</div>

<script>
    // ===== TAB SWITCHING =====
    document.querySelectorAll('.tab-button').forEach(button => {
        button.addEventListener('click', function() {
            const tabName = this.dataset.tab;

            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });

            // Remove active class from all buttons
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active');
            });

            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            this.classList.add('active');

            // Reset validation
            clearValidation();
        });
    });

    // ===== FILE HANDLING =====
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('fileInput');

    dropzone.addEventListener('click', () => fileInput.click());

    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.classList.add('active');
    });

    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('active');
    });

    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('active');

        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFileSelect(files[0]);
        }
    });

    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            handleFileSelect(e.target.files[0]);
        }
    });

    // ===== TEXT HANDLING =====
    const textInput = document.getElementById('textInput');
    textInput.addEventListener('input', () => {
        validateText(textInput.value);
    });

    // ===== VALIDATION FUNCTIONS =====
    function handleFileSelect(file) {
        clearValidation();

        // Validation
        const maxSize = 50 * 1024 * 1024; // 50MB

        if (file.type !== 'application/pdf') {
            showValidation(false, 'Veuillez sélectionner un fichier PDF');
            return;
        }

        if (file.size > maxSize) {
            showValidation(false, 'Le fichier dépasse la limite de 50MB');
            return;
        }

        showValidation(true, `Fichier accepté: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)}MB)`);
        showFileIndicator(`📄 ${file.name} (${(file.size / 1024 / 1024).toFixed(2)}MB)`);
        enableAnalyzeButton();
    }

    function validateText(text) {
        clearValidation();

        if (!text.trim()) {
            hideFileIndicator();
            return;
        }

        // Simple regex to detect reference-like patterns
        const hasReferences = /(\[\d+\]|et al|vol\.?|pp\.?|http|doi|arxiv)/gi.test(text);
        const wordCount = text.trim().split(/\s+/).length;

        if (wordCount < 10) {
            showValidation(false, 'Veuillez entrer au moins 10 mots');
            hideFileIndicator();
            return;
        }

        if (hasReferences) {
            showValidation(true, `Texte valide (${wordCount} mots, ${Math.floor(wordCount / 100)} références détectées)`);
            showFileIndicator(`📝 Texte (${wordCount} mots)`);
        } else {
            showValidation(true, `Texte valide (${wordCount} mots)`);
            showFileIndicator(`📝 Texte (${wordCount} mots)`);
        }

        enableAnalyzeButton();
    }

    function showValidation(isValid, message) {
        const container = document.getElementById('validationContainer');
        const div = document.createElement('div');
        div.className = `validation-item ${isValid ? 'validation-success' : 'validation-error'}`;
        div.textContent = message;
        container.appendChild(div);
    }

    function clearValidation() {
        document.getElementById('validationContainer').innerHTML = '';
    }

    function enableAnalyzeButton() {
        document.getElementById('analyzeBtn').classList.add('visible');
    }

    // ===== ANALYZE FUNCTION =====
    document.getElementById('analyzeBtn').addEventListener('click', async () => {
        const loadingState = document.getElementById('loadingState');
        const emptyState = document.getElementById('emptyState');
        const analyzeBtn = document.getElementById('analyzeBtn');

        loadingState.style.display = 'flex';
        emptyState.style.display = 'none';
        analyzeBtn.disabled = true;

        // Simulate API call (replace with real backend)
        setTimeout(() => {
            displayMockResults();
            loadingState.style.display = 'none';
            analyzeBtn.disabled = false;
        }, 2000);
    });

    // ===== DISPLAY RESULTS =====
    function displayMockResults() {
        const mockData = [{
                score: 95,
                title: "Smith, J. (2021). Machine Learning Fundamentals. Nature Reviews.",
                authors: "John Smith, Alice Brown",
                year: 2021,
                journal: "Nature Reviews",
                justification: "Cette référence existe effectivement dans PubMed. Le score est excellent - tous les paramètres correspondent."
            },
            {
                score: 75,
                title: "Johnson et al. (2019). Artificial Intelligence in Healthcare.",
                authors: "David Johnson, et al.",
                year: 2019,
                journal: "IEEE Transactions on Medical Imaging",
                justification: "La référence existe mais le titre est légèrement différent. L'année et les auteurs correspondent."
            },
            {
                score: 45,
                title: "Lee, M. (2023). Quantum Computing Breakthrough. Quantum Science Weekly.",
                authors: "Michael Lee",
                year: 2023,
                journal: "Quantum Science Weekly",
                justification: "Le journal 'Quantum Science Weekly' n'existe pas. C'est probablement une hallucination. L'auteur est inconnu dans les bases de données académiques."
            },
            {
                score: 88,
                title: "Garcia, P., Rodriguez, L. (2022). Deep Neural Networks. ACM Computing Surveys.",
                authors: "Pedro Garcia, Luis Rodriguez",
                year: 2022,
                journal: "ACM Computing Surveys",
                justification: "Référence valide trouvée dans ACM Digital Library. Tous les détails correspondent correctement."
            }
        ];

        const resultsList = document.getElementById('resultsList');
        const statsBar = document.getElementById('statsBar');

        resultsList.innerHTML = '';

        let stats = {
            total: mockData.length,
            excellent: 0,
            uncertain: 0,
            hallucination: 0
        };

        mockData.forEach((item, index) => {
            // Count stats
            if (item.score >= 90) stats.excellent++;
            else if (item.score >= 60) stats.uncertain++;
            else stats.hallucination++;

            // Create result item
            const resultItem = createResultItem(item);
            resultsList.appendChild(resultItem);
        });

        // Update stats
        document.getElementById('totalCount').textContent = stats.total;
        document.getElementById('excellentCount').textContent = stats.excellent;
        document.getElementById('uncertainCount').textContent = stats.uncertain;
        document.getElementById('hallucCount').textContent = stats.hallucination;
        statsBar.style.display = 'flex';
    }

    function createResultItem(data) {
        const item = document.createElement('div');
        item.className = 'result-item';

        const scoreClass = data.score >= 90 ? 'score-excellent' :
            data.score >= 60 ? 'score-uncertain' : 'score-hallucination';

        item.innerHTML = `
        <div class="result-header" onclick="toggleDetails(this)">
            <div class="result-score ${scoreClass}">${data.score}%</div>
            <div class="result-content">
                <div class="result-title">${data.title}</div>
                <div class="result-meta">${data.authors} • ${data.year}</div>
            </div>
            <div class="result-toggle">+</div>
        </div>
        <div class="result-details">
            <div class="detail-section">
                <div class="detail-label">Auteurs</div>
                <div class="detail-value">${data.authors}</div>
            </div>
            <div class="detail-section">
                <div class="detail-label">Année</div>
                <div class="detail-value">${data.year}</div>
            </div>
            <div class="detail-section">
                <div class="detail-label">Journal/Source</div>
                <div class="detail-value">${data.journal}</div>
            </div>
            <div class="detail-section">
                <div class="detail-label">Justification IA</div>
                <div class="ai-explanation">${data.justification}</div>
            </div>
        </div>
    `;

        return item;
    }

    function toggleDetails(header) {
        const toggle = header.querySelector('.result-toggle');
        const details = header.nextElementSibling;

        toggle.classList.toggle('open');
        details.classList.toggle('open');
    }

    function showFileIndicator(text) {
        const indicator = document.getElementById('fileIndicator');
        const indicatorText = document.getElementById('indicatorText');
        indicatorText.textContent = text;
        indicator.classList.add('show');
    }

    function hideFileIndicator() {
        const indicator = document.getElementById('fileIndicator');
        indicator.classList.remove('show');
    }

    // Clear indicator on button click
    document.getElementById('clearIndicatorBtn').addEventListener('click', (e) => {
        e.preventDefault();
        hideFileIndicator();
        document.getElementById('fileInput').value = '';
        document.getElementById('textInput').value = '';
        document.getElementById('analyzeBtn').disabled = true;
    });
</script>

<?php
include "footer.php";
?>