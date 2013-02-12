<?php
class DefaultEventHandler implements EventHandler
{
    protected $default_info = array(
        'section'   => 'crm',
        'lang_id'   => FRONTEND_LANG_ID,
    );

    function handleEvent(Event $event) {
        $info = $event->getInfo();
        $substitutions = isset($info['substitution']) ? $info['substitution'] : array();
        $arrMailTemplate = array_merge(
            $this->default_info,
            array(
                'key'     => $event->getName(),
                'substitution' => $substitutions,
        ));
        if (false === Communications::send($arrMailTemplate)) {
            $event->cancel();
            return false;
        };
        return true;
    }
}
 
