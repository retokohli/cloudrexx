<?php
/**
 * Convert CSV to SQL queries
 *
 * Actually expects values to be separated by tabstops.
 * @author  Reto Kohli <reto.kohli@comvation.com>
 */

require_once ('lib/Converter.php');
require_once ('lib/SqlGenerators.php');

/**
 * Convert CSV to SQL queries
 *
 * Actually expects values to be separated by tabstops.
 * @author  Reto Kohli <reto.kohli@comvation.com>
 */
class DatabaseToSql
{
    /**
     * Convert the source CSV to the target SQL file
     * @param   string  $csv_source_path
     * @param   string  $sql_target_path
     * @throws  Exception
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public static function convert($sql_target_path)
    {
        $exportFile = fopen($sql_target_path, 'w');
        if (!$exportFile) {
            throw new Exception('Failed to open target file for writing: '
            . $sql_target_path);
        }
        $user = FWUser::getFWUserObject()->objUser;
        $users = $user->getUsers([
            'group_id' => array_keys(Converter::map_usergroups_ids),
        ]);
        if ($users === false) {
            var_dump($user->getErrorMsg());
            die("Error loading Users");
        }
        $sqlQueries = array();
        $sqlQueries[] = \SqlGenerators::dumpHeader();
        $sqlQueries[] = \Converter::initUsers();
        while (!$users->EOF) {
            $user_id = $users->getId();
            $user_id_migrated = $user_id + Converter::user_id_lower;
//\DBG::log("User ID {$users->getId()} -> migrated $user_id_migrated");
            $username = $users->getUsername();
            if (in_array($username, Converter::exclude_usernames)) {
//\DBG::log("Username excluded: $username");
                $users->next();
                continue;
            }
//\DBG::log("Original Newsletter List IDs");//\DBG::dump($users->getSubscribedNewsletterListIDs());
            $newsletterIds = [];
            foreach ($users->getSubscribedNewsletterListIDs() as $newsletterId) {
                if (array_key_exists($newsletterId,
                        Converter::map_newsletter_list_ids)) {
                    $newsletterIds[Converter::map_newsletter_list_ids[$newsletterId]] =
                        true;
                }
            }
//\DBG::log("Replaced Newsletter List IDs");//\DBG::dump($newsletterIds);
//\DBG::log("Original OpenUser Group IDs");//\DBG::dump($users->getAssociatedGroupIds());
            $usergroupIds = [];
            foreach ($users->getAssociatedGroupIds() as $usergroupId) {
                if (array_key_exists($usergroupId, Converter::map_usergroups_ids)) {
                    $usergroupIds[Converter::map_usergroups_ids[$usergroupId]] = true;
                }
            }
            if (!$usergroupIds) {
                die("Error: no user group");
            }
//\DBG::log("Replaced OpenUser Group IDs");//\DBG::dump($usergroupIds);
            $password = static::getPassword($user_id);
//\DBG::log("Password: $password");
            $sqlQueries[] = static::makeUser(
                $user_id_migrated, $users->getUsername(),
                $password,
                $users->getEmail(), $users->getRegistrationDate(),
                $users->getLastAuthenticationTime(), $users->getLastActivityTime(),
                $users->getEmailAccess(), $users->getFrontendLanguage(),
                $users->getBackendLanguage(), $users->getActiveStatus(),
                current(array_keys($usergroupIds)),
                $users->getProfileAccess(),
                $users->getProfileAttribute('gender'),
                $users->getProfileAttribute('title'),
                $users->getProfileAttribute('firstname'),
                $users->getProfileAttribute('lastname'),
                $users->getProfileAttribute('company'),
                $users->getProfileAttribute('address'),
                $users->getProfileAttribute('city'),
                $users->getProfileAttribute('zip'),
                $users->getProfileAttribute('country'),
                $users->getProfileAttribute('phone_office'),
                $users->getProfileAttribute('phone_private'),
                $users->getProfileAttribute('phone_mobile'),
                $users->getProfileAttribute('phone_fax'),
                $users->getProfileAttribute('birthday'),
                $users->getProfileAttribute('website'),
                $users->getProfileAttribute('profession'),
                $users->getProfileAttribute('interests'),
                $users->getProfileAttribute('signature'),
                $users->getProfileAttribute('picture')
            );
            $sqlQueries[] = SqlGenerators::userGroup(
                $user_id_migrated, array_keys($usergroupIds)
            );
            $customAttributes = [
                '1' => $users->getProfileAttribute(1),
                '2' => $users->getProfileAttribute(2),
                '3' => $users->getProfileAttribute(3),
                '4' => $users->getProfileAttribute(4),
                '5' => $users->getProfileAttribute(5),
                '6' => $users->getProfileAttribute(6),
                '7' => $users->getProfileAttribute(7),
                '8' => $users->getProfileAttribute(8),
                '9' => $users->getProfileAttribute(9),
                '10' => $users->getProfileAttribute(10),
            ];
            $sqlQueries[] = static::makeCustomProfileAttributes(
                $user_id_migrated, $customAttributes
            );
            $sqlQueries[] = static::makeNewsletterAccessUser(
                $user_id_migrated, array_keys($newsletterIds)
            );
//echo "<hr />aborting<br />";break;
            $users->next();
        }
        fwrite($exportFile, join("\n", $sqlQueries) . "\n");
        fclose($exportFile);
    }

    /**
     * Return queries for inserting a User with the given properties
     * @param   integer $user_id
     * @param   string  $username
     * @param   string  $password
     * @param   string  $email
     * @param   integer $regdate
     * @param   integer $last_auth
     * @param   integer $last_activity
     * @param   string  $email_access
     * @param   integer $frontend_lang_id
     * @param   integer $backend_lang_id
     * @param   boolean $active
     * @param   integer $primary_group
     * @param   string  $profile_access
     * @param   string  $gender
     * @param   string  $title
     * @param   string  $firstname
     * @param   string  $lastname
     * @param   string  $company
     * @param   string  $address
     * @param   string  $city
     * @param   string  $zip
     * @param   integer $country
     * @param   string  $phone_office
     * @param   string  $phone_private
     * @param   string  $phone_mobile
     * @param   string  $phone_fax
     * @param   integer $birthday
     * @param   string  $website
     * @param   string  $profession
     * @param   string  $interests
     * @param   string  $signature
     * @param   string  $picture
     * @return  string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public static function makeUser(
        $user_id, $username, $password, $email, $regdate, $last_auth,
        $last_activity, $email_access, $frontend_lang_id, $backend_lang_id,
        $active, $primary_group, $profile_access, $gender, $title, $firstname,
        $lastname, $company, $address, $city, $zip, $country, $phone_office,
        $phone_private, $phone_mobile, $phone_fax, $birthday, $website,
        $profession, $interests, $signature, $picture)
    {
        $sqlQueries = array();
        $sqlQueries[] = \SqlGenerators::user(
                $user_id, $username, $password, $email, $regdate, $last_auth,
                $last_activity, $email_access, $frontend_lang_id,
                $backend_lang_id, $active, $primary_group, $profile_access);
        $sqlQueries[] = \SqlGenerators::userProfile(
                $user_id, $gender, $title, $firstname, $lastname, $company,
                $address, $city, $zip, $country, $phone_office, $phone_private,
                $phone_mobile, $phone_fax, $birthday, $website, $profession,
                $interests, $signature, $picture);
        return join("\n", $sqlQueries);
    }

    /**
     * Return queries for inserting ProfileAttributes
     *
     * For each numeric key, adds a corresponding insert query with that value
     * in the array.
     * Empty values (null, the empty string, and false) are skipped.
     * Values must be of a scalar type.
     * @param   integer $user_id
     * @param   array   $converted
     * @return  string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public static function makeCustomProfileAttributes($user_id, $converted)
    {
        $sqlQueries[] = \SqlGenerators::userAttributeValue($user_id, 0, '');
        foreach ($converted as $attributeId => $value) {
            // Skip columns that are not custom ProfileAttributes
            if (!is_numeric($attributeId)) {
                continue;
            }
            if (is_null($value)) {
                continue;
            }
            if ($value === '') {
                continue;
            }
            if ($value === false) {
                continue;
            }
            if (is_array($value)) {
                echo "CsvToSql::makeCustomProfileAttributes($user_id, \$converted): ERROR: Cannot handle array value:<br />";
                var_dump($value);
                continue;
            }
            $sqlQueries[] = \SqlGenerators::userAttributeValue($user_id,
                    $attributeId, $value);
        }
        return join("\n", $sqlQueries);
    }

    protected static function makeNewsletterAccessUser($user_id, $newsletterIds)
    {
        $sqlQueries = [];
        foreach ($newsletterIds as $newsletterId) {
            $sqlQueries[] = SqlGenerators::newsletterAccessUser(
                $user_id, $newsletterId
            );
        }
        return join("\n", $sqlQueries);
    }

    protected static function getPassword($userId)
    {
        global $objDatabase;
        $result = $objDatabase->Execute('
            SELECT `password`
            FROM `' . Config::DBPREFIX . 'access_users`
            WHERE `id`=?', $userId);
        return ($result ? $result->fields['password'] : false);
    }

}
