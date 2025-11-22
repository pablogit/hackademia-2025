#!/bin/bash

# RefCheck - Script de déploiement
# Usage: ./deploy.sh [environment]

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
ENVIRONMENT=${1:-development}
PROJECT_NAME="RefCheck"
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DEPLOY_DIR="/var/www/html/refcheck"
BACKUP_DIR="/var/backups/refcheck"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Fonctions
print_header() {
    echo -e "${BLUE}=== $1 ===${NC}\n"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}\n"
}

print_error() {
    echo -e "${RED}✗ $1${NC}\n"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}\n"
}

# Vérifications
check_prerequisites() {
    print_header "Vérification des prérequis"
    
    # Vérifier PHP
    if ! command -v php &> /dev/null; then
        print_error "PHP n'est pas installé"
        exit 1
    fi
    print_success "PHP $(php -v | head -n 1)"
    
    # Vérifier curl
    if ! command -v curl &> /dev/null; then
        print_error "curl n'est pas installé"
        exit 1
    fi
    print_success "curl installé"
    
    # Vérifier git
    if ! command -v git &> /dev/null; then
        print_warning "git n'est pas installé (non critique)"
    else
        print_success "git $(git --version)"
    fi
}

# Créer les répertoires
setup_directories() {
    print_header "Configuration des répertoires"
    
    if [ ! -d "$DEPLOY_DIR" ]; then
        mkdir -p "$DEPLOY_DIR"
        print_success "Dossier créé: $DEPLOY_DIR"
    fi
    
    mkdir -p "$DEPLOY_DIR/uploads"
    mkdir -p "$DEPLOY_DIR/logs"
    mkdir -p "$BACKUP_DIR"
    
    print_success "Répertoires configurés"
}

# Backup
backup_existing() {
    print_header "Sauvegarde de l'installation existante"
    
    if [ -d "$DEPLOY_DIR/exemple_biblio" ]; then
        BACKUP_PATH="$BACKUP_DIR/$TIMESTAMP"
        mkdir -p "$BACKUP_PATH"
        cp -r "$DEPLOY_DIR" "$BACKUP_PATH/refcheck"
        print_success "Sauvegarde créée: $BACKUP_PATH"
    else
        print_warning "Aucune installation existante à sauvegarder"
    fi
}

