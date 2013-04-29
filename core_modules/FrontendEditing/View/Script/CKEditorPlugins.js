/**
 * Frontend Editing
 * @author: Ueli Kramer <ueli.kramer@comvation.com>
 * @version: 1.0
 * @package: contrexx
 * @subpackage: core_modules_frontend_editing
 */

/**
 * Add the custom plugins to the ckeditor
 * * the publish button
 */
cx.fe.addCustomPlugins = function () {
    // publish a page
    CKEDITOR.plugins.add('publish', {
        init: function (editor) {
            var pluginName = 'Publish';
            editor.addCommand(pluginName, {
                exec: function (editor) {
                    cx.fe.page.title = CKEDITOR.instances.fe_title.getData();
                    cx.fe.page.content = CKEDITOR.instances.fe_content.getData();
                    cx.fe.page.scheduled_publishing = (cx.fe.page.scheduled_publishing ? 'on' : 'off');
                    cx.fe.page.application = cx.fe.page.module;
                    jQuery.post(cx.variables.get('basePath', 'contrexx') + 'cadmin/index.php?cmd=jsondata&object=page&act=set', {
                        'action': 'publish',
                        'page': cx.fe.page
                    }, function(response) {
                        if (response.data != null) {
                        }
                    });
                }
            });

            editor.ui.addButton(pluginName, {
                label: cx.fe.langVars.TXT_FRONTEND_EDITING_PUBLISH,
                command: pluginName,
                className: 'cke_button_publish'
            });
        }
    });

    // save as draft
    CKEDITOR.plugins.add('save', {
        init: function (editor) {
            var pluginName = 'Save';
            editor.addCommand(pluginName, {
                exec: function (editor) {
                    cx.fe.page.title = CKEDITOR.instances.fe_title.getData();
                    cx.fe.page.content = CKEDITOR.instances.fe_content.getData();
                    cx.fe.page.scheduled_publishing = (cx.fe.page.scheduled_publishing ? 'on' : 'off');
                    cx.fe.page.application = cx.fe.page.module;
                    jQuery.post(cx.variables.get('basePath', 'contrexx') + 'cadmin/index.php?cmd=jsondata&object=page&act=set', {
                        'page': cx.fe.page
                    }, function(response) {
                        if (response.data != null) {
                        }
                    });
                }
            });

            editor.ui.addButton(pluginName, {
                label: cx.fe.langVars.TXT_FRONTEND_EDITING_SAVE,
                command: pluginName,
                className: 'cke_button_save'
            });
        }
    });
};