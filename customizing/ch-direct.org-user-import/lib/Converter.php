<?php
/**
 * Convert selected Users and Newsletter subscriptions from voev.ch
 * for inserting into the new ch-direct.org installation
 *
 * Note: You MUST provide the source database tables with the prefix
 * as set in the Converter::TABLE_PREFIX_SOURCE constant.
 * @author  Reto Kohli <reto.kohli@comvation.com>
 */

/**
 * Custom conversion of associations
 * @author  Reto Kohli <reto.kohli@comvation.com>
 */
class Converter
{
    /**
     * Path and file name of the SQL DDL/DML to be written
     */
    const SQL_TARGET_PATH = 'data/export/ch-direct.org-user-newsletter.sql';

    const user_id_lower = 100;
    const user_id_upper = 10000;

    /**
     * Exclude users that already exist on the target system by username
     */
    const exclude_usernames = [
        'system',
        'cecile.eppler@sbb.ch',
        'dsfsdf@asdasd.ch',
        'christophe.dessonnaz@tpf.ch',
        'christophe.dessonnza@tpf.ch',
        'kommunikation@ch-direct.org',
        'bruno.galliker@voev.ch',
        'floriane.moerch@voev.ch',
        'daniel.meier@ch-direct.org',
        'thomas.ammann@ch-direct.org',
        'janik@comvation.com',
    ];

    /**
     * Map old to new UserGroup IDs
     *
     * Only members of any of the old groups are migrated.
     * Mapping: Old UserGroup ID => new UserGroup ID
     *      25 [TU] Community DV: 12, 15, 25
     *      26 [B] Kantone, Bund: 42, 61
     *      27 [M] Medienschaffende: Keine
     *      28 [D] Drittanbieter: 36, 55
     */
    const map_usergroups_ids = [
        12 => 25,
        15 => 25,
        25 => 25,
        42 => 26,
        61 => 26,
        36 => 28,
        55 => 28,
    ];

    /**
     * Map Newsletter list IDs
     *
     * voev.ch:     ch-direct.org:
     *  7            2              Newsletter ch-direct [de]
     *  3            3              Newsletter ch-direct [fr]
     *  4            4              Newsletter ch-direct [it]
     */
    const map_newsletter_list_ids = [
        7 => 2,
        3 => 3,
        4 => 4,
    ];

    /**
     * Return queries for preparing Users between the given ID limits
     * for the import
     *
     * Deletes all User and Newsletter in the ID range for repeated execution.
     * Forces the AUTO_INCREMENT of the User table to be greater than the
     * upper limit.
     * @return  string              The SQL queries
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public static function initUsers()
    {
        $sqlQueries[] = 'INSERT INTO `' . Config::DBPREFIX . 'access_users`'
            . ' (`id`) VALUES (' . static::user_id_upper . ');';
        $sqlQueries[] = 'DELETE FROM `' . Config::DBPREFIX . 'access_users`'
            . ' WHERE `id`=' . static::user_id_upper . ';';
        $whereCondition = ' BETWEEN ' . static::user_id_lower
            . ' AND ' . static::user_id_upper;
        // Order of deletion is important; there are constraints
        $sqlQueries[] = 'DELETE FROM `' . Config::DBPREFIX . 'access_rel_user_group`'
            . ' WHERE user_id' . $whereCondition . ';';
        $sqlQueries[] = 'DELETE FROM `' . Config::DBPREFIX . 'access_user_attribute_value`'
            . ' WHERE user_id' . $whereCondition . ';';
        $sqlQueries[] = 'DELETE FROM `' . Config::DBPREFIX . 'access_user_profile`'
            . ' WHERE user_id' . $whereCondition . ';';
        $sqlQueries[] = 'DELETE FROM `' . Config::DBPREFIX . 'access_users`'
            . ' WHERE id' . $whereCondition . ';';
        $sqlQueries[] = 'DELETE FROM `' . Config::DBPREFIX . 'module_newsletter_access_user`'
            . ' WHERE accessUserID' . $whereCondition . ';';
        return join("\n", $sqlQueries);
    }

}
