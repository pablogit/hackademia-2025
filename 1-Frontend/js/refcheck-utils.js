/**
 * RefCheck - Utilitaires JavaScript
 * Fonctionnalités réutilisables pour l'interface
 */

// ===== VALIDATION HELPERS =====
const ValidationUtils = {
    /**
     * Valide un fichier PDF
     */
    validatePDF: function(file) {
        const maxSize = 50 * 1024 * 1024; // 50MB
        
        if (file.type !== 'application/pdf') {
            return {
                valid: false,
                message: 'Veuillez sélectionner un fichier PDF'
            };
        }
        
        if (file.size > maxSize) {
            return {
                valid: false,
                message: 'Le fichier dépasse la limite de 50MB'
            };
        }
        
        return {
            valid: true,
            message: `Fichier accepté: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)}MB)`
        };
    },

    /**
     * Valide du texte brut
     */
    validateText: function(text) {
        const trimmed = text.trim();
        
        if (!trimmed) {
            return {
                valid: false,
                message: null
            };
        }
        
        const wordCount = trimmed.split(/\s+/).length;
        
        if (wordCount < 10) {
            return {
                valid: false,
                message: 'Veuillez entrer au moins 10 mots'
            };
        }
        
        // Détection de références
        const hasReferences = /(\[\d+\]|et al|vol\.?|pp\.?|http|doi|arxiv)/gi.test(text);
        
        const result = {
            valid: true,
            message: `Texte valide (${wordCount} mots`,
            wordCount: wordCount
        };
        
        if (hasReferences) {
            result.message += `, ${Math.floor(wordCount / 100)} références détectées)`;
        } else {
            result.message += ')';
        }
        
        return result;
    },

    /**
     * Valide une adresse email
     */
    validateEmail: function(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
};

// ===== UI HELPERS =====
const UIUtils = {
    /**
     * Affiche un message de validation
     */
    showValidation: function(containerId, isValid, message) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        const div = document.createElement('div');
        div.className = `validation-item ${isValid ? 'validation-success' : 'validation-error'}`;
        div.textContent = message;
        
        container.appendChild(div);
        
        // Animation
        div.style.animation = 'slideIn 0.3s ease';
        
        return div;
    },

    /**
     * Efface tous les messages de validation
     */
    clearValidation: function(containerId) {
        const container = document.getElementById(containerId);
        if (container) {
            container.innerHTML = '';
        }
    },

    /**
     * Affiche un état de chargement
     */
    showLoading: function(elementId, show = true) {
        const element = document.getElementById(elementId);
        if (element) {
            element.style.display = show ? 'flex' : 'none';
        }
    },

    /**
     * Basculer la visibilité d'un élément
     */
    toggle: function(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.style.display = element.style.display === 'none' ? 'block' : 'none';
        }
    },

    /**
     * Affiche une notification toast
     */
    showToast: function(message, type = 'info', duration = 3000) {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 6px;
            background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#667eea'};
            color: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 9999;
            animation: slideIn 0.3s ease;
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'fadeOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    },

    /**
     * Affiche un modal de confirmation
     */
    showConfirm: function(message, onConfirm, onCancel) {
        const modal = document.createElement('div');
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        `;
        
        const content = document.createElement('div');
        content.style.cssText = `
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            max-width: 400px;
        `;
        
        content.innerHTML = `
            <p style="margin-bottom: 20px; color: #333;">${message}</p>
            <div style="display: flex; gap: 10px;">
                <button onclick="this.closest('div').parentElement.remove()" class="btn btn-secondary" style="flex: 1;">Annuler</button>
                <button onclick="this.closest('div').parentElement.remove()" class="btn btn-primary" style="flex: 1;">Confirmer</button>
            </div>
        `;
        
        modal.appendChild(content);
        document.body.appendChild(modal);
        
        // Event listeners
        const buttons = content.querySelectorAll('button');
        buttons[0].addEventListener('click', () => {
            modal.remove();
            if (onCancel) onCancel();
        });
        buttons[1].addEventListener('click', () => {
            modal.remove();
            if (onConfirm) onConfirm();
        });
    }
};

// ===== API HELPERS =====
const APIUtils = {
    /**
     * Effectue un appel API avec gestion d'erreur
     */
    fetch: async function(url, options = {}) {
        try {
            const response = await fetch(url, {
                headers: {
                    'Content-Type': 'application/json',
                    ...options.headers
                },
                ...options
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    /**
     * Upload un fichier avec progress
     */
    uploadFile: async function(file, endpoint, onProgress) {
        const formData = new FormData();
        formData.append('file', file);
        
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            
            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    if (onProgress) onProgress(percentComplete);
                }
            });
            
            xhr.addEventListener('load', () => {
                if (xhr.status === 200) {
                    resolve(JSON.parse(xhr.responseText));
                } else {
                    reject(new Error(`Upload failed with status ${xhr.status}`));
                }
            });
            
            xhr.addEventListener('error', () => {
                reject(new Error('Upload failed'));
            });
            
            xhr.open('POST', endpoint);
            xhr.send(formData);
        });
    }
};

// ===== FORMAT HELPERS =====
const FormatUtils = {
    /**
     * Formate un score en pourcentage avec couleur
     */
    formatScore: function(score) {
        let color, status;
        
        if (score >= 90) {
            color = '#28a745';
            status = 'Excellent';
        } else if (score >= 60) {
            color = '#ffc107';
            status = 'Incertain';
        } else {
            color = '#dc3545';
            status = 'Hallucination';
        }
        
        return {
            score: score,
            percentage: `${score}%`,
            color: color,
            status: status
        };
    },

    /**
     * Formate la date en format lisible
     */
    formatDate: function(date) {
        const d = new Date(date);
        return d.toLocaleDateString('fr-FR', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    /**
     * Formate les octets en unités lisibles
     */
    formatBytes: function(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    },

    /**
     * Tronque un texte avec ellipsis
     */
    truncate: function(text, length = 100) {
        return text.length > length ? text.substring(0, length) + '...' : text;
    }
};

// ===== STORAGE HELPERS =====
const StorageUtils = {
    /**
     * Sauvegarde un objet dans localStorage
     */
    set: function(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
        } catch (error) {
            console.error('Storage error:', error);
        }
    },

    /**
     * Récupère un objet depuis localStorage
     */
    get: function(key) {
        try {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : null;
        } catch (error) {
            console.error('Storage error:', error);
            return null;
        }
    },

    /**
     * Supprime un objet de localStorage
     */
    remove: function(key) {
        try {
            localStorage.removeItem(key);
        } catch (error) {
            console.error('Storage error:', error);
        }
    },

    /**
     * Efface tout localStorage
     */
    clear: function() {
        try {
            localStorage.clear();
        } catch (error) {
            console.error('Storage error:', error);
        }
    }
};

// ===== EVENT HELPERS =====
const EventUtils = {
    /**
     * Attache un événement au dom avec délégation
     */
    on: function(selector, event, callback) {
        document.addEventListener(event, (e) => {
            if (e.target.matches(selector)) {
                callback(e);
            }
        });
    },

    /**
     * Déclenche un événement personnalisé
     */
    emit: function(eventName, detail) {
        const event = new CustomEvent(eventName, { detail });
        document.dispatchEvent(event);
    },

    /**
     * Écoute un événement personnalisé
     */
    on: function(eventName, callback) {
        document.addEventListener(eventName, callback);
    }
};

// Export pour utilisation en module (optionnel)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        ValidationUtils,
        UIUtils,
        APIUtils,
        FormatUtils,
        StorageUtils,
        EventUtils
    };
}
