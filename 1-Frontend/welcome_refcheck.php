<?php

/**
 * RefCheck - Accueil
 * Page d'accueil du vérificateur de références IA
 */

$page = "home";
$user_name = isset($_SERVER['surname']) ? strtolower($_SERVER['surname']) : 'guest';
$user_mail = isset($_SERVER['mail']) ? strtolower($_SERVER['mail']) : '';

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RefCheck - Vérificateur de Références IA</title>
    <link rel="icon" type="image/png" href="./icon.png">
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            width: 100%;
        }

        header {
            background: rgba(255, 255, 255, 0.95);
            padding: 25px 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        header h1 {
            color: #333;
            font-size: 36px;
            margin-bottom: 5px;
        }

        header p {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .tagline {
            color: #E83131;
            font-weight: 600;
            font-size: 14px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .feature-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .feature-card h3 {
            color: #333;
            font-size: 20px;
            margin-bottom: 10px;
        }

        .feature-card p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }

        .cta-section {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .cta-section h2 {
            color: #333;
            font-size: 32px;
            margin-bottom: 20px;
        }

        .cta-section p {
            color: #666;
            font-size: 16px;
            margin-bottom: 30px;
        }

        .btn-group {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 14px 40px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #E83131 0%, #D71E5E 50%, #C41689 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(232, 49, 49, 0.3);
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #333;
            border: 2px solid #E83131;
        }

        .btn-secondary:hover {
            background: #E83131;
            color: white;
        }

        .feature-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }

        .how-it-works h2 {
            color: #ffffffff;
            font-size: 28px;
            margin-bottom: 30px;
            text-align: center;
        }

        .steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .step {
            text-align: center;
        }

        .step-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #E83131 0%, #D71E5E 50%, #C41689 100%);
            color: white;
            font-size: 24px;
            font-weight: bold;
            border-radius: 50%;
            margin-bottom: 15px;
        }

        .step h3 {
            color: #ffffffff;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .step p {
            color: white;
            font-size: 13px;
            line-height: 1.6;
        }

        footer {
            text-align: center;
            color: rgba(255, 255, 255, 0.8);
            margin-top: 40px;
            font-size: 13px;
        }

        @media (max-width: 768px) {
            header h1 {
                font-size: 32px;
            }

            .cta-section h2 {
                font-size: 24px;
            }

            .btn-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- HEADER -->
        <header>
            <div class="tagline">🔍 Innovation Académique</div>
            <img src="./logo.png" alt="Logo UNIGE" style="height: 60px; margin-bottom: 10px;">
            <h1>RefCheck</h1>
            <p>Vérificateur d'Hallucinations de Références IA</p>
            <p style="font-size: 12px; color: #999;">Déterminez si vos citations bibliographiques sont réelles ou inventées par l'IA</p>
        </header>

        <!-- FEATURES -->
        <div class="features">
            <div class="feature-card">
                <div class="feature-icon">📤</div>
                <h3>Upload Facile</h3>
                <p>Glissez-déposez vos fichiers PDF ou collez directement du texte pour une analyse immédiate.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🎨</div>
                <h3>Résultats Visuels</h3>
                <p>Visualisez vos résultats avec un score et un code couleur sémantique : vert, jaune ou rouge selon la véracité.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🧠</div>
                <h3>Justifications IA</h3>
                <p>Recevez des explications détaillées générées par une IA pour comprendre chaque score.</p>
            </div>
        </div>

        <!-- HOW IT WORKS -->
        <div class="how-it-works">
            <h2>Comment ça fonctionne ?</h2>
            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3>Soumettre</h3>
                    <p>Uploadez un PDF ou collez du texte contenant des références bibliographiques.</p>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <h3>Extraire</h3>
                    <p>Le système extrait automatiquement les références du document source.</p>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <h3>Comparer</h3>
                    <p>Chaque référence est comparée avec des bases de données réelles.</p>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <h3>Analyser</h3>
                    <p>Obtenez un score et une explication pour chaque référence.</p>
                </div>
            </div>
        </div>

        <!-- CTA SECTION -->
        <div class="cta-section">
            <h2>Prêt à vérifier vos références ?</h2>
            <p>Lancez une analyse maintenant et découvrez la qualité de vos citations</p>
            <div class="btn-group">
                <a href="./refcheck.php" class="btn btn-primary">Lancer l'Analyse 🚀</a>
                <a href="#documentation" class="btn btn-secondary">Voir la Documentation</a>
            </div>
        </div>

        <!-- FOOTER -->
        <footer>
            <p>RefCheck © 2025 - HackademIA 2025 | Projet de Vérification Intelligente de Références</p>
        </footer>
    </div>
</body>

</html>