--------------------------------------------------------------------------------
-- Import basic data
--------------------------------------------------------------------------------

REPLACE INTO contrexx_module_partners(
    name,           first_contact_name, first_contact_email,
    web_url,        address,       city,
    zip_code,       phone_nr,      fax_nr,
    customer_quote, logo_url,      description,    
    active,         creation_date, import_ref
)

SELECT `1`,                 `2`,               `9`, 
    concat('http://', `8`), `3`,               `5`, 
    `4`,                    `6`,               `7`, 
    '',                     '',                '', 
    1,                      CURRENT_TIMESTAMP, `id`

FROM contrexx_module_memberdir_values;


--------------------------------------------------------------------------------
-- Import Level data
--------------------------------------------------------------------------------
DELETE FROM contrexx_module_partners_to_labels 
    WHERE label_id IN (14,13,16)
    AND partner_id NOT IN (SELECT id FROM contrexx_module_partners WHERE import_ref IS NULL);

REPLACE INTO contrexx_module_partners_to_labels(partner_id, label_id)
SELECT 
    p.id, 
    CASE WHEN mdv.`10` = 1 THEN 14 
    ELSE (
        CASE WHEN mdv.`10` = 2 THEN 13
        ELSE 16
        END
    )
    END
FROM contrexx_module_partners p 
LEFT JOIN contrexx_module_memberdir_values mdv ON mdv.id = p.import_ref;


--------------------------------------------------------------------------------
-- Import Region Data
--------------------------------------------------------------------------------
REPLACE INTO contrexx_module_partners_to_labels(partner_id, label_id)

-- Switzerland
SELECT p.id, 6251 FROM contrexx_module_memberdir_values m 
    LEFT JOIN contrexx_module_partners p ON p.import_ref = m.id
    WHERE m.dirid IN (SELECT dirid FROM contrexx_module_memberdir_directories WHERE name = 'Schweiz' OR name LIKE 'Kanton%')

-- Germany
UNION SELECT p.id, 6128 FROM contrexx_module_memberdir_values m 
    LEFT JOIN contrexx_module_partners p ON p.import_ref = m.id
    WHERE m.dirid IN (
        SELECT dirid FROM contrexx_module_memberdir_directories WHERE name IN (
            'Bayern', 'Berlin', 'Hamburg', 'Hessen', 'Niedersachsen',
            'Rheinland-Pfalz', 'Sachsen', 'Nordrhein-Westfalen', 
            'Schleswig-Holstein', 'Mecklenburg-Vorpommern', 'Deutschland'
        ) 
        OR name LIKE 'Baden-W%rttemberg'
        OR name LIKE 'Th%ringen'
    )

-- Dänemark
UNION SELECT p.id, 6104 FROM contrexx_module_memberdir_values m 
    LEFT JOIN contrexx_module_partners p ON p.import_ref = m.id
    WHERE m.dirid IN (
        SELECT dirid FROM contrexx_module_memberdir_directories WHERE name LIKE 'D%nemark'
    )

-- Österreich
UNION SELECT p.id, 6061 FROM contrexx_module_memberdir_values m 
    LEFT JOIN contrexx_module_partners p ON p.import_ref = m.id
    WHERE m.dirid IN (
        SELECT dirid FROM contrexx_module_memberdir_directories WHERE name LIKE '%sterreich'
    )
;

--------------------------------------------------------------------------------
-- Make all imported partners "solution partners"
--------------------------------------------------------------------------------
REPLACE INTO contrexx_module_partners_to_labels(partner_id, label_id)
SELECT id, 6288 FROM contrexx_module_partners WHERE import_ref IS NOT NULL;


