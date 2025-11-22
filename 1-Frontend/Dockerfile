# RefCheck Frontend - Dockerfile

FROM php:7.4-apache

LABEL maintainer="HackademIA 2025"
LABEL description="RefCheck - Vérificateur de Références IA"

# Installer les extensions PHP requises
RUN apt-get update && apt-get install -y \
    curl \
    libcurl4-openssl-dev \
    && docker-php-ext-install curl json \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Activer les modules Apache
RUN a2enmod rewrite \
    && a2enmod headers

# Copier les fichiers de l'application
COPY ./ /var/www/html/

# Créer les répertoires nécessaires
RUN mkdir -p /var/www/html/uploads \
    && mkdir -p /var/www/html/logs \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/uploads \
    && chmod -R 777 /var/www/html/logs

# Configuration Apache
COPY apache-config.conf /etc/apache2/sites-available/refcheck.conf
RUN a2ensite refcheck \
    && a2dissite 000-default

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD curl -f http://localhost/welcome_refcheck.php || exit 1

# Exposer le port
EXPOSE 80

# Lancer Apache
CMD ["apache2-foreground"]
