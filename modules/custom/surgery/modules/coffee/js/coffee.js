/**
 * @file
 * JavaScript file for the Coffee module.
 */

(function ($, Drupal, drupalSettings, DrupalCoffee) {
  "use strict";

  const MAX_MEMORY_SIZE = 10;
  const STORAGE_KEY = "surgery.coffee";

  const SCOPE = "surgery.coffee";
  const COFFEE_RESULTS_ID = "coffee-results";
  const COFFEE_FORM_ID = "coffee-form";
  const COFFEE_BG_ID = "coffee-bg";
  const COFFEE_QUERY_ID = "coffee-q";
  const COFFEE_HELP_TEXT_ID = "coffee-help-text";
  const COFFEE_DYNAMIC_RESULT_ID = "coffee-dynamic-result";
  const COFFEE_MEMORY_SUGGESTION_WRAPPER = "coffee-memory-suggestion-wrapper";
  const COFFEE_MEMORY_SUGGESTION_ID = "coffee-memory-suggestion";
  const COFFEE_SPINNER_OVERLAY_ID = "spinner-overlay";
  // @see docroot/modules/custom/surgery/surgery.module file.
  const availableCommands =
    drupalSettings.surgery.coffee.availableCommands.join(", ");
  const INSPECT_COMMAND = "inspect";
  const FIELD_VALUE_COMMAND = "field-value";
  const LIST_FIELDS_COMMAND = "list-fields";

  // Remap the filter functions for autocomplete to recognise the
  // extra value "command".
  var proto = $.ui.autocomplete.prototype;
  var initSource = proto._initSource;

  function thisLine() {
    const e = new Error();
    const regex = /\((.*):(\d+):(\d+)\)$/
    const match = regex.exec(e.stack.split("\n")[2]);
    return match && Array.isArray(match) ? { filepath: match[1], line: match[2], column: match[3] } : { filepath: null, line: null, column: null };
  }

  function filter(array, term) {
    var matcher = new RegExp($.ui.autocomplete.escapeRegex(term), "i");
    return $.grep(array, function (value) {
      return (
        matcher.test(value.command) ||
        matcher.test(value.label) ||
        matcher.test(value.value)
      );
    });
  }

  $.extend(proto, {
    _initSource: function () {
      if ($.isArray(this.options.source)) {
        this.source = function (request, response) {
          response(filter(this.options.source, request.term));
        };
      } else {
        initSource.call(this);
      }
    },
  });

  /**
   * Coffee module namespace.
   *
   * @namespace
   *
   * @todo put this in Drupal.coffee to expose it.
   */
  DrupalCoffee = DrupalCoffee || {};

  /**
   * Attaches coffee module behaviors.
   *
   * Initializes DOM elements coffee module needs to display the search.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attach coffee functionality to the page.
   *
   * @todo get most of it out of the behavior in dedicated functions.
   */
  Drupal.behaviors.coffee = {
    attach: function (context) {
      const body = once("coffee", "body", context);
      body.forEach((body) => {
        var $body = $(body);
        DrupalCoffee.bg.appendTo($body).hide();
        DrupalCoffee.wrapper.appendTo("body").addClass("hide-form");
        DrupalCoffee.form
          .append(DrupalCoffee.label)
          .append(DrupalCoffee.field)
          .append(DrupalCoffee.helpText)
          .append(DrupalCoffee.dynamicResult)
          .append(DrupalCoffee.memorySuggestion)
          // .append(DrupalCoffee.spinnerOverlay)
          .append(DrupalCoffee.results)
          .wrapInner('<div id="coffee-form-inner" />')
          .appendTo(DrupalCoffee.wrapper);

        // Append the spinner to the body to make it centered.
        $body.append(DrupalCoffee.spinnerOverlay);

        DrupalCoffee.memorySuggestion.hide();

        // Load autocomplete data set, consider implementing
        // caching with local storage.
        DrupalCoffee.dataset = [];
        DrupalCoffee.isItemSelected = false;

        var autocomplete_data_element = "ui-autocomplete";

        $.when(
          $.ajax({
            url: drupalSettings.coffee.dataPath
              ? drupalSettings.coffee.dataPath
              : Drupal.url("admin/coffee/get-data"),
            dataType: "json",
          }),
          // Extends the results from Coffee with our results.
          $.ajax({
            url: drupalSettings.surgery.coffee.dataPath
              ? drupalSettings.surgery.coffee.dataPath
              : Drupal.url("admin/surgery/coffee/get-data"),
            dataType: "json",
          })
        ).then(
          // Success
          (a1, a2) => {
            const data = a1[0].concat(a2[0]);

            DrupalCoffee.dataset = data;

            // Apply autocomplete plugin on show.
            var $autocomplete = $(DrupalCoffee.field).autocomplete({
              source: DrupalCoffee.dataset,
              focus: function (event, ui) {
                // Prevents replacing the value of the input field.
                DrupalCoffee.isItemSelected = true;
                event.preventDefault();
              },
              change: function (event, ui) {
                DrupalCoffee.isItemSelected = false;
              },
              select: function (event, ui) {
                DrupalCoffee.redirect(
                  ui.item.value,
                  event.metaKey || event.ctrlKey
                );
                event.preventDefault();
                return false;
              },
              delay: 0,
              appendTo: DrupalCoffee.results,
            });

            // @see https://api.jqueryui.com/autocomplete/#method-_renderItem
            $autocomplete.data(autocomplete_data_element)._renderItem =
              function (ul, item) {
                // Strip the basePath when displaying the link description.
                var description = item.value;

                // Results from a custom command.
                // Normally, a result is a link. However, for a custom command, its results should not be links.
                // Instead, we want to stay on the same page and interact with Coffee.
                if ('is_custom_command' in item && typeof (item.is_custom_command) === "boolean" && item.is_custom_command) {
                  return (
                    $("<li></li>")
                      // @see https://api.jquery.com/data/
                      // Using the data() method to update data does not affect attributes in the DOM.
                      // To set a data-* attribute value, use attr.
                      .data("item.autocomplete", item)
                      .append(
                        `<a href="#">` +
                        item.label +
                        '<small class="description">' +
                        description +
                        "</small></a>"
                      )
                      .appendTo(ul)
                      .on('click', (event) => {
                        event.preventDefault();
                        event.stopPropagation();
                        // Sets the value & trigger a key-up to simulate keyboard input.
                        $(`#${COFFEE_QUERY_ID}`).val(item.value);
                        $(`#${COFFEE_QUERY_ID}`).trigger('keyup');
                      })
                  );
                }

                return (
                  $("<li></li>")
                    // @see https://api.jquery.com/data/
                    // Using the data() method to update data does not affect attributes in the DOM.
                    // To set a data-* attribute value, use attr.
                    .data("item.autocomplete", item)
                    .append(
                      `<a href="${description}">` +
                      item.label +
                      '<small class="description">' +
                      description +
                      "</small></a>"
                    )
                    .appendTo(ul)
                );
              }

            // We want to limit the number of results.
            // @see https://api.jqueryui.com/autocomplete/#method-_renderMenu
            $(DrupalCoffee.field).data(autocomplete_data_element)._renderMenu =
              function (ul, items) {
                var self = this;
                items = items.slice(0, drupalSettings.coffee.maxResults);
                $.each(items, function (index, item) {
                  self._renderItemData(ul, item);
                });
              };

            DrupalCoffee.form.keydown(function (event) {
              if (event.keyCode === 13) {
                var openInNewWindow = event.metaKey || event.ctrlKey;
                if (!DrupalCoffee.isItemSelected) {
                  var $firstItem = $(DrupalCoffee.results)
                    .find("li:first")
                    .data("item.autocomplete");
                  if (typeof $firstItem === "object") {
                    DrupalCoffee.redirect($firstItem.value, openInNewWindow);
                    event.preventDefault();
                  }
                }
              }
            });

            // Loads dynamic results.
            let timer = null;
            DrupalCoffee.form.keyup(function (event) {
              let query = $(`#${COFFEE_QUERY_ID}`).val();
              if (query === "") {
                $(`#${COFFEE_RESULTS_ID}`).show();
                $(`#${COFFEE_HELP_TEXT_ID}`).show();
                $(`#${COFFEE_DYNAMIC_RESULT_ID}`).hide();
                DrupalCoffee.display_query_history();
              } else {
                query = query.toLowerCase();
                $(`#${COFFEE_MEMORY_SUGGESTION_WRAPPER}`).hide();
                $(`#${COFFEE_HELP_TEXT_ID}`).hide();
                clearTimeout(timer);
                timer = setTimeout(() => {
                  // `inspect` command.
                  if (query.includes(INSPECT_COMMAND)) {
                    const regex = new RegExp(`${INSPECT_COMMAND} \\d+`, "ig");
                    if (regex.exec(query)) {
                      $.ajax({
                        url: Drupal.url(
                          `admin/surgery/coffee/get-data/${query}`
                        ),
                        data: {
                          scope: SCOPE,
                        },
                        success: (result) => {
                          if (result.data && result.data.status) {
                            const text = `${result.data.message} <br/> ${result.data.label} ${result.data.bundle}`;
                            $(`#${COFFEE_DYNAMIC_RESULT_ID}`).html(text);
                          } else {
                            $(`#${COFFEE_DYNAMIC_RESULT_ID}`).html(
                              "<p>We fail to fetch node. Perhaps it does not exist.</p>"
                            );
                          }
                          $(`#${COFFEE_DYNAMIC_RESULT_ID}`).show();
                          $(`#${COFFEE_HELP_TEXT_ID}`).hide();
                          // Update the history of queries/searches.
                          DrupalCoffee.update_query_history(query);
                        },
                      }).done(() => {
                        setTimeout(function () {
                          $(`#${COFFEE_SPINNER_OVERLAY_ID}`).fadeOut(300);
                        }, 300);
                      });
                    } else {
                      $(`#${COFFEE_DYNAMIC_RESULT_ID}`).html(
                        "<p>We expect a number for node ID.</p><p>We expect a format of 'inspect NID'.</p>"
                      );
                      $(`#${COFFEE_DYNAMIC_RESULT_ID}`).show();
                    }
                  }
                  // `field-value` command.
                  else if (query.includes(FIELD_VALUE_COMMAND)) {
                    const regex = new RegExp(
                      `${FIELD_VALUE_COMMAND} \\d+ \\w+`,
                      "ig"
                    );
                    // Query all field values with asterisk (*).
                    const regexAll = new RegExp(
                      `${FIELD_VALUE_COMMAND} \\d+ *`,
                      "ig"
                    );
                    if (regex.exec(query) || regexAll.exec(query)) {
                      $.ajax({
                        url: Drupal.url(
                          `admin/surgery/coffee/get-data/${query}`
                        ),
                        data: {
                          scope: SCOPE,
                        },
                        success: (result) => {
                          if (result.data && result.data.value) {
                            const text = `${result.data.message} <br/> ${result.data.value}`;
                            $(`#${COFFEE_DYNAMIC_RESULT_ID}`).html(text);
                          } else {
                            $(`#${COFFEE_DYNAMIC_RESULT_ID}`).html(
                              "<p>We fail to fetch results. Something went wrong.</p><p>We expect a format of 'field-value NID FIELD_MACHINE_NAME'.</p>"
                            );
                          }
                          $(`#${COFFEE_DYNAMIC_RESULT_ID}`).show();
                          $(`#${COFFEE_HELP_TEXT_ID}`).hide();
                          // Update the history of queries/searches.
                          DrupalCoffee.update_query_history(query);
                        },
                      }).done(() => {
                        setTimeout(function () {
                          $(`#${COFFEE_SPINNER_OVERLAY_ID}`).fadeOut(300);
                        }, 300);
                      });
                    } else {
                      $(`#${COFFEE_DYNAMIC_RESULT_ID}`).html(
                        "<p>We expect a format of 'field-value NID FIELD_MACHINE_NAME'.</p>"
                      );
                      $(`#${COFFEE_DYNAMIC_RESULT_ID}`).show();
                    }
                  }
                  // `list-fields` command.
                  else if (query.includes(LIST_FIELDS_COMMAND)) {
                    const regex = new RegExp(
                      `${LIST_FIELDS_COMMAND} \\w+`,
                      "ig"
                    );
                    if (regex.exec(query)) {
                      $.ajax({
                        url: Drupal.url(
                          `admin/surgery/coffee/get-data/${query}`
                        ),
                        data: {
                          scope: SCOPE,
                        },
                        success: (result) => {
                          if (result.data && result.data.value) {
                            const text = `${result.data.message} <br/> ${result.data.value}`;
                            $(`#${COFFEE_DYNAMIC_RESULT_ID}`).html(text);
                          } else {
                            $(`#${COFFEE_DYNAMIC_RESULT_ID}`).html(
                              "<p>We fail to fetch results. Something went wrong.</p><p>We expect a format of 'list-fields NID|BUNDLE'.</p>"
                            );
                          }
                          //
                          $(".list-fields .coffee-field").on(
                            "click",
                            (event) => {
                              const text = $(event.target).text();
                              if (/\d/.test(query)) {
                                const _query = `${FIELD_VALUE_COMMAND} 36391 ${text}`;
                                $.ajax({
                                  url: Drupal.url(
                                    `admin/surgery/coffee/get-data/${_query}`
                                  ),
                                  data: {
                                    scope: SCOPE,
                                  },
                                  success: (result) => {
                                    if (result.data && result.data.value) {
                                      const text = `${result.data.message} <br/> ${result.data.value}`;
                                      // $(`#${COFFEE_DYNAMIC_RESULT_ID}`).html(text);
                                      $(".list-fields--click-on--value").html(
                                        text
                                      );
                                      $(".list-fields").scrollTop(0);
                                    } else {
                                      $(`#${COFFEE_DYNAMIC_RESULT_ID}`).html(
                                        "<p>We fail to fetch results. Something went wrong.</p><p>We expect a format of 'field-value NID FIELD_MACHINE_NAME'.</p>"
                                      );
                                    }
                                  },
                                });
                              } else {
                                navigator.clipboard.writeText(text).then(
                                  () => {
                                    alert(`Copied ${text} to clipboard.`);
                                  },
                                  (err) => {
                                    alert(
                                      `Trouble copying ${text} to clipboard.`
                                    );
                                  }
                                );
                              }
                              // event.stopPropagation();
                            }
                          );
                          //
                          $(`#${COFFEE_RESULTS_ID}`).hide();
                          $(`#${COFFEE_DYNAMIC_RESULT_ID}`).show();
                          $(`#${COFFEE_HELP_TEXT_ID}`).hide();
                          // Update the history of queries/searches.
                          DrupalCoffee.update_query_history(query);
                        },
                      }).done(() => {
                        setTimeout(function () {
                          $(`#${COFFEE_SPINNER_OVERLAY_ID}`).fadeOut(300);
                        }, 300);
                      });
                    } else {
                      $(`#${COFFEE_SPINNER_OVERLAY_ID}`).fadeIn(300);
                      // Make the position `static` or it is displaced offscreen.
                      $(`#${COFFEE_RESULTS_ID} ul`).css({ position: 'static' });
                      $(`#${COFFEE_RESULTS_ID}`).show();
                      $(`#${COFFEE_DYNAMIC_RESULT_ID}`).html(
                        "<p>We expect a format of 'list-fields NID or BUNDLE'.</p>"
                      );
                      $(`#${COFFEE_DYNAMIC_RESULT_ID}`).show();
                      $(`#${COFFEE_SPINNER_OVERLAY_ID}`).fadeOut(300);
                    }
                  }
                }, 2000);
              }
            });
          },
          // Failure
          (e1, e2) => {
            DrupalCoffee.field.val(
              "Could not load data, please refresh the page."
            );
          }
        );

        $(".toolbar-icon-coffee").click(function (event) {
          event.preventDefault();
          DrupalCoffee.coffee_show();
        });

        // Key events.
        $(document).keydown(function (event) {
          // Show the form with alt + D. Use 2 keycodes as 'D' can be uppercase or lowercase.
          if (
            DrupalCoffee.wrapper.hasClass("hide-form") &&
            event.altKey === true &&
            // 68/206 = d/D, 75 = k.
            (event.keyCode === 68 ||
              event.keyCode === 206 ||
              event.keyCode === 75)
          ) {
            DrupalCoffee.coffee_show();
            event.preventDefault();
          }
          // Close the form with esc or alt + D.
          else {
            if (
              !DrupalCoffee.wrapper.hasClass("hide-form") &&
              (event.keyCode === 27 ||
                (event.altKey === true &&
                  (event.keyCode === 68 || event.keyCode === 206)))
            ) {
              DrupalCoffee.coffee_close();
              event.preventDefault();
            }
          }
        });

        // On the AJAX invokes, fade the spinner in.
        $(document).ajaxSend(function (event, jqXHR, ajaxOptions) {
          if (ajaxOptions.url.includes(SCOPE)) {
            $(`#${COFFEE_SPINNER_OVERLAY_ID}`).fadeIn(300);
          }
        });
      });
    },
  };

  DrupalCoffee.update_query_history = function (query) {
    // Update the history of queries/searches.
    let storage = localStorage.getItem(STORAGE_KEY);
    if (!storage) storage = [];
    else storage = JSON.parse(storage);
    if (!storage.includes(query)) storage.push(query);
    localStorage.setItem(STORAGE_KEY, JSON.stringify(storage));
  };

  DrupalCoffee.display_query_history = function () {
    let storage = localStorage.getItem(STORAGE_KEY);
    if (!storage || storage.length <= 0) return;
    else storage = JSON.parse(storage);
    const N = Math.min(MAX_MEMORY_SIZE, storage.length);
    let recommendations = "";
    for (var i = 0; i < N; i++) recommendations += `<li>${storage[i]}</li>`;
    $(`#${COFFEE_MEMORY_SUGGESTION_ID}`).html(recommendations);
    $(`#${COFFEE_MEMORY_SUGGESTION_WRAPPER}`).show();
  };

  // Prefix the open and close functions to avoid
  // conflicts with autocomplete plugin.
  /**
   * Open the form and focus on the search field.
   */
  DrupalCoffee.coffee_show = function () {
    DrupalCoffee.wrapper.removeClass("hide-form");
    DrupalCoffee.bg.show();
    DrupalCoffee.field.focus();
    $(DrupalCoffee.field).autocomplete({ enable: true });
  };

  /**
   * Close the form and destroy all data.
   */
  DrupalCoffee.coffee_close = function () {
    DrupalCoffee.field.val("");
    DrupalCoffee.wrapper.addClass("hide-form");
    DrupalCoffee.bg.hide();
    $(DrupalCoffee.field).autocomplete({ enable: false });
  };

  /**
   * Close the Coffee form and redirect.
   *
   * @param {string} path
   *   URL to redirect to.
   * @param {bool} openInNewWindow
   *   Indicates if the URL should be open in a new window.
   */
  DrupalCoffee.redirect = function (path, openInNewWindow) {
    DrupalCoffee.coffee_close();

    if (openInNewWindow) {
      window.open(path);
    } else {
      document.location = path;
    }
  };

  /**
   * The HTML elements.
   *
   * @todo use Drupal.theme.
   */
  DrupalCoffee.label = $(
    '<label for="coffee-q" class="visually-hidden" />'
  ).text(Drupal.t("Query", "", ""));
  DrupalCoffee.results = $(`<div id="${COFFEE_RESULTS_ID}" />`);
  DrupalCoffee.wrapper = $('<div class="coffee-form-wrapper" />');
  DrupalCoffee.form = $(`<form id="${COFFEE_FORM_ID}" action="#" />`);
  DrupalCoffee.bg = $(`<div id="${COFFEE_BG_ID}" />`).click(function () {
    DrupalCoffee.coffee_close();
  });

  DrupalCoffee.field = $(
    `<input id="${COFFEE_QUERY_ID}" type="text" autocomplete="off" />`
  );
  DrupalCoffee.helpText = $(
    `<p id="${COFFEE_HELP_TEXT_ID}">If you want to open result in a new tab, please press Cmd/Ctrl key while selecting the result.</p><p>Currently, those commands are supported: ${availableCommands}.</p>`
  );
  DrupalCoffee.dynamicResult = $(
    `<p id="${COFFEE_DYNAMIC_RESULT_ID}">Dynamic result is rendered here.</p>`
  );
  DrupalCoffee.memorySuggestion = $(
    `<div id=${COFFEE_MEMORY_SUGGESTION_WRAPPER}><p>You searched for:</p><ul id="${COFFEE_MEMORY_SUGGESTION_ID}"></ul></div>`
  );
  DrupalCoffee.spinnerOverlay = $(
    `<div id="${COFFEE_SPINNER_OVERLAY_ID}">
      <div class="cv-spinner">
        <span class="spinner"></span>
      </div>
    </div>`
  );
})(jQuery, Drupal, drupalSettings);
