/**
 * Topics frontend JS
 *
 * Note that jQuery 2.0.3 is activated by the FrontendController,
 * cx.jQuery (1.6.1) is not used as its version is far too low.
 */
/* global cx */
cx.ready(function() {
  var url_base = cx.variables.get("url_base", "Module/Topics");
  var slug_entry = cx.variables.get("slug_entry", "Module/Topics");
  var slug_category = cx.variables.get("slug_category", "Module/Topics");
  // Note that the system locale and page slug are both taken from url_base
  //var locale_system = cx.variables.get("locale_system", "Module/Topics");
  var locale_list = cx.variables.get("locale_list", "Module/Topics");
  var locale_detail = cx.variables.get("locale_detail", "Module/Topics");
  /**
   * @type {boolean}
   */
  var frontend_fulltext_enable = cx.variables.get(
    "frontend_fulltext_enable", "Module/Topics");
  // Customizing/Extension:
  var theme_folder = cx.variables.get("themeFolder", "contrexx");
  var entry_id = null;
  var loaded = {};
  var fulltext = null; // unset on purpose
  var requests = [];
  var request_running = false;
  jQuery("#topics-controls select").change(changeControls);
  jQuery("#topics-term").keyup(debounce(filterList, 300));
  if (frontend_fulltext_enable) {
    jQuery("#topics-fulltext-hidden").show();
    jQuery("#topics-fulltext").change(changeFulltext);
  }
  // Compatibility with all jQuery versions prior to 1.7
  // requires the use of delegate().
  // When upgrading to jQuery 1.7+, replace "delegate" with "on",
  // and switch its first two parameters, e.g.
  //  .delegate("a", "change", clickDetail);
  // becomes
  //  .on("change", "a", clickDetail);
  // Note: Delegated events cannot be bound to elements that are
  // replaced entirely. Instead, they are tied to the common parent.
  jQuery("#topics")
    // MUST NOT match "#topics-letter a"!
    .delegate("#topics-list a", "click", clickDetail)
    .delegate("#topics-detail-entry a", "click", clickDetail);
  // After initially loading the list, also load the details
  // if an entry slug is present in the URL
  changeControls().then(function() {
    changeDetail();
  });
  /**
   * Handler for changed controls
   * @returns {Deferred}
   */
  function changeControls() {
    if (locale_detail !== jQuery("#topics-locale-detail").val()) {
      locale_detail = jQuery("#topics-locale-detail").val();
      // If the detail locale is changed, only the detail is updated.
      // Ignore the list locale when updating the detail.
      var href = makeListUrl(locale_detail);
      return getHref(href).then(function() {
        changeDetail();
        updateUrl();
      });
    }
    // Other cases:
    //  - Change the Category
    //  - Change the list locale
    slug_category = jQuery("#topics-category").val();
    locale_list = jQuery("#topics-locale-list").val();
    // Ignore the detail locale when updating the list.
    var href = makeListUrl(locale_list);
    return getHref(href).then(function() {
      updateList(loaded[href]);
      updateUrl();
      // Some browsers don't reset the input on reload.
      // If so, restore the previous results.
      // If the list locale has changed, this may or may not
      // be useful, however.
      filterList();
    });
  }
  /**
   * Return the URL for loading the list in the given locale via an API call
   * @param   {string} locale
   * @returns {string}
   */
  function makeListUrl(locale) {
    return url_base +
      "/" + slug_category +
      "/" + locale + // Note that this represents the list locale
      // No detail locale nor entry slug.
      // But add empty path components and the fulltext flag
      "///fulltext";
  }
  /**
   * Delay the callback for some time after the last call
   * @param   {function}  callback
   * @param   {integer}   delayMs
   * @returns {function}
   */
  function debounce(callback, delayMs) {
    var timeout = null;
    return function() {
      if (timeout) {
        window.clearTimeout(timeout);
      }
      timeout = window.setTimeout(callback, delayMs);
    };
  }
  /**
   * Show list entries matching the search term
   *
   * Depending on the frontend_fulltext_enable setting
   * and the #topics-fulltext checkbox, filters by
   *  - Entry names present in the list, or
   *  - full Entries, including name and description
   * @returns {undefined}
   */
  function filterList() {
    var terms = jQuery("#topics-term").val().split(/\s+/)
      .filter(function(term) {
        return term !== "";
      })
      .map(function(term) {
        return new RegExp(term, "i");
      });
    var size = terms.length;
    // When filtering, hide the anchors to provide a free view on the results
    if (size) {
      jQuery(".topics-list-anchor").hide();
    } else {
      jQuery(".topics-list-anchor").show();
    }
    // Unless fulltext search is on, limit to the Entry names.
    // Note that the parent <a> element must be shown or hidden in any case.
    var selector_element = ".topics-list-entry a";
    var selector_filter = (fulltext ? null : " .topics-list-entry-name");
    jQuery(selector_element)
      .each(function(i, element) {
        element = jQuery(element);
        var element_filter = element;
        if (selector_filter) {
          element_filter = element.find(selector_filter);
        }
        var text = element_filter.text();
        element.show(); // Show all if the term is empty
        var index = 0;

        // Implements logical OR with individual search terms:
        //while (index < size) {
        //  if (terms[index].test(text)) {
        //    return;
        //  }
        //  ++index;
        //}
        //element.hide();

        // Implements logical AND with individual search terms:
        while (index < size) {
          if (!terms[index].test(text)) {
            element.hide();
            return;
          }
          ++index;
        }
      });
  }
  function changeFulltext(event) {
    var checkbox = jQuery(event.target);
    fulltext = false;
    if (checkbox.is(":checked")) {
      fulltext = true;
    }
    filterList();
  }
  /**
   * Handle clicks on any Entry link
   *
   * Covers both the list and the detail part of the view.
   * Opens "internal" links representing Topics Entries in the detail view.
   * "External" links, recognized by containing at least one slash, are
   * opened in a new tab or window.
   * @param   {Event}   event
   * @returns {Boolean}
   */
  function clickDetail(event) {
    // Clicked the target <span>, but triggered the current target <a>!
    var target = jQuery(event.currentTarget);
    // Detect href attributes that point outside the Topics
    var href = target.attr("href");
    if (!href) {
      //console.log("clickDetail(): ERROR: no href: ", href);
      return false; // Suppress default event
    }
    // Topics Entry (aka internal) href attribute values always have the form
    //  <slug_entry>
    // URLs containing a slash are regarded as external.
    if (href.match(/\//)) {
      window.open(href);
      return false; // Suppress default event
    }
    slug_entry = href;
    changeDetail();
    updateUrl();
    return false; // Suppress default event
  }
  /**
   * Find the Entry matching the current slug_entry
   * @returns {undefined}
   */
  function changeDetail() {
    if (!slug_entry) {
      return false;
    }
    // The Entry selection may have been made in either the list
    // or the detail locale, depending on where the user clicked!
    // Try to find the Entry slug in the list locale first.
    var href = makeListUrl(locale_list);
    findSlug(href)
      .done(function() {
        // Global entry_id is now set to the correct value
      })
      .fail(function() {
        // When the detail locale is different, the above won't match.
        // Ensure that the detail locale version of the list is loaded, too.
        var href = makeListUrl(locale_detail);
        findSlug(href)
          .done(function() {
            // Global entry_id is now set to the correct value
          })
          .fail(function() {
            // Global entry_id has been nulled
          });
      })
      .always(function() {
        if (!entry_id) {
          return;
        }
        // Search for the Entry ID
        var selector = "a[data-id='" + entry_id + "']";
        var href = makeListUrl(locale_detail);
        // The detail locale may not have been loaded yet,
        // e.g. after reloading the page.
        getHref(href).then(function() {
          var target = jQuery(loaded[href]).find(selector);
          if (!target.length) {
            return;
          }
          updateDetail(
            // Mind that the contents are HTML encoded, so no html()!
            target.find(".topics-list-entry-name").text(),
            target.find(".topics-list-entry-description").text());
        });
      });
  }
  /**
   * Return a promise that is resolved if the current Entry slug is found
   *
   * Tries to load href if not cached.
   * Searches the resulting list of Entries and sets global entry_id on success.
   * On error or miss, the promise is rejected.
   * @param   {string}  href
   * @returns {Promise}
   */
  function findSlug(href) {
    var defer = jQuery.Deferred();
    getHref(href).then(function() {
      var selector = "a[href='" + slug_entry + "']";
      var target = jQuery(loaded[href]).find(selector);
      if (target.length) {
        entry_id = target.data("id");
        defer.resolve();
      } else {
        entry_id = null;
        defer.reject();
      }
    });
    return defer.promise();
  }
  /**
   * Load the URL given
   *
   * Resolves the returned Promise object when the content is ready.
   * @param   {string}    href
   * @returns {Promise}
   */
  function getHref(href) {
// TEST: Disable to skip cache, and force reloading
    if (loaded[href]) {
      // Short for: return jQuery.Deferred().resolve().promise();
      return jQuery.when();
    }
    return topicsRequest(href);
  }
  /**
   * Update the list part of the view
   * @param   {string}    data
   * @returns {undefined}
   */
  function updateList(data) {
    // The new content SHOULD NOT replace the #element itself,
    // but only its previous content (aka inner HTML).
    // Otherwise, on a failed request, the container element would
    // be replaced with empty content, and thus be removed!
    jQuery("#topics-list-entries").html(jQuery(data).html());
    // Must call AFTER updating the content. Otherwise, anchors won't work.
  }
  /**
   * Update the detail part of the view
   * @param   {string}    name
   * @param   {string}    description
   * @returns {undefined}
   */
  function updateDetail(name, description) {
    // By definition, API calls will return detail contents with
    // href attribute values consisting of the entry slug only, e.g.:
    //  <a href="creancier">créancier</a>
    jQuery("#topics-detail-entry-name").html(name);
    jQuery("#topics-detail-entry-description").html(description);
  }
  /**
   * Update the URL in the browser address bar
   * @returns {undefined}
   */
  function updateUrl() {
    var title = jQuery("#topics-detail-entry-name").text().trim();
    var href = url_base + "/" +
      slug_category + "/" +
      locale_list + "/" +
      locale_detail + "/" +
      slug_entry +
      window.location.hash;
    // Update the address without reloading the page; see
    //  http://stackoverflow.com/questions/3338642/updating-address-bar-with-new-url-without-hash-or-reloading-the-page
    var state = {
      // Note: Unused FTTB, but might come in handy:
      slug_category: slug_category,
      locale_list: locale_list,
      locale_detail: locale_detail,
      slug_entry: slug_entry
    };
    // Note that "title" here is the history entry name,
    // and is NOT applied to the document title!
    window.history.pushState(state, title, href);
    // Also note that
    //window.history.replaceState("object or string", "Title", "/another-new-url");
    // will not create a new state, but replace the current one.
  }
  /**
   * Handle browser history events
   * @param   {Event}     e
   * @returns {undefined}
   */
  window.onpopstate = function(e) {
    if (e.state) {
      slug_category = e.state.slug_category;
      locale_list = e.state.locale_list;
      locale_detail = e.state.locale_detail;
      slug_entry = e.state.slug_entry;
      changeDetail();
    }
  };
  /**
   * Queue a request
   *
   * Resolves the returned Promise object when the content is ready.
   * @param   {string}    href
   * @returns {Promise}
   */
  function topicsRequest(href) {
    var defer = jQuery.Deferred();
    requests.push(topicsRequestFactory(defer, href));
    topicsRequestTrigger();
    return defer.promise();
  }
  /**
   * Trigger the next request in the queue, if any
   * @returns {undefined}
   */
  function topicsRequestTrigger() {
    if (requests.length === 0) {
      // Nothing to be done
      return;
    }
    if (request_running) {
      // The currently running request will trigger when done
      return;
    }
    request_running = true;
    var request = requests.shift();
    request();
  }
  /**
   * Return a triggerable AJAX request for href in the form of a function
   *
   * The function will eventually resolve or reject the given Deferred object.
   * @param   {Deferred}  defer
   * @param   {string}    href
   * @returns {function}
   */
  function topicsRequestFactory(defer, href) {
    return function() {
      var url =
        cx.variables.get(
// Does not work on my local system:
//      "baseUrl", "MultiSite") +
// Instead, use
          "basePath", "contrexx") +
        "api/TopicsEntries" + // API path
        "&href=" + href +
        "&theme_folder=" + theme_folder;
      busy();
      return jQuery.ajax({
        dataType: "html", // or "json" for JSON
        url: url,
        type: "GET"
      })
        .done(function(response) {
          loaded[href] = jQuery.parseHTML(response.trim());
          defer.resolve();
        })
        .fail(function(request, status, error) {
          defer.reject(error);
        })
        .always(function() {
          ready();
        });
    };
  }
  /**
   * Mark the view as busy
   * @returns {undefined}
   */
  function busy() {
    jQuery("#loading").fadeIn();
  }
  /**
   * Mark the view as ready
   * @returns {undefined}
   */
  function ready() {
    jQuery("#loading").fadeOut("fast");
    request_running = false;
    topicsRequestTrigger();
  }
});
