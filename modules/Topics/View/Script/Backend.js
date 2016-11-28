/* global cx */
cx.ready(function() {
  // Category-Entry associations
  cx.jQuery("#entry-categories, #category-entries").chosen();
  // Entry type: Either
  //  - "slug" (internal entry), or
  //  - "href" (link to an internal or external page)
  // Note that this distinction is only implicitly stored in the entry;
  // if the href property of the Entry is non-empty, it's of type "href",
  // "slug" otherwise.
  cx.jQuery("#topics-type-href").click(function() {
    cx.jQuery("input[name^='entry[href]'").parent().parent().show();
    cx.jQuery("input[name^='entry[slug]'").parent().parent().hide();
    cx.jQuery("textarea[name^='entry[description]'").parent().parent().hide();
  });
  cx.jQuery("#topics-type-entry").click(function() {
    cx.jQuery("input[name^='entry[href]'").val("").parent().parent().hide();
    cx.jQuery("input[name^='entry[slug]'").parent().parent().show();
    cx.jQuery("textarea[name^='entry[description]'").parent().parent().show();
  });
  if (cx.jQuery("input[name^='entry[href]'").val()) {
    cx.jQuery("#topics-type-href").click();
  } else {
    cx.jQuery("#topics-type-entry").click();
  }
});
