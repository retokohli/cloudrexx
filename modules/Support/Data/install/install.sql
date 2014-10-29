# Insert Module into Modules table
INSERT INTO `contrexx_modules` (`id`, `name`, `distributor`, `description_variable`, `status`, `is_required`, `is_core`, `is_active`, `is_licensed`) VALUES ('100', 'Support', 'Comvation AG', 'TXT_MODULE_SUPPORT_DESCRIPTION', 'y', '1', '0', '1', '1');

# Insert Modul into components table
INSERT INTO `contrexx_component` (`id`, `name`, `type`) VALUES ('100', 'Support', 'module');

# Insert Menu table
INSERT INTO `contrexx_backend_areas` (`area_id`, `parent_area_id`, `type`, `scope`, `area_name`, `is_active`, `uri`, `target`, `module_id`, `order_id`, `access_id`) VALUES (NULL, '2', 'navigation', 'global', 'TXT_MODULE_SUPPORT', '1', 'index.php?cmd=Support', '_self', '100', '15', '899');

# Create Tables
CREATE TABLE contrexx_module_support_feedback (id INT AUTO_INCREMENT NOT NULL, feedback_type INT NOT NULL, subject VARCHAR(255) NOT NULL, comment LONGTEXT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB;

# Add following entry for feedback notification
INSERT INTO `contrexx_core_mail_template` (`key`, `section`, `text_id`, `html`, `protected`) VALUES ('notifyAboutNewSupportFeedBack', 'Support', '18', '1', '1');

INSERT INTO `trunk`.`contrexx_core_text` (`id`, `lang_id`, `section`, `key`, `text`) VALUES 
('18', '1', 'Support', 'core_mail_template_bcc', ''), 
('18', '1', 'Support', 'core_mail_template_cc', ''), 
('18', '1', 'Support', 'core_mail_template_from', '[FEEDBACK_FROM_EMAIL]'), 
('18', '1', 'Support', 'core_mail_template_message', '[TXT_USER_CONTACT]: \\n
[TXT_USER_NAME]: [USER_NAME] \\n
[TXT_USER_EMAIL]: [USER_EMAIL] \\n
\\n\\n
[TXT_FEEDBACK_MAIL]: \\n\\n
[TXT_FEEDBACK_TOPIC]: [FEEDBACK_TYPE] \\n\\n
[TXT_FEEDBACK_URL]: [FEEDBACK_URL] \\n\\n
[TXT_FEEDBACK_COMMENT]: [FEEDBACK_COMMENT] \\n\\n
'), 
('18', '1', 'Support', 'core_mail_template_message_html', '<div style="width:600px; font-family: arial,helvetica,sans-serif; font-size: 13px;">

    <p><strong>[TXT_USER_CONTACT]</strong></p>

    <table cellpadding="0" cellspacing="0" style="width:100%; font-size: 13px;">
        <tbody>
            <tr>
                <td valign="top">[TXT_USER_NAME]</td>
                <td>&nbsp;: [USER_NAME]</td>
            </tr>
            <tr>
                <td valign="top">[TXT_USER_EMAIL]</td>
                <td>&nbsp;: [USER_EMAIL]</td>
            </tr>
        </tbody>
    </table>


    <p><strong>[TXT_FEEDBACK_MAIL]</strong></p>

    <table cellpadding="0" cellspacing="0" style="width:100%; font-size: 13px;">
        <tbody>
            <tr>
                <td valign="top" width="15%">[TXT_FEEDBACK_TOPIC]</td>
                <td>&nbsp;: [FEEDBACK_TYPE]</td>
            </tr>
            <tr>
                <td valign="top">[TXT_FEEDBACK_URL]</td>
                <td>&nbsp;: [FEEDBACK_URL]</td>
            </tr>
            <tr>
                <td valign="top">[TXT_FEEDBACK_COMMENT]</td>
                <td>&nbsp;: [FEEDBACK_COMMENT]</td>
            </tr>
        </tbody>
    </table>
</div>'), 
('18', '1', 'Support', 'core_mail_template_name', 'FeedBack'), 
('18', '1', 'Support', 'core_mail_template_reply', ''), 
('18', '1', 'Support', 'core_mail_template_sender', '[FEEDBACK_SENDER_NAME]'), 
('18', '1', 'Support', 'core_mail_template_subject', '[FEEDBACK_SUBJECT]'), 
('18', '1', 'Support', 'core_mail_template_to', '[FEEDBACK_TO_EMAIL]');