<?php
/**
 * SQL DDL/DML Helpers
 * @author  Reto Kohli <reto.kohli@comvation.com>
 */

/**
 * Various generators for standard DDL/DML
 * @author  Reto Kohli <reto.kohli@comvation.com>
 */
class SqlGenerators
{
    /**
     * Return the header to be inserted into the MySQL dump file
     *
     * Make MySQL treat the dump as UTF-8 encoded.
     * @return  string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public static function dumpHeader()
    {
        return '/*!40101 SET NAMES utf8 */;'."\n";
    }

    /**
     * Return a query for inserting the User with the given properties
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
     * @return  string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public static function user(
        $user_id, $username, $password, $email, $regdate, $last_auth,
        $last_activity, $email_access, $frontend_lang_id, $backend_lang_id,
        $active, $primary_group, $profile_access)
    {
        $query = sprintf(
            'INSERT INTO `' . Config::DBPREFIX . 'access_users` ('
            . '`id`, `username`, `password`, `email`, '
            . '`regdate`, `last_auth`, `last_activity`, '
            . '`email_access`, `frontend_lang_id`, `backend_lang_id`, '
            . '`active`, `primary_group`, `profile_access`'
            . ') VALUES ('
            . '%1$u, "%2$s", "%3$s", "%4$s", '
            . '%5$d, %6$d, %7$d, '
            . '"%8$s", %9$u, %10$u, '
            . '%11$u, %12$u, "%13$s"'
            . ');', $user_id, addslashes($username), addslashes($password),
            addslashes($email), $regdate, $last_auth, $last_activity,
            $email_access, $frontend_lang_id, $backend_lang_id, $active,
            $primary_group, $profile_access
        ); //die($query);
        return $query;
    }

    /**
     * Return a query for assigning a User to the given Usergroups
     * @param   integer $user_id
     * @param   array   $usergroup_ids
     * @return  string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public static function userGroup($user_id, array $usergroup_ids)
    {
        $query =
            'INSERT INTO `' . Config::DBPREFIX . 'access_rel_user_group` '
            . '(`user_id`, `group_id`) '
            . 'VALUES('
            . join('), (',
                array_map(function($usergroup_id) use ($user_id) {
                    return sprintf('%1$u, %2$u', $user_id, $usergroup_id);
                }, $usergroup_ids)
            )
            . ');';
        return $query;
    }

    /**
     * Return a query for inserting the Userprofile with the given properties
     * @param   integer $user_id
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
    public static function userProfile($user_id, $gender, $title, $firstname,
        $lastname, $company, $address, $city, $zip, $country, $phone_office,
        $phone_private, $phone_mobile, $phone_fax, $birthday, $website,
        $profession, $interests, $signature, $picture)
    {
        return sprintf(
            'INSERT INTO `' . Config::DBPREFIX . 'access_user_profile` ('
            . '`user_id`, `gender`, `title`, '
            . '`firstname`, `lastname`, `company`, '
            . '`address`, `city`, `zip`, `country`, '
            . '`phone_office`, `phone_private`, `phone_mobile`, `phone_fax`, '
            . '`birthday`, `website`, `profession`, '
            . '`interests`, `signature`, `picture`'
            . ') VALUES ('
            . '%1$u, "%2$s", "%3$s", '
            . '"%4$s", "%5$s", "%6$s", '
            . '"%7$s", "%8$s", "%9$s", "%10$s", '
            . '"%11$s", "%12$s", "%13$s", "%14$s", '
            . '%15$s, "%16$s", "%17$s", '
            . '"%18$s", "%19$s", "%20$s"'
            . ');',
            $user_id, $gender, addslashes($title),
            addslashes($firstname), addslashes($lastname), addslashes($company),
            addslashes($address), addslashes($city), addslashes($zip), $country,
            addslashes($phone_office), addslashes($phone_private),
            addslashes($phone_mobile), addslashes($phone_fax),
            (isset($birthday) ? intval($birthday) : 'NULL'),
            addslashes($website), addslashes($profession),
            addslashes($interests), addslashes($signature), addslashes($picture)
        );
    }

    /**
     * Return a query for inserting a ProfileAttribute
     * @param   integer $user_id
     * @param   integer $attributeId
     * @param   string  $attributeValue
     * @return  string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public static function userAttributeValue($user_id, $attributeId,
        $attributeValue)
    {
//echo "SqlGenerators::userAttributeValue($user_id, $attributeId, $attributeValue): DEBUG: Inserting<br />";
        return sprintf(
            'INSERT INTO `' . Config::DBPREFIX . 'access_user_attribute_value` '
            . '(`attribute_id`, `user_id`, `value`) VALUES '
            . '("%1$s", %2$u, "%3$s");',
            $attributeId, $user_id, addslashes($attributeValue));
    }

    /**
     * Return a query for inserting a Newsletter subscription
     * @param   int     $user_id
     * @param   int     $category_id
     * @param   string  $code
     * @return  string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public static function newsletterAccessUser(
        $user_id, $category_id, $code = ''
    ) {
        return sprintf(
            'INSERT INTO `' . Config::DBPREFIX . 'module_newsletter_access_user` '
            . '(`accessUserID`,	`newsletterCategoryID`, `code`) VALUES '
            . '("%1$u", %2$u, "%3$s");',
            $user_id, $category_id, addslashes($code)
        );
    }

}
