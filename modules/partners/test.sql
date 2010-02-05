SELECT COUNT(*) AS c
FROM   ( SELECT           partner.*
       FROM     `contrexx_module_partners` AS partner
                LEFT JOIN contrexx_module_partners_to_labels
                ON       contrexx_module_partners_to_labels.partner_id = partner.id
                LEFT JOIN contrexx_module_partners_label_entry
                ON       contrexx_module_partners_to_labels.label_id = contrexx_module_partners_label_entry.id
       WHERE    (name                                             LIKE '%'
                OR       first_contact_name                       LIKE '%'
                OR       web_url                                  LIKE '%'
                OR       address                                  LIKE '%'
                OR       city                                     LIKE '%'
                )
       AND      contrexx_module_partners_label_entry.default_partner = 1
       ORDER BY `name`
       ) AS
       q1
