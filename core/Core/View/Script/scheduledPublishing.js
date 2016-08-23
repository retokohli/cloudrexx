/**
 * USAGE:
 *
 * In controller.class.php 
 * =======================
 * \JS::activate('cx');
 * \JS::registerCSS('core/Core/View/Style/scheduledPublishing.css');
 * \JS::registerJS('core/Core/View/Script/scheduledPublishing.js');
 *
 * \ContrexxJavascript::getInstance()->setVariable(array(
 *      'active'            => $_CORELANG['TXT_CORE_ACTIVE'],
 *      'inactive'          => $_CORELANG['TXT_CORE_INACTIVE'],
 *      'scheduledActive'   => $_CORELANG['TXT_CORE_SCHEDULED_ACTIVE'],
 *      'scheduledInactive' => $_CORELANG['TXT_CORE_SCHEDULED_INACTIVE'], 
 * ), 'core/View');
 * 
 * Note: Requires global variable $_CORELANG 
 *
 * In template file
 * ================
 * <a class="schedule_status active" href="javascript:void(0);"></a>
 * <a class="schedule_status inactive" href="javascript:void(0);"></a>
 * <a class="schedule_status scheduled active" href="javascript:void(0);"></a>
 * <a class="schedule_status scheduled inactive" href="javascript:void(0);"></a>
 */

cx.ready(function(){
    var tooltip = cx.jQuery('<div />')
                    .addClass('tooltip-message')
                    .appendTo('body');
    cx.jQuery('.schedule_status')
        .tooltip({
            tip: tooltip,
            position: 'top right',
            track: true,
            predelay: 200,
            onBeforeShow: function() {
                var statuses   = [],
                    objTrigger = this.getTrigger(),
                    objTip     = this.getTip();
                objTip.html('');

                if (objTrigger.hasClass('scheduled')) {
                    if (objTrigger.hasClass('active')) {
                        statuses.push(cx.variables.get('scheduledActive', 'core/View'));
                    } else {
                        statuses.push(cx.variables.get('scheduledInactive', 'core/View'));
                    }
                } else if (objTrigger.hasClass('active')) {
                    statuses.push(cx.variables.get('active', 'core/View'));
                } else {
                    statuses.push(cx.variables.get('inactive', 'core/View'));
                }

                if (statuses.length > 0) {
                    objTip.html(statuses.join('<br />'));
                }
            }
        });
});
