"""
Application graphique pour l'extraction de bibliographie PDF en json
"""

import tkinter as tk
from tkinter import ttk, filedialog, messagebox, scrolledtext
from pathlib import Path    
import sys
from extract_bibliography import count_references


class BibliographyExtractorApp:
    """Interface graphique pour l'extraction de bibliographie PDF."""
    
    def __init__(self, root):
        self.root = root
        self.root.title("Extracteur de Bibliographie PDF")
        self.root.geometry("900x700")
        self.root.resizable(True, True)
        
        # Variables
        self.pdf_path = tk.StringVar()
        
        # Créer l'interface
        self.create_widgets()
        
    def create_widgets(self):
        """Crée tous les widgets de l'interface."""
        
        # Frame principal
        main_frame = ttk.Frame(self.root, padding="10")
        main_frame.grid(row=0, column=0, sticky=(tk.W, tk.E, tk.N, tk.S))
        
        # Configuration de la grille
        self.root.columnconfigure(0, weight=1)
        self.root.rowconfigure(0, weight=1)
        main_frame.columnconfigure(1, weight=1)
        main_frame.rowconfigure(3, weight=1)
        
        # Titre
        title_label = ttk.Label(
            main_frame, 
            text="📚 Extracteur de Bibliographie PDF", 
            font=("Arial", 16, "bold")
        )
        title_label.grid(row=0, column=0, columnspan=3, pady=(0, 20))
        
        # Section sélection de fichier
        file_frame = ttk.LabelFrame(main_frame, text="1. Sélectionner le fichier PDF", padding="10")
        file_frame.grid(row=1, column=0, columnspan=3, sticky=(tk.W, tk.E), pady=5)
        file_frame.columnconfigure(1, weight=1)
        
        ttk.Label(file_frame, text="Fichier PDF:").grid(row=0, column=0, sticky=tk.W, padx=5)
        
        self.file_entry = ttk.Entry(file_frame, textvariable=self.pdf_path, width=50)
        self.file_entry.grid(row=0, column=1, sticky=(tk.W, tk.E), padx=5)
        
        browse_btn = ttk.Button(file_frame, text="Parcourir...", command=self.browse_file)
        browse_btn.grid(row=0, column=2, padx=5)
        
        
        # Bouton d'extraction (comptage rapide)
        extract_btn = ttk.Button(
            main_frame, 
            text="🚀 Extraire la Bibliographie (JSON)", 
            command=self.count_references_only,
            style="Accent.TButton"
        )
        extract_btn.grid(row=2, column=0, columnspan=3, padx=5, pady=10)
        
        # Section résultats
        results_frame = ttk.LabelFrame(main_frame, text="2. Résultats", padding="10")
        results_frame.grid(row=3, column=0, columnspan=3, sticky=(tk.W, tk.E, tk.N, tk.S), pady=5)
        results_frame.columnconfigure(0, weight=1)
        results_frame.rowconfigure(0, weight=1)
        
        # Zone de texte avec scrollbar
        self.results_text = scrolledtext.ScrolledText(
            results_frame, 
            width=80, 
            height=25,
            wrap=tk.WORD,
            font=("Courier", 10)
        )
        self.results_text.grid(row=0, column=0, sticky=(tk.W, tk.E, tk.N, tk.S))
        
        # Boutons d'action sur les résultats
        action_frame = ttk.Frame(main_frame)
        action_frame.grid(row=4, column=0, columnspan=3, pady=10)
        
        save_btn = ttk.Button(action_frame, text="💾 Enregistrer dans un fichier", command=self.save_results)
        save_btn.grid(row=0, column=0, padx=5)
        
        clear_btn = ttk.Button(action_frame, text="🗑️ Effacer", command=self.clear_results)
        clear_btn.grid(row=0, column=1, padx=5)
        
        copy_btn = ttk.Button(action_frame, text="📋 Copier", command=self.copy_results)
        copy_btn.grid(row=0, column=2, padx=5)
        
    def browse_file(self):
        """Ouvre le dialogue de sélection de fichier."""
        filename = filedialog.askopenfilename(
            title="Sélectionner un fichier PDF",
            filetypes=[("Fichiers PDF", "*.pdf"), ("Tous les fichiers", "*.*")]
        )
        if filename:
            self.pdf_path.set(filename)
    
    def count_references_only(self):
        """Compte rapidement le nombre de références sans extraire tout le document."""
        pdf_file = self.pdf_path.get()
        
        if not pdf_file:
            messagebox.showerror("Erreur", "Veuillez sélectionner un fichier PDF.")
            return
        
        if not Path(pdf_file).exists():
            messagebox.showerror("Erreur", f"Le fichier '{pdf_file}' est introuvable.")
            return
        
        try:
            # Afficher un message de chargement
            self.results_text.delete(1.0, tk.END)
            self.results_text.insert(tk.END, "⏳ Comptage en cours...\n")
            self.results_text.update()
            
            # Compter les références rapidement
            count = count_references(pdf_file)
            
            # Afficher le résultat
            self.results_text.delete(1.0, tk.END)
            
            result = f"{'='*80}\n"
            result += f"📊 NOMBRE DE RÉFÉRENCES BIBLIOGRAPHIQUES\n"
            result += f"{'='*80}\n\n"
            
            if count == 0:
                result += f"⚠️  Aucune référence trouvée dans le document.\n\n"
                result += f"Vérifiez que le PDF contient une section 'Bibliographie' ou 'Références'.\n"
                messagebox.showwarning(
                    "Aucune référence", 
                    "Aucune référence bibliographique n'a été trouvée dans le document."
                )
            else:
                result += f"✅ Nombre total de références trouvées: {count}\n\n"
                result += f"📄 Fichier analysé: {Path(pdf_file).name}\n"
                result += f"📁 Chemin: {pdf_file}\n"
                
                messagebox.showinfo(
                    "Résultat", 
                    f"✅ {count} référence(s) trouvée(s) dans le document!"
                )
            
            self.results_text.insert(tk.END, result)
            
        except FileNotFoundError:
            messagebox.showerror("Erreur", f"Le fichier '{pdf_file}' est introuvable.")
        except Exception as e:
            error_msg = f"Erreur lors du comptage:\n{str(e)}"
            self.results_text.delete(1.0, tk.END)
            self.results_text.insert(tk.END, f"❌ {error_msg}")
            messagebox.showerror("Erreur", error_msg)
    
    def save_results(self):
        """Enregistre les résultats dans un fichier."""
        content = self.results_text.get(1.0, tk.END)
        
        if not content.strip() or "Comptage en cours" in content or (content.strip().startswith("⚠️") and len(content.strip()) < 100):
            messagebox.showwarning("Avertissement", "Aucun résultat à enregistrer.")
            return
        
        filename = filedialog.asksaveasfilename(
            title="Enregistrer les résultats",
            defaultextension=".txt",
            filetypes=[
                ("Fichiers texte", "*.txt"),
                ("Tous les fichiers", "*.*")
            ]
        )
        
        if filename:
            try:
                with open(filename, 'w', encoding='utf-8') as f:
                    f.write(content)
                messagebox.showinfo("Succès", f"Résultats enregistrés dans:\n{filename}")
            except Exception as e:
                messagebox.showerror("Erreur", f"Erreur lors de l'enregistrement:\n{str(e)}")
    
    def clear_results(self):
        """Efface les résultats affichés."""
        self.results_text.delete(1.0, tk.END)
    
    def copy_results(self):
        """Copie les résultats dans le presse-papiers."""
        content = self.results_text.get(1.0, tk.END)
        if content.strip():
            self.root.clipboard_clear()
            self.root.clipboard_append(content)
            messagebox.showinfo("Succès", "Résultats copiés dans le presse-papiers!")
        else:
            messagebox.showwarning("Avertissement", "Aucun résultat à copier.")


def main():
    """Fonction principale pour lancer l'application."""
    root = tk.Tk()
    
    # Style moderne
    style = ttk.Style()
    style.theme_use('clam')
    
    app = BibliographyExtractorApp(root)
    
    # Centrer la fenêtre
    root.update_idletasks()
    width = root.winfo_width()
    height = root.winfo_height()
    x = (root.winfo_screenwidth() // 2) - (width // 2)
    y = (root.winfo_screenheight() // 2) - (height // 2)
    root.geometry(f'{width}x{height}+{x}+{y}')
    
    root.mainloop()


if __name__ == "__main__":
    main()

