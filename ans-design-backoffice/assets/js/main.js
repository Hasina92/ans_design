document.addEventListener("DOMContentLoaded", function () {
  // --- GESTION DU POPUP DE DÉTAILS ---
  const modal = document.getElementById("detailsModal");
  if (modal) {
    const closeModalBtn = document.getElementById("closeModal");
    const modalBody = document.getElementById("modalBody");

    function openModal() {
      modal.style.display = "flex";
    }
    function closeModal() {
      modal.style.display = "none";
    }

    closeModalBtn.addEventListener("click", closeModal);
    modal.addEventListener("click", (event) => {
      if (event.target === modal) closeModal();
    });

    document.body.addEventListener("click", function (event) {
      // --- OUVERTURE DU POPUP ---
      if (event.target.classList.contains("details-btn")) {
        const commandeId = event.target.dataset.commandeId;
        modalBody.innerHTML = '<div class="modal-loading">Chargement...</div>';
        openModal();

        fetch(`ajax/get_order_details.php?id=${commandeId}`)
          .then((response) => {
            if (!response.ok)
              throw new Error("Erreur réseau ou commande non trouvée.");
            return response.json();
          })
          .then((data) => {
            // --- FONCTION HELPER : Génère la liste détaillée des articles ---
            const renderArticlesList = (articles) => {
              if (!articles || articles.length === 0)
                return "<em>Aucun article listé</em>";

              // Conteneur avec scroll pour le responsive (évite que le modal soit trop long sur mobile)
              let html =
                '<div class="articles-scroll-box" style="margin-top:5px; max-height:200px; overflow-y:auto; border:1px solid #eee; background:#fafafa; padding:8px; border-radius:4px;">';

              articles.forEach((art) => {
                html += `
                        <div style="margin-bottom:8px; padding-bottom:8px; border-bottom:1px dashed #ccc; font-size:0.95em;">
                            <div style="font-weight:bold; color:#333;">
                                ${art.description} <span style="color:#e67e22;">(x${art.quantite})</span>
                            </div>`;

                // Affichage des options (caractéristiques)
                if (art.options && art.options.length > 0) {
                  html += `<ul style="margin:2px 0 0 15px; padding:0; list-style:none; font-size:0.85em; color:#666;">`;
                  art.options.forEach((opt) => {
                    html += `<li>• ${opt.caracteristique_nom} : <strong>${opt.valeur_choisie}</strong></li>`;
                  });
                  html += `</ul>`;
                } else {
                  html += `<small style="color:#999; margin-left:15px;">Standard</small>`;
                }

                html += `</div>`;
              });

              html += "</div>";
              return html;
            };
            // -------------------------------------------------------------

            const contentHtml = `
                    <div class="modal-header">
                        <h2 class="modal-title">Détails de la Commande #${
                          data.numero_commande || data.id
                        }</h2>
                    </div>
                    <div class="modal-body-columns">
                        <!-- COLONNE DE GAUCHE -->
                        <div class="modal-column-left">
                            <div class="modal-panel">
                                <h4 class="modal-subtitle">Infos Clés</h4>
                                <div class="info-grid">
                                    <span>Client : </span> <strong class="name">${
                                      data.prenom
                                    } ${data.nom}</strong><br/>
                                    <span>Société : </span> <strong>${
                                      data.societe || "Particulier"
                                    }</strong><br/>
                                    
                                    <!-- MODIFICATION ICI : Liste détaillée au lieu du résumé -->
                                    <div style="grid-column: span 2; margin-top:5px;">
                                        <span style="color:#555;">Détails Articles :</span>
                                        ${renderArticlesList(
                                          data.articles_detailed_list
                                        )}
                                    </div>
                                    
                                    <div class="total-info-grid" style="margin-top:10px;">
                                        <span>Total : </span> 
                                        <strong>${parseFloat(
                                          data.total_ttc
                                        ).toLocaleString("fr-FR")} AR</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-panel">
                                <h4 class="modal-subtitle">Gestion du Statut</h4>
                                <div class="info-grid">
                                    <label for="newStatusSelect">Statut de la Commande</label><br>
                                    <select id="newStatusSelect" class="modal-input">
                                        <option value="En validation" ${
                                          data.statut === "En validation"
                                            ? "selected"
                                            : ""
                                        }>En validation</option>
                                        <option value="En production" ${
                                          data.statut === "En production"
                                            ? "selected"
                                            : ""
                                        }>En production</option>
                                        <option value="En attente devis" ${
                                          data.statut === "En attente devis"
                                            ? "selected"
                                            : ""
                                        }>En attente devis</option>
                                        <option value="Livrée" ${
                                          data.statut === "Livrée"
                                            ? "selected"
                                            : ""
                                        }>Livrée</option>
                                        <option value="Annulé" ${
                                          data.statut === "Annulé"
                                            ? "selected"
                                            : ""
                                        }>Annulé</option>
                                        <option value="Avis à valider" ${
                                          data.statut === "Avis à valider"
                                            ? "selected"
                                            : ""
                                        }>Avis à valider</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="modal-panel">
                                <h4 class="modal-subtitle">Notes & Retour Client</h4>
                                <textarea id="avisClient" class="modal-textarea" placeholder="Entrer ici l'avis final du client...">${
                                  data.avis_client || ""
                                }</textarea>
                                <div class="modal-checkbox-wrapper">
                                    <input type="checkbox" id="publierAvis" ${
                                      data.publier_avis ? "checked" : ""
                                    }>
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
                                        </div>
                                    </div>
                                    <div class="timeline-item">
                                        <div class="timeline-icon"></div>
                                        <div class="timeline-content">
                                            <strong>Paiement</strong><br>
                                            <small>
                                                Méthode :
                                                <strong>${(function () {
                                                  switch (
                                                    data.methode_paiement
                                                  ) {
                                                    case "mobile-money":
                                                      return "Mobile Money";
                                                    case "livraison":
                                                      return "Paiement à la livraison";
                                                    case "recuperation":
                                                      return "Point de vente";
                                                    default:
                                                      return (
                                                        data.methode_paiement ||
                                                        "Non définie"
                                                      );
                                                  }
                                                })()}</strong>
                                            </small>
                                            ${
                                              data.details_paiement
                                                ? `<br><small>Détails : ${data.details_paiement}</small>`
                                                : ""
                                            }
                                        </div>
                                    </div>

                                    <div class="timeline-item">
                                        <div class="timeline-icon"></div>
                                        <div class="timeline-content">
                                            <strong>Préparation fichier PAO</strong>
                                        </div>
                                    </div>
                                    
                                    <div class="timeline-item">
                                        <div class="timeline-content">
                                            <p style="margin:5px 0;"><strong>Adresse de livraison : </strong>${
                                              data.adresse_livraison ||
                                              "Non définie"
                                            }</p>
                                            <p style="margin:5px 0;"><strong>Code postal : </strong>${
                                              data.code_postal || "Non définie"
                                            }</p>
                                            <p style="margin:5px 0;"><strong>Ville : </strong>${
                                              data.ville || "Non définie"
                                            }
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="modal-panel">
                                <h4 class="modal-subtitle">Suivi du Retour / SAV</h4>
                                <textarea id="notesSav" class="modal-textarea" placeholder="Note interne sur les problèmes de livraison, demandes spécifique, etc.">${
                                  data.notes_sav || ""
                                }</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <p id="updateStatusMessage"></p>
                        <button id="saveOrderDetailsBtn" class="modal-save-btn" data-commande-id="${
                          data.id
                        }">Valider le formulaire</button>
                    </div>
                `;
            modalBody.innerHTML = contentHtml;
          })
          .catch((error) => {
            modalBody.innerHTML = `<p style="color:red; text-align:center; padding:20px;">${error.message}</p>`;
          });
      }

      if (event.target.id === "saveOrderDetailsBtn") {
        const button = event.target;
        const commandeId = button.dataset.commandeId;
        const messageEl = document.getElementById("updateStatusMessage");

        const formData = {
          commande_id: commandeId,
          statut: document.getElementById("newStatusSelect").value,
          avis_client: document.getElementById("avisClient").value,
          notes_sav: document.getElementById("notesSav").value,
          publier_avis: document.getElementById("publierAvis").checked,
        };

        button.disabled = true;
        messageEl.textContent = "Sauvegarde en cours...";
        messageEl.style.color = "#333";

        fetch("ajax/update_full_order.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(formData),
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              messageEl.style.color = "green";
              messageEl.textContent = data.message;
              setTimeout(() => {
                closeModal();
                window.location.reload();
              }, 1500);
            } else {
              throw new Error(data.message);
            }
          })
          .catch((error) => {
            messageEl.style.color = "red";
            messageEl.textContent = `Erreur : ${error.message}`;
          })
          .finally(() => {
            button.disabled = false;
          });
      }
    });
  }

  // --- GESTION DE LA RECHERCHE DE CLIENTS ---
  const clientSearchInput = document.getElementById("clientSearchInput");
  if (clientSearchInput) {
    const clientListContainer = document.getElementById("clientListContainer");

    clientSearchInput.addEventListener("keyup", function () {
      const query = this.value;

      fetch(`ajax/search_clients.php?query=${encodeURIComponent(query)}`)
        .then((response) => response.json())
        .then((clients) => {
          let html = "";
          if (clients.length > 0) {
            clients.forEach((client) => {
              html += `
                                <a href="clients_commandes.php?client_id=${
                                  client.id
                                }" style="display:block; padding:15px; border:1px solid #ddd; border-radius:5px; margin-bottom:10px;">
                                    <strong>${client.prenom} ${
                client.nom
              }</strong>
                                    <br>
                                    <small>${client.societe || ""}</small>
                                </a>
                            `;
            });
          } else {
            html = "<p>Aucun client trouvé.</p>";
          }
          clientListContainer.innerHTML = html;
        })
        .catch((error) => {
          clientListContainer.innerHTML = "<p>Erreur lors de la recherche.</p>";
        });
    });
  }
});

document.addEventListener("DOMContentLoaded", function () {
  const addBtn = document.getElementById("add-caracteristique");
  const container = document.getElementById("caracteristiques-container");
  const template = document.getElementById("caracteristique-template");

  if (addBtn && container && template) {
    let caracteristiqueIndex = container.children.length;

    addBtn.addEventListener("click", function () {
      // Cloner le template
      const cloneHtml = template.innerHTML.replace(
        /__INDEX__/g,
        caracteristiqueIndex
      );
      const newDiv = document.createElement("div");
      newDiv.innerHTML = cloneHtml;

      // Ajouter le nouvel élément au conteneur
      container.appendChild(newDiv.firstElementChild);
      caracteristiqueIndex++;
    });

    // Gérer la suppression d'un item
    container.addEventListener("click", function (event) {
      if (
        event.target &&
        event.target.classList.contains("remove-caracteristique")
      ) {
        event.target.closest(".caracteristique-item").remove();
      }
    });
  }
});
