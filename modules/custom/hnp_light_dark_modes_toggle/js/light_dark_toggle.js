// /**
//  * @file modules/custom/hnp_light_dark_modes_toggle/js/light_dark_toggle.js
//  */

// (function ($, Drupal, drupalSettings) {
//   const input = $(".switch.light-dark-toggle").find(
//     'input[type="checkbox"]'
//   )[0];

//   let currentMode;
//   let targetMode;

//   console.log(drupalSettings.current_theme_mode);
//   currentMode = drupalSettings.current_theme_mode;
//   if (drupalSettings.current_theme_mode === "light") {
//     $(input).attr("checked", true);
//   } else {
//     $(input).attr("checked", false);
//   }

//   window.onresize = () => {
//     // $medium-screen-max // <= 991px
//     const styles = getComputedStyle(document.body);
//     const dark = styles.getPropertyValue("--background-color-dark");
//     const light = styles.getPropertyValue("--background-color-light");
//     const children = $("#masthead").children();
//     if (window.innerWidth < 991) {
//       for (var j = 0; j < children.length; j++) {
//         const each = $(children[j]);
//         $(each).css({
//           cssText: `background-color: ${dark} !important`,
//         });
//       }
//     } else {
//       for (var j = 0; j < children.length; j++) {
//         const each = $(children[j]);
//         $(each).css({
//           cssText: `background-color: ${
//             targetMode === "light" ? light : dark
//           } !important`,
//         });
//       }
//     }
//   };

//   const main = () => {
//     // currentMode = input.checked;
//     // targetMode = currentMode ? "light" : "dark";
//     const styles = getComputedStyle(document.body);
//     const dark = styles.getPropertyValue("--background-color-dark");
//     const light = styles.getPropertyValue("--background-color-light");
//     let cards = $(".category-card").find("a");
//     for (var i = 0; i < cards.length; i++) {
//       let color = $(cards[i]).css("color");
//       console.log(color);
//       if (!currentMode) {
//         color = light;
//       }

//       $(cards[i]).on("mouseover", (event) => {
//         $(event.currentTarget).css("color", "white");
//       });

//       $(cards[i]).on("mouseout", (event) => {
//         $(event.currentTarget).css(
//           "color",
//           currentMode === "light" ? "black" : "white"
//         );
//         console.log(currentMode);
//       });
//     }
//   };

//   main(currentMode);

//   Drupal.behaviors.toggleLightDarkModes = {
//     attach: function (context, settings) {
//       if (context === document) {
//         $(input).on("change", (event) => {
//           currentMode = input.checked;
//           targetMode = currentMode ? "light" : "dark";
//           const endpoint = `/hnp-light-dark-modes/toggle-to/${targetMode}`;
//           console.log(endpoint);

//           // BACKGROUND COLOR
//           const styles = getComputedStyle(document.body);
//           const dark = styles.getPropertyValue("--background-color-dark");
//           const light = styles.getPropertyValue("--background-color-light");
//           const array = [
//             // "#block-sitebranding",
//             "#block-de-theme-breadcrumbs",
//             ".responsive-menu-page-wrapper",
//             // "#masthead",
//           ];
//           for (var i = 0; i < array.length; i++) {
//             if (array[i] === "#masthead") {
//               const children = $(array[i]).children();
//               for (var j = 0; j < children.length; j++) {
//                 const each = $(children[j]);
//                 $(each).css({
//                   cssText: `background-color: ${
//                     targetMode === "light" ? light : dark
//                   } !important`,
//                 });
//               }
//             } else {
//               const each = $(array[i])[0];
//               $(each).css(
//                 "background-color",
//                 targetMode === "light" ? light : dark
//               );
//             }
//           }

//           // TEXT COLOR
//           const black = styles.getPropertyValue("--text-color-dark");
//           const white = styles.getPropertyValue("--text-color-light");
//           $("html").css("color", targetMode === "light" ? black : white);
//           // $("#block-uppermenublock")
//           //   .find("a")
//           //   .css("color", targetMode === "light" ? black : white);
//           // $("#masthead-primary")
//           //   .find("a")
//           //   .css("color", targetMode === "light" ? black : white);
//           // $("#block-sitebranding")
//           //   .find("a")
//           //   .css({
//           //     cssText: `color: ${
//           //       targetMode === "light" ? black : white
//           //     } !important`,
//           //   });
//           // $("#block-sitebranding")
//           //   .find(".site-slogan")
//           //   .css({
//           //     cssText: `color: ${
//           //       targetMode === "light" ? black : white
//           //     } !important`,
//           //   });
//           $("#horizontal-menu")
//             .find(".menu.sub-nav")
//             .find("a")
//             .css("color", white);
//           $(".responsive-menu-toggle-icon").css(
//             "color",
//             targetMode === "light" ? white : black
//           );
//           $(".page-title").css("color", targetMode === "light" ? black : white);
//           $(".breadcrumb")
//             .find("a")
//             .css("color", targetMode === "light" ? black : white);
//           $(".category-card")
//             .find("a")
//             .css("color", targetMode === "light" ? black : white);

//           $.ajax({
//             url: endpoint,
//             type: "GET",
//             success: (results) => {
//               console.log(results);
//               currentMode = input.checked ? "light" : "dark";
//               main(currentMode);
//             },
//             failure: (error) => {
//               console.log(error);
//             },
//           });
//         });
//       }
//     },
//   };
// })(jQuery, Drupal, drupalSettings);
