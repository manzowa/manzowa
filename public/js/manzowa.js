import { sgjs } from "../lib/sgjs/js/sgjs.js";

// Initialisation de l'application SGJS module
(function() {
  //Action Menu
  (function () {
    let navToggler = document.querySelector(".mo-nav-toggler");
    let navMenu = navToggler?.dataset?.toggle;
    let togglerIcon = (action) => {
      action.classList.contains("lni-menu-hamburger-1")
        ? action.classList.replace("lni-menu-hamburger-1", "lni-xmark")
        : action.classList.replace("lni-xmark", "lni-menu-hamburger-1");
    };
    let togglerShow = (action) => {
      let menu = document.getElementById(action);
      if (menu != null || menu != undefined) {
        menu.classList.toggle("mo-show");
      }
    };
    if (navToggler != null || navToggler != undefined) {
      navToggler.addEventListener("click", function () {
        togglerIcon(this.querySelector(".mo-nav-toggler i"));
        togglerShow(navMenu);
      });
    }
  })();
  // Skills Module
  (function () {
    // Check ID app-skills
    if (document.getElementById("app-skills")) {
      // Check if sgjs is defined
      if (typeof sgjs !== "undefined") {
        sgjs()
          .option({
            selected: "app-skills",
            ladder: 10,
            frontColor: "#ffa801",
            Function: "progress",
          })
          .add({
            titre: "Stack technique",
            frames: [
              { name: "PHP", level: 8 },
              { name: "Symfony", level: 7 },
              { name: "MySQL", level: 8 },
              { name: "PostgreSQL", level: 6 },
              { name: "React", level: 6 },
              { name: "Wordpress", level: 8 },
              { name: "CI/CD", level: 8 },
              { name: "Go", level: 5 }
            ],
          })
          .animate();
      }
    }
  })();
})();

// (function() {
//     const form = document.forms['searched'];
//     if (form != undefined) {
//         form.addEventListener('submit', async function(e) {
//             e.preventDefault(); // empêche l'envoi réel
//             const valeur = form.elements['q'].value;
//             const url = `/api/v1/ecoles/${encodeURIComponent(valeur)}`
//             try {
//                 const response = await fetch(url);
//                 if (!response.ok) {
//                     throw new Error(`HTTP error! status: ${response.status}`);
//                 }
//                 const data = await response.json();
//                 // Check nested properties
//                 if (data.success && data.data?.schools) {
//                     showSuggestions(data.data.schools);  
//                 } else {
//                     console.warn("No schools found or API returned success: false");
//                 }
//             } catch (error) {
//                 console.error("Error:", error);
//             }
//         });
//     }
//     function showSuggestions(schools) {
//         const suggestionsList = document.getElementById("suggestions");
//         suggestionsList.innerHTML = "";

//         if (schools.length === 0) {
//             const li = document.createElement('li');
//             li.textContent = 'Aucune école trouvée.';
//             suggestionsList.appendChild(li);
//             return;
//         }
//         schools.forEach((school) => {
//             const li = document.createElement('li');
//             li.textContent = school.nom || "Nom inconnu";
//             suggestionsList.appendChild(li);
//         });
//     };
// })();