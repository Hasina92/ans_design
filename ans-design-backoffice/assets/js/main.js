document.addEventListener('DOMContentLoaded', function() {
    
    // --- GESTION DU POPUP DE DÉTAILS ---
    const modal = document.getElementById('detailsModal');
    if (modal) {
        const closeModalBtn = document.getElementById('closeModal');
        const modalBody = document.getElementById('modalBody');

        function openModal() { modal.style.display = 'flex'; }
        function closeModal() { modal.style.display = 'none'; }

        closeModalBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', (event) => {
            if (event.target === modal) closeModal();
        });

        document.body.addEventListener('click', function(event) {
            
            if (event.target.classList.contains('details-btn')) {
                const commandeId = event.target.dataset.commandeId;
                modalBody.innerHTML = '<div class="modal-loading">Chargement...</div>';
                openModal();

                fetch(`ajax/get_order_details.php?id=${commandeId}`)
                    .then(response => {
                        if (!response.ok) throw new Error('Erreur réseau ou commande non trouvée.');
                        return response.json();
                    })
                    .then(data => {
                        // === NOUVELLE STRUCTURE HTML POUR LE STYLE DE LA MAQUETTE ===
                        const contentHtml = `
                            <div class="modal-header">
                                <h2 class="modal-title">Détails de la Commande #${data.id}</h2>
                            </div>
                            <div class="modal-body-columns">
                                <!-- COLONNE DE GAUCHE -->
                                <div class="modal-column-left">
                                    <div class="modal-panel">
                                        <h4 class="modal-subtitle">Infos Clés</h4>
                                        <div class="info-grid">
                                            <span>Client : </span> <strong class="name">${data.prenom} ${data.nom}</strong><br/>
                                            <span>Société : </span> <strong>${data.societe || 'Particulier'}</strong><br/>
                                            <span>Articles : </span> <strong>${data.articles_details || 'N/A'}</strong><br/>
                                            <div class="total-info-grid"><span>Total : </span> <strong>${parseFloat(data.total_ttc).toFixed(2)} €</strong></div>
                                        </div>
                                    </div>

                                    <div class="modal-panel">
                                        <h4 class="modal-subtitle">Gestion du Statut</h4>
                                        <div class="info-grid">
                                        <label for="newStatusSelect">Statut de la Commande</label><br>
                                        <select id="newStatusSelect" class="modal-input">
                                            <option value="En validation" ${data.statut === 'En validation' ? 'selected' : ''}>En validation</option>
                                            <option value="En production" ${data.statut === 'En production' ? 'selected' : ''}>En production</option>
                                            <option value="Livrée" ${data.statut === 'Livrée' ? 'selected' : ''}>Livrée</option>
                                            <option value="Annulé" ${data.statut === 'Annulé' ? 'selected' : ''}>Annulé</option>
                                            <option value="Avis à valider" ${data.statut === 'Avis à valider' ? 'selected' : ''}>Avis à valider</option>
                                        </select>
                                        </div>
                                        
                                    </div>
                                    
                                    <div class="modal-panel">
                                        <h4 class="modal-subtitle">Notes & Retour Client</h4>
                                        <textarea id="avisClient" class="modal-textarea" placeholder="Entrer ici l'avis final du client...">${data.avis_client || ''}</textarea>
                                        <div class="modal-checkbox-wrapper">
                                            <input type="checkbox" id="publierAvis" ${data.publier_avis ? 'checked' : ''}>
                                            <label for="publierAvis">Publier dans 'Avis Clients' sur le site</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- COLONNE DE DROITE -->
                                <div class="modal-column-right">
                                    <div class="modal-panel">
                                        <h4 class="modal-subtitle">Timeline de Production</h4>
                                        <div class="timeline">
                                            <div class="timeline-item completed">
                                                <div class="timeline-content">
                                                    <strong>Validation Fichier (PAO)</strong><br>
                                                    <small>Action requise.</small><br>
                                                    <button class="timeline-btn">Marquer comme fait</button>
                                                </div>
                                                <div class="timeline-icon">✓</div>
                                            </div>
                                            <div class="timeline-item">
                                                <div class="timeline-icon"></div>
                                                <div class="timeline-content">
                                                    <strong>Paiement Avance</strong><br>
                                                    <small>Action requise.</small><br>
                                                    <button class="timeline-btn">Marquer comme fait</button>
                                                </div>
                                            </div>
                                            <div class="timeline-item">
                                                <div class="timeline-icon"></div>
                                                <div class="timeline-content">
                                                    <strong>Préparation fichier PAO</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="modal-panel">
                                        <h4 class="modal-subtitle">Suivi du Retour</h4>
                                        <textarea id="notesSav" class="modal-textarea" placeholder="Note interne sur les problèmes de livraison, demandes spécifique, etc.">${data.notes_sav || ''}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <p id="updateStatusMessage"></p>
                                <button id="saveOrderDetailsBtn" class="modal-save-btn" data-commande-id="${data.id}">Valider le formulaire</button>
                            </div>
                        `;
                        modalBody.innerHTML = contentHtml;
                    })
                    .catch(error => {
                        modalBody.innerHTML = `<p style="color:red;">${error.message}</p>`;
                    });
            }

            if (event.target.id === 'saveOrderDetailsBtn') {
                const button = event.target;
                const commandeId = button.dataset.commandeId;
                const messageEl = document.getElementById('updateStatusMessage');

                const formData = {
                    commande_id: commandeId,
                    statut: document.getElementById('newStatusSelect').value,
                    avis_client: document.getElementById('avisClient').value,
                    notes_sav: document.getElementById('notesSav').value,
                    publier_avis: document.getElementById('publierAvis').checked
                };
                
                button.disabled = true;
                messageEl.textContent = 'Sauvegarde en cours...';
                messageEl.style.color = '#333';

                fetch('ajax/update_full_order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        messageEl.style.color = 'green';
                        messageEl.textContent = data.message;
                        setTimeout(() => {
                            closeModal();
                            window.location.reload(); 
                        }, 1500);
                    } else {
                        throw new Error(data.message);
                    }
                })
                .catch(error => {
                    messageEl.style.color = 'red';
                    messageEl.textContent = `Erreur : ${error.message}`;
                })
                .finally(() => {
                    button.disabled = false;
                });
            }
        });
    }
    
    // --- GESTION DE LA RECHERCHE DE CLIENTS ---
    const clientSearchInput = document.getElementById('clientSearchInput');
    if (clientSearchInput) {
        const clientListContainer = document.getElementById('clientListContainer');

        clientSearchInput.addEventListener('keyup', function() {
            const query = this.value;

            fetch(`ajax/search_clients.php?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(clients => {
                    let html = '';
                    if (clients.length > 0) {
                        clients.forEach(client => {
                            html += `
                                <a href="clients_commandes.php?client_id=${client.id}" style="display:block; padding:15px; border:1px solid #ddd; border-radius:5px; margin-bottom:10px;">
                                    <strong>${client.prenom} ${client.nom}</strong>
                                    <br>
                                    <small>${client.societe || ''}</small>
                                </a>
                            `;
                        });
                    } else {
                        html = '<p>Aucun client trouvé.</p>';
                    }
                    clientListContainer.innerHTML = html;
                })
                .catch(error => {
                    clientListContainer.innerHTML = '<p>Erreur lors de la recherche.</p>';
                });
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const addBtn = document.getElementById('add-caracteristique');
    const container = document.getElementById('caracteristiques-container');
    const template = document.getElementById('caracteristique-template');

    if (addBtn && container && template) {
        let caracteristiqueIndex = container.children.length;

        addBtn.addEventListener('click', function() {
            // Cloner le template
            const cloneHtml = template.innerHTML.replace(/__INDEX__/g, caracteristiqueIndex);
            const newDiv = document.createElement('div');
            newDiv.innerHTML = cloneHtml;
            
            // Ajouter le nouvel élément au conteneur
            container.appendChild(newDiv.firstElementChild);
            caracteristiqueIndex++;
        });

        // Gérer la suppression d'un item
        container.addEventListener('click', function(event) {
            if (event.target && event.target.classList.contains('remove-caracteristique')) {
                event.target.closest('.caracteristique-item').remove();
            }
        });
    }
});