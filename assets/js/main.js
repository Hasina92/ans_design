// HEADER
window.addEventListener("scroll", function () {
  var header = document.querySelector("header");
  header.classList.toggle("sticky", window.scrollY > 0);
});

document.addEventListener("DOMContentLoaded", function () {
  const burgerMenu = document.getElementById("burgerMenu");
  const menu = document.querySelector(".nav-menu");
  burgerMenu.addEventListener("click", function () {
    menu.style.left = menu.style.left === "0px" ? "-80%" : "0px";
    burgerMenu.classList.toggle("active");
  });
});

// TABS-LINK
jQuery(document).ready(function ($) {
  function initTabs(group) {
    const $links = $(`.tabslink-${group} a`);
    const $contents = $(`.tabscontent-${group}`);

    // Ajouter classe animation
    $contents.addClass("tabs-content-anim");

    // Afficher le premier
    $contents.hide().removeClass("show");
    $contents.first().show().addClass("show");

    $links.removeClass("active").first().addClass("active");

    $links.on("click", function (e) {
      e.preventDefault();
      const target = $(this).attr("href");

      // Masquer tous les contenus
      $contents.removeClass("show").hide();

      // Afficher celui cliqué avec fondu
      $(target).fadeIn(200).addClass("show");

      // Lien actif
      $links.removeClass("active");
      $(this).addClass("active");
    });
  }

  [
    "produits",
    "realisations",
    "processus",
    "cart",
    "personal-information",
    "connexion",
  ].forEach(initTabs);
});

//TABS-LINK ETAPE
jQuery(document).ready(function ($) {
  const group = "etape";
  const $links = $(`.tabslink-${group} a`);
  const $contents = $(`.tabscontent-${group}`);

  // Objet pour stocker les choix de chaque étape
  const selections = {};

  // Initialisation : cacher tout sauf la première étape
  $contents.hide().first().show();
  $links.removeClass("active").first().addClass("active");

  // Gestion du clic sur une carte
  $contents.on("click", ".card-etape", function () {
    const $this = $(this);
    const $currentTab = $this.closest(".tabscontent-" + group);
    const currentId = $currentTab.attr("id");

    // Retirer la classe active des autres cartes
    $this.siblings().removeClass("active");
    $this.addClass("active");

    // Enregistrer la sélection
    selections[currentId] = $this.index();

    // ✅ Ajouter la coche verte sur l’onglet correspondant
    const $currentLink = $links.filter(`[href="#${currentId}"]`);
    $currentLink.addClass("completed");

    // Passer à l'étape suivante
    const currentIndex = $links.index($currentLink);
    const nextIndex = currentIndex + 1;

    if (nextIndex < $links.length) {
      const nextHref = $links.eq(nextIndex).attr("href");
      $contents.hide();
      $(nextHref).fadeIn(300);

      $links.removeClass("active");
      $links.eq(nextIndex).addClass("active");
    } else {
      console.log("Dernière étape atteinte ✅");
    }
  });

  // Quand on clique sur un onglet manuellement
  $links.on("click", function (e) {
    e.preventDefault();
    const target = $(this).attr("href");

    $contents.hide();
    $(target).fadeIn(300);

    $links.removeClass("active");
    $(this).addClass("active");

    // Restaurer la sélection précédente
    const selectedIndex = selections[target.replace("#", "")];
    if (selectedIndex !== undefined) {
      const $cards = $(target).find(".card-etape");
      $cards.removeClass("active");
      $cards.eq(selectedIndex).addClass("active");
    }
  });
});

//ACCORDEON
document.querySelectorAll(".accordeon-title").forEach((title) => {
  title.addEventListener("click", () => {
    const content = title.parentElement;
    const text = content.querySelector(".accordeon-text");
    const fleche = content.querySelector(".fleche");

    if (content.classList.contains("active")) {
      content.classList.remove("active");
      text.style.maxHeight = null;
      fleche.style.rotate = "0deg";
    } else {
      document.querySelectorAll(".accordeon-content").forEach((item) => {
        item.classList.remove("active");
        item.querySelector(".accordeon-text").style.maxHeight = null;
        fleche.style.rotate = "180deg";
      });

      content.classList.add("active");
      text.style.maxHeight = text.scrollHeight + "px";
    }
  });
});

