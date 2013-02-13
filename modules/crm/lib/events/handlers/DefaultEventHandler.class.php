<?php
class DefaultEventHandler implements EventHandler
{
    protected $default_info = array(
        'section'   => 'crm',
        'lang_id'   => FRONTEND_LANG_ID,
    );
    private $_event_fallback_user_id = 1;

    function handleEvent(Event $event) {
        global $_CONFIG, $objFWUser;

        $info = $event->getInfo();
        $substitutions = isset($info['substitution']) ? $info['substitution'] : array();
        $arrMailTemplate = array_merge(
            $this->default_info,
            array(
                'key'     => $event->getName(),
                'substitution' => $substitutions,
        ));

        $user = $objFWUser->objUser;
        if ($user->getId() == 0) {
            $user = $user->getUser($this->_event_fallback_user_id);
        }

        $substitutions = array(
            'URL'       => $_CONFIG['domainUrl'],
            'EMAIL'     => $user->getEmail(),
            'FIRSTNAME' => $user->getProfileAttribute('firstname'),
            'LASTNAME'  => $user->getProfileAttribute('lastname'),
            'USERNAME'  => $user->getUsername(),
            'DATE'      => date("d.m.Y"),
            'DATETIME'  => date("d.m.Y H:i:s"),
        );

        $arrMailTemplate['substitution'] = array_merge($substitutions, $arrMailTemplate['substitution']);
        
        if (false === MailTemplate::send($arrMailTemplate)) {
            $event->cancel();
            return false;
        };
        return true;
    }
}
 