# Copier les fichiers
copy_files() {
    print_header "Copie des fichiers"
    
    cp -r "$PROJECT_DIR/exemple_biblio"/* "$DEPLOY_DIR/"
    print_success "Fichiers copiés"
}

# Configurer les permissions
setup_permissions() {
    print_header "Configuration des permissions"
    
    if [ "$ENVIRONMENT" = "production" ]; then
        chmod -R 755 "$DEPLOY_DIR"
        chmod -R 777 "$DEPLOY_DIR/uploads"
        chmod -R 777 "$DEPLOY_DIR/logs"
        sudo chown -R www-data:www-data "$DEPLOY_DIR"
        print_success "Permissions configurées (production)"
    else
        chmod -R 755 "$DEPLOY_DIR"
        chmod -R 777 "$DEPLOY_DIR/uploads"
        chmod -R 777 "$DEPLOY_DIR/logs"
        print_success "Permissions configurées (développement)"
    fi
}

# Configuration
configure_app() {
    print_header "Configuration de l'application"
    
    if [ ! -f "$DEPLOY_DIR/config.php" ]; then
        cp "$DEPLOY_DIR/../config.php.example" "$DEPLOY_DIR/config.php"
        print_warning "config.php créé - VEUILLEZ LE CONFIGURER"
    fi
    
    if [ ! -f "$DEPLOY_DIR/.env" ]; then
        cp "$DEPLOY_DIR/.env.example" "$DEPLOY_DIR/.env"
        print_warning ".env créé - VEUILLEZ LE CONFIGURER"
    fi
    
    # Adapter la configuration selon l'environnement
    if [ "$ENVIRONMENT" = "production" ]; then
        sed -i "s/DEBUG_MODE = true/DEBUG_MODE = false/" "$DEPLOY_DIR/config.php"
        sed -i "s/ENVIRONMENT = 'development'/ENVIRONMENT = 'production'/" "$DEPLOY_DIR/config.php"
        print_success "Configuration production appliquée"
    fi
}

# Tester l'installation
test_installation() {
    print_header "Test de l'installation"
    
    if [ -f "$DEPLOY_DIR/config.php" ]; then
        php -l "$DEPLOY_DIR/config.php" > /dev/null && print_success "config.php valide"
    fi
    
    if [ -f "$DEPLOY_DIR/header.php" ]; then
        php -l "$DEPLOY_DIR/header.php" > /dev/null && print_success "header.php valide"
    fi
    
    if [ -f "$DEPLOY_DIR/refcheck.php" ]; then
        php -l "$DEPLOY_DIR/refcheck.php" > /dev/null && print_success "refcheck.php valide"
    fi
}

# Nettoyer
cleanup() {
    print_header "Nettoyage"
    
    # Supprimer les anciens uploads
    find "$DEPLOY_DIR/uploads" -type f -mtime +7 -delete 2>/dev/null
    print_success "Anciens uploads supprimés"
    
    # Compresser les logs
    gzip -q "$DEPLOY_DIR/logs"/*.log 2>/dev/null
    print_success "Logs compressés"
}

# Health check
health_check() {
    print_header "Vérification de santé"
    
    if [ -d "$DEPLOY_DIR/uploads" ] && [ -w "$DEPLOY_DIR/uploads" ]; then
        print_success "Dossier uploads accessible"
    else
        print_error "Dossier uploads non accessible"
        return 1
    fi
    
    if [ -d "$DEPLOY_DIR/logs" ] && [ -w "$DEPLOY_DIR/logs" ]; then
        print_success "Dossier logs accessible"
    else
        print_error "Dossier logs non accessible"
        return 1
    fi
    
    if php -l "$DEPLOY_DIR/refcheck.php" > /dev/null 2>&1; then
        print_success "Interface principale valide"
    else
        print_error "Interface principale invalide"
        return 1
    fi
}

# Résumé
show_summary() {
    print_header "Résumé du déploiement"
    
    echo -e "Projet          : ${GREEN}$PROJECT_NAME${NC}"
    echo -e "Environnement   : ${GREEN}$ENVIRONMENT${NC}"
    echo -e "Répertoire      : ${GREEN}$DEPLOY_DIR${NC}"
    echo -e "Sauvegarde      : ${GREEN}$BACKUP_DIR/$TIMESTAMP${NC}"
    echo -e "Timestamp       : ${GREEN}$TIMESTAMP${NC}"
    echo ""
    echo -e "${GREEN}✓ Déploiement réussi!${NC}"
    echo ""
    echo "Prochaines étapes:"
    echo "1. Éditer $DEPLOY_DIR/.env avec vos paramètres"
    echo "2. Éditer $DEPLOY_DIR/config.php si nécessaire"
    echo "3. Configurer le serveur web (Apache/Nginx)"
    echo "4. Redémarrer le serveur web"
    echo "5. Accéder à http://localhost/refcheck/welcome_refcheck.php"
}

# Rollback
rollback() {
    print_header "Rollback du déploiement"
    
    if [ -d "$BACKUP_DIR/$TIMESTAMP" ]; then
        rm -rf "$DEPLOY_DIR"
        cp -r "$BACKUP_DIR/$TIMESTAMP/refcheck" "$DEPLOY_DIR"
        print_success "Rollback effectué"
    else
        print_error "Aucune sauvegarde disponible"
        exit 1
    fi
}

# Menu principal
main() {
    clear
    echo -e "${BLUE}"
    echo "╔════════════════════════════════════════╗"
    echo "║  RefCheck - Script de Déploiement      ║"
    echo "║  Environnement: $ENVIRONMENT"
    echo "╚════════════════════════════════════════╝"
    echo -e "${NC}"
    
    # Afficher le menu si pas d'arguments
    if [ $# -eq 0 ]; then
        echo "Usage: $0 [development|production|test]"
        echo ""
        echo "Options:"
        echo "  development  - Déploiement en développement (par défaut)"
        echo "  production   - Déploiement en production"
        echo "  test         - Mode test sans modification"
        echo "  rollback     - Revenir à la sauvegarde précédente"
        exit 0
    fi
    
    # Validation environnement
    if [[ ! "$ENVIRONMENT" =~ ^(development|production|test|rollback)$ ]]; then
        print_error "Environnement invalide: $ENVIRONMENT"
        exit 1
    fi
    
    # Rollback
    if [ "$ENVIRONMENT" = "rollback" ]; then
        rollback
        exit 0
    fi
    
    # Mode test
    if [ "$ENVIRONMENT" = "test" ]; then
        print_header "MODE TEST - Aucune modification"
        check_prerequisites
        exit 0
    fi
    
    # Déploiement normal
    check_prerequisites
    backup_existing
    setup_directories
    copy_files
    setup_permissions
    configure_app
    test_installation
    cleanup
    health_check
    show_summary
}

# Trap errors
trap 'print_error "Une erreur est survenue à la ligne $LINENO"; exit 1' ERR

# Exécuter
main "$@"