// MINI-CART
function mini_cart() {
  const open_buttons = document.querySelectorAll(".open-button");
  const mini_cart = document.getElementById("overlay");
  const close_button = document.getElementById("close-button");
  const wrapper_mini_cart = document.getElementById("wrapper_mini_cart");

  if (!mini_cart || !close_button || !wrapper_mini_cart) return;

  open_buttons.forEach((btn) => {
    btn.addEventListener("click", () => {
      mini_cart.classList.add("active");

      setTimeout(() => {
        wrapper_mini_cart.classList.add("active");
      }, 500);
    });
  });

  // Bouton de fermeture
  close_button.addEventListener("click", () => {
    wrapper_mini_cart.classList.remove("active");

    setTimeout(() => {
      mini_cart.classList.remove("active");
    }, 500);
  });
}

mini_cart();

function checkCaracteristiquesCompletes() {
  const selections = document.querySelectorAll(".card-etape.active");
  const caracTabs = document.querySelectorAll(
    ".tabscontent-etape:not(.quantite)"
  );
  const quantite = document.querySelector("input[name='quantite']");

  // Vérifier chaque caractéristique
  if (selections.length < caracTabs.length) {
    alert("Veuillez sélectionner toutes les caractéristiques.");
    return false;
  }

  // Vérifier quantité
  if (!quantite || quantite.value <= 0) {
    alert("Veuillez renseigner une quantité valide.");
    return false;
  }

  return true;
}

//POP-UP SUIVI COMMANDE
// POP-UP HANDLER (avec validation optionnelle)
function popupHandler(
  openSelector,
  popupSelector,
  closeSelector,
  beforeOpen = null
) {
  const openBtns = document.querySelectorAll(openSelector); // 👈 ICI
  const popup = document.querySelector(popupSelector);
  const closeBtn = document.querySelector(closeSelector);

  if (!openBtns.length || !popup || !closeBtn) return;

  function closeAllPopups() {
    document.querySelectorAll(".popup.active").forEach((p) => {
      p.classList.remove("active");
    });
  }

  // 🔓 OUVERTURE (pour TOUS les boutons)
  openBtns.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      if (beforeOpen && beforeOpen() === false) {
        e.preventDefault();
        return;
      }

      closeAllPopups();
      popup.classList.add("active");
    });
  });

  // ❌ FERMETURE via bouton
  closeBtn.addEventListener("click", () => {
    popup.classList.remove("active");
  });

  // ❌ FERMETURE clic extérieur
  popup.addEventListener("click", (e) => {
    if (e.target === popup) {
      popup.classList.remove("active");
    }
  });
}

// 🔄 Appels réutilisables
popupHandler(
  "#open-popup-suivi",
  ".pop-up-suivi-commande",
  "#close-popup-suivi"
);

popupHandler(
  "#open-popup-detail",
  ".pop-up-detail-commande",
  "#close-popup-detail"
);

popupHandler(
  "#open-popup-devis",
  ".pop-up-devis-commande",
  "#close-popup-devis-commande"
);

// 🛒 AJOUT PANIER AVEC VALIDATION
popupHandler(
  "#open-popup-ajout-panier",
  ".pop-up-ajout-panier",
  "#close-popup-ajout-panier",
  checkCaracteristiquesCompletes
);

popupHandler(
  "#open-popup-reset-password",
  ".pop-up-reset-password",
  "#close-popup-reset-password"
);

popupHandler(
  "#open-popup-info-equipe",
  ".pop-up-info-equipe",
  "#close-popup-info-equipe"
);

