// HEADER
window.addEventListener("scroll", function () {
  var header = document.querySelector("header");
  header.classList.toggle("sticky", window.scrollY > 0);
});

//TABS-LINK
jQuery(document).ready(function ($) {
  function initTabs(group) {
    const $links = $(`.tabslink-${group} a`);
    const $contents = $(`.tabscontent-${group}`);

    $contents.hide().first().show();
    $links.removeClass("active").first().addClass("active");

    $links.on("click", function (e) {
      e.preventDefault();
      const target = $(this).attr("href");

      $contents.hide();
      $(target).show();

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

//POP-UP SUIVI COMMANDE
function popupHandler(openSelector, popupSelector, closeSelector) {
  const openBtn = document.querySelector(openSelector);
  const popup = document.querySelector(popupSelector);
  const closeBtn = document.querySelector(closeSelector);

  if (!openBtn || !popup || !closeBtn) return;

  // Fonction pour fermer toutes les pop-ups ouvertes
  function closeAllPopups() {
    document.querySelectorAll(".popup.active").forEach((p) => {
      p.classList.remove("active");
    });
  }

  // Ouvrir la pop-up
  openBtn.addEventListener("click", () => {
    closeAllPopups(); // 🔹 Ferme les autres avant d'ouvrir la nouvelle
    popup.classList.add("active");
  });

  // Fermer via le bouton
  closeBtn.addEventListener("click", () => {
    popup.classList.remove("active");
  });

  // Fermer en cliquant en dehors du contenu
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
  "#open-popup-ajout-panier",
  ".pop-up-ajout-panier",
  "#close-popup-ajout-panier"
);

//SLICK
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
