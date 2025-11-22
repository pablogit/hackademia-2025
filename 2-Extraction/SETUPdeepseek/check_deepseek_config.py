"""
Script de vérification rapide de la configuration DeepSeek.
Teste la connexion API sans traiter de PDF.
"""

from openai import OpenAI
import sys
# Importer la clé API
try:
    from credentials import deepseekkey as api_key
except ImportError:
    print("❌ Erreur: Fichier credentials.py introuvable ou clé deepseekkey manquante")
    print("\nCréez un fichier credentials.py avec:")
    print('deepseekkey = "sk-votre_clé_ici"')
    sys.exit(1)

def test_connection():
    """Test de connexion à DeepSeek API."""
    print("=" * 80)
    print("TEST DE CONNEXION DEEPSEEK API")
    print("=" * 80)

    # Vérifier la clé
    if not api_key or api_key == "YOUR_DEEPSEEK_API_KEY_HERE":
        print("\n❌ Erreur: Clé API non configurée")
        print("\nÉtapes:")
        print("1. Allez sur https://platform.deepseek.com")
        print("2. Créez un compte et obtenez une clé API")
        print("3. Modifiez credentials.py:")
        print('   deepseekkey = "sk-votre_clé_ici"')
        return False

    print(f"\n✅ Clé API trouvée: {api_key[:10]}...{api_key[-4:]}")

    # Test de connexion
    print("\n🤖 Test de connexion à DeepSeek...")

    try:
        client = OpenAI(
            api_key=api_key,
            base_url="https://api.deepseek.com"
        )

        # Test simple
        response = client.chat.completions.create(
            model="deepseek-chat",
            messages=[
                {"role": "system", "content": "Tu es un assistant utile."},
                {"role": "user", "content": "Réponds juste 'OK' si tu me reçois."}
            ],
            temperature=0.1,
            max_tokens=10
        )

        result = response.choices[0].message.content.strip()

        print(f"✅ Connexion réussie!")
        print(f"📩 Réponse de DeepSeek: {result}")

        print("\n" + "=" * 80)
        print("✅ CONFIGURATION VALIDE - PRÊT À UTILISER")
        print("=" * 80)

        print("\nVous pouvez maintenant utiliser:")
        print("  python test_sample_deepseek.py  # Test sans PDF")
        print("  python extract_with_deepseek.py exemple_article.pdf  # Extraction réelle")

        return True

    except Exception as e:
        print(f"\n❌ Erreur de connexion: {e}")
        print("\nVérifiez:")
        print("1. Votre clé API est valide")
        print("2. Vous avez du crédit sur votre compte DeepSeek")
        print("3. Votre connexion internet fonctionne")
        return False


if __name__ == "__main__":
    success = test_connection()
    sys.exit(0 if success else 1)