popupHandler(
  "#open-popup-technologie",
  ".pop-up-technologie",
  "#close-popup-technologie"
);

popupHandler(
  "#open-popup-temoignages",
  ".pop-up-temoignages",
  "#close-popup-temoignages"
);

//SLICK PRODUITS
$(document).ready(function () {
  $(".produits-slick").slick({
    infinite: true,
    slidesToShow: 3,
    slidesToScroll: 2,
    variableWidth: true,
    arrows: true,
    dots: false,
    centerMode: true,
    prevArrow: $(".slick-prev-custom"),
    nextArrow: $(".slick-next-custom"),
  });
});

//SLICK EQUIPE
$(document).ready(function () {
  $(".container-equipe").slick({
    infinite: true,
    slidesToShow: 4,
    slidesToScroll: 1,
    // variableWidth: true,
    arrows: true,
    dots: false,
    centerMode: false,
    prevArrow: $(".slick-prev-custom-equipe"),
    nextArrow: $(".slick-next-custom-equipe"),
    responsive: [
      {
        breakpoint: 1024,
        settings: {
          slidesToShow: 2,
        },
      },
      {
        breakpoint: 768,
        settings: {
          slidesToShow: 1,
        },
      },
    ],
  });
});

//SLICK TEMOIGNAGES
$(document).ready(function () {
  $(".container-temoignages").slick({
    infinite: true,
    slidesToShow: 3,
    slidesToScroll: 1,
    variableWidth: true,
    arrows: true,
    dots: false,
    centerMode: true,
    prevArrow: $(".slick-prev-custom-temoignages"),
    nextArrow: $(".slick-next-custom-temoignages"),
  });
});

//SLICK IMAGE PASSION
$(document).ready(function () {
  $(".slick-image").slick({
    infinite: true,
    slidesToShow: 1,
    slidesToScroll: 1,
    arrows: false,
    dots: false,
    speed: 1000,
    autoplay: true,
    autoplaySpeed: 3000,
  });
});

//SLICK TABSLINK REALISATION
$(document).ready(function () {
  $(".tabslink-realisations").slick({
    infinite: false,
    slidesToShow: 4,
    slidesToScroll: 1,
    arrows: true,
    dots: false,
    variableWidth: true,
    autoplay: false,
    prevArrow: $(".slick-prev-custom"),
    nextArrow: $(".slick-next-custom"),
  });
});

//SLICK TABSLINK PROCESSUS
$(document).ready(function () {
  // INITIALISATION SLICK
  var $processus = $(".tabslink-processus");

  $processus.slick({
    infinite: false,
    slidesToShow: 4, // 4 étapes visibles
    slidesToScroll: 1,
    arrows: false,
    dots: false,
    speed: 700,
  });

  // CLIC SUR UNE ÉTAPE
  $(".tabslink-processus li").on("click", function (e) {
    e.preventDefault(); // empêche le scroll vers les ancres

    let index = $(this).index(); // position de l'étape cliquée

    $processus.slick("slickGoTo", index);
  });
});

//ACCORDEON COMMANDE
document.addEventListener("DOMContentLoaded", () => {
  const accordeonTitles = document.querySelectorAll(".accordeon-title-compte");

  accordeonTitles.forEach((title) => {
    title.addEventListener("click", () => {
      const card = title.closest(".accordeon-card-compte");
      const content = card.querySelector(".accordeon-content-compte"); // contenu déroulant
      const arrow = card.querySelector(".order-arrow img"); // icône flèche
      const isActive = card.classList.contains("active");

      // Ferme tous les autres accordéons
      document
        .querySelectorAll(".accordeon-card-compte.active")
        .forEach((activeCard) => {
          if (activeCard !== card) {
            activeCard.classList.remove("active");
            const activeContent = activeCard.querySelector(
              ".accordeon-content-compte"
            );
            const activeArrow = activeCard.querySelector(".order-arrow img");
            if (activeArrow) activeArrow.style.transform = "rotate(0deg)";
          }
        });

      // Toggle du card cliqué
      if (isActive) {
        card.classList.remove("active");
        if (arrow) arrow.style.transform = "rotate(0deg)";
      } else {
        card.classList.add("active");
        if (arrow) arrow.style.transform = "rotate(180deg)";
      }
    });
  });
});

//SCROLL-TOP
document.querySelectorAll('a[href^="."]').forEach((anchor) => {
  anchor.addEventListener("click", function (e) {
    e.preventDefault();

    const target = document.querySelector(this.getAttribute("href"));
    if (target) {
      target.scrollIntoView({
        behavior: "smooth",
        block: "start",
      });
    }
  });
});

// POP-UP DÉTAIL CATALOGUE
document.addEventListener("DOMContentLoaded", () => {
  const openButtons = document.querySelectorAll(".open-popup-detail-catalogue");
  const popup = document.querySelector(".pop-up-detail-catalogue");
  const closeButton = popup.querySelector(".close-popup-detail-catalogue");

  if (!popup || !openButtons.length) return;

  // Ouvrir la popup
  openButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
      popup.classList.add("active");
    });
  });

  // Fermer via la croix
  closeButton.addEventListener("click", () => {
    popup.classList.remove("active");
  });

  // Fermer en cliquant à l’extérieur du contenu
  popup.addEventListener("click", (e) => {
    if (e.target === popup) {
      popup.classList.remove("active");
    }
  });
});

// METHODE DE PAIEMENT
document.addEventListener("DOMContentLoaded", function () {
  const payment = document.getElementById("payment");
  const operators = document.getElementById("mobile-operators");
  const operatorSelect = document.getElementById("operateur");

  const telmaInput = document.getElementById("input-telma");
  const airtelInput = document.getElementById("input-airtel");
  const orangeInput = document.getElementById("input-orange");

  if (!payment || !operators || !operatorSelect) return;

  // Affiche les opérateurs si "mobile-money" est sélectionné
  payment.addEventListener("change", function () {
    if (this.value === "mobile-money") {
      operators.classList.remove("hidden");
    } else {
      operators.classList.add("hidden");
      operatorSelect.value = "";
      hideAllOperatorInputs();
    }
  });

  // Quand un opérateur est choisi
  operatorSelect.addEventListener("change", function () {
    hideAllOperatorInputs(); // cache tout avant d’afficher le bon champ

    switch (this.value) {
      case "telma":
        telmaInput.classList.remove("hidden");
        break;
      case "airtel":
        airtelInput.classList.remove("hidden");
        break;
      case "orange":
        orangeInput.classList.remove("hidden");
        break;
    }
  });

  // Fonction utilitaire pour cacher tous les champs
  function hideAllOperatorInputs() {
    telmaInput.classList.add("hidden");
    airtelInput.classList.add("hidden");
    orangeInput.classList.add("hidden");
  }
});

//CARD HISTORIQUE
const cards = document.querySelectorAll(".card-historique");
const observer = new IntersectionObserver(
  (entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add("visible");
        // Si tu veux que l’animation ne se joue qu'une fois :
        observer.unobserve(entry.target);
      }
    });
  },
  {
    threshold: 0.2, // visible à 20%
  }
);

cards.forEach((card) => observer.observe(card));

//ANIMATION LIGNE HISTORIQUE
document.addEventListener("DOMContentLoaded", () => {
  gsap.registerPlugin(ScrollTrigger);

  // Animation de la ligne
  const path = document.querySelector("#timeline path");
  const length = path.getTotalLength();

  path.style.strokeDasharray = length;
  path.style.strokeDashoffset = length;

  gsap.to(path, {
    strokeDashoffset: 0,
    ease: "none",
    scrollTrigger: {
      trigger: "#timeline",
      start: "top 80%",
      end: "bottom 20%",
      scrub: 1.5,
    },
  });

  // Animation des cercles
  const circles = document.querySelectorAll("#timeline circle");

  gsap.set(circles, { opacity: 0, scale: 0.4, transformOrigin: "center" });

  gsap.to(circles, {
    opacity: 1,
    scale: 1,
    duration: 0.6,
    stagger: 0.3, // décalage entre chaque cercle
    ease: "power1.out",
    scrollTrigger: {
      trigger: "#timeline",
      start: "top 70%",
      end: "bottom 20%",
      scrub: 1.5,
    },
  });
});

//COMPTEUR
gsap.registerPlugin(ScrollTrigger);

function animateCounter(element) {
  let finalValue = parseInt(element.textContent.replace(/\D/g, ""));
  let hasPlus = element.textContent.includes("+");
  let hasSpace = element.textContent.includes(" ");

  gsap.fromTo(
    element,
    { innerText: 0 },
    {
      innerText: finalValue,
      duration: 2,
      ease: "power1.out",
      scrollTrigger: {
        trigger: element, // déclenchement quand le compteur entre dans la vue
        start: "top 90%", // quand le haut du compteur atteint 90% de la fenêtre
        toggleActions: "play none none none", // s'active une seule fois
      },
      snap: { innerText: 1 },
      onUpdate: function () {
        let val = Math.floor(element.innerText);
        if (hasSpace) {
          val = val.toLocaleString("fr-FR");
        }
        if (hasPlus) {
          val += "+";
        }
        element.textContent = val;
      },
    }
  );
}

// On sélectionne tous les compteurs
document.querySelectorAll(".nombre").forEach((num) => {
  animateCounter(num);
});

//SEARCH TRIGGER

// === Variables ===
const searchIcon = document.getElementById("search-icon");
const searchContainer = document.getElementById("search-container");
const searchInput = document.getElementById("search-produit");
const resultsBox = document.getElementById("resultats-produit");

// === Afficher / Masquer la barre de recherche au clic sur l'icône ===
searchIcon.addEventListener("click", function () {
  if (searchContainer.style.display === "block") {
    searchContainer.style.display = "none";
  } else {
    searchContainer.style.display = "block";
    searchInput.focus(); // met le focus sur le champ
  }
});

// === Recherche en direct (AJAX) ===
searchInput.addEventListener("keyup", function () {
  let query = this.value.trim();

  // Si le champ est vide, on vide les résultats
  if (query.length === 0) {
    resultsBox.innerHTML = "";
    return;
  }

  // Requête AJAX
  fetch("search-produit.php?q=" + encodeURIComponent(query))
    .then((res) => res.text())
    .then((html) => {
      resultsBox.innerHTML = html;
    });
});

// === Redirection vers etape.php au clic sur un résultat ===
document.addEventListener("click", function (e) {
  const item = e.target.closest(".result-produit-item");
  if (item) {
    let id = item.getAttribute("data-id");
    window.location.href = "etape.php?id=" + id;
  }
});

// === Masquer la recherche si clic en dehors de l'icône ou du container ===
document.addEventListener("click", function (e) {
  if (
    !e.target.closest("#search-icon") &&
    !e.target.closest("#search-container")
  ) {
    searchContainer.style.display = "none";
  }
});

//VOIR PLUS PACK LIST
document.querySelectorAll(".voir-plus").forEach((btn) => {
  btn.addEventListener("click", function () {
    const list = this.previousElementSibling;

    list.classList.toggle("open");
    this.textContent = list.classList.contains("open")
      ? "Voir moins"
      : "Voir plus";
  });
});

//VOIR PLUS REALISATION
document.querySelectorAll(".voir-plus-btn").forEach((btn) => {
  btn.addEventListener("click", () => {
    const container = btn.closest(".tabscontent-realisations");
    container.querySelectorAll(".card-realisation").forEach((card) => {
      card.style.display = "flex";
    });
    btn.style.display = "none";
  });
});

//RESET PASSWORD
document
  .getElementById("resetPassword")
  .addEventListener("submit", function (e) {
    e.preventDefault();
  });
