<?php

/**
 * Contrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Comvation AG 2007-2015
 * @version   Contrexx 4.0
 * 
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Contrexx" is a registered trademark of Comvation AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

 /** 
 * @copyright   CONTREXX CMS - COMVATION AG 
 * @author      Comvation Development Team <info@comvation.com>
 * @access      public 
 * @package     contrexx
 * @subpackage  core_databasemanager
 */ 
global $_ARRAYLANG; 
$_ARRAYLANG['TXT_DBM_MAINTENANCE_TITLE'] = 'Bakım';
$_ARRAYLANG['TXT_DBM_STATUS_TITLE'] = 'Durumbilgileri';
$_ARRAYLANG['TXT_DBM_STATUS_MYSQL_VERSION'] = 'MySQL Verziyonu';
$_ARRAYLANG['TXT_DBM_STATUS_USED_TABLES'] = 'Kullanılan Tablolar';
$_ARRAYLANG['TXT_DBM_STATUS_USED_SPACE'] = 'Kullanılan bellek (hafıza)';
$_ARRAYLANG['TXT_DBM_STATUS_BACKOG'] = 'Çıkıntı';
$_ARRAYLANG['TXT_DBM_CONNECTION_TITLE'] = 'Bağlantı bilgileri';
$_ARRAYLANG['TXT_DBM_CONNECTION_DBPREFIX'] = 'Veritabanı-öneki';
$_ARRAYLANG['TXT_DBM_CONNECTION_DATABASE'] = 'Veritabanı-ismi';
$_ARRAYLANG['TXT_DBM_CONNECTION_USERNAME'] = 'Veritabanı-Kullanıcısı';
$_ARRAYLANG['TXT_DBM_STATUS_PHPINFO'] = 'PHP-biligisi';
$_ARRAYLANG['TXT_DBM_MAINTENANCE_OPTIMIZE_DB'] = 'Veritabanını uygun hale getir';
$_ARRAYLANG['TXT_DBM_MAINTENANCE_OPTIMIZE_START'] = 'Optimal (en Uygun duruma) getir!';
$_ARRAYLANG['TXT_DBM_MAINTENANCE_OPTIMIZE_DESC'] = 'Bu fonksiyonla Contrexx-Veritabanını en uyugun duruma getirebilirsiniz. Ama belki boşalan bellek hemen sunulmaya bilir.Sistem böylelikle hızlanır ve üstelik hafıza alanı kazanılmaktadır.Bu işlem belik birkaç saniyeler sürebelir(veritabanına bağlı).Bunun için SIK SIK aralıklarla bu fonksiyonu (optimal) kullanın.';
$_ARRAYLANG['TXT_DBM_MAINTENANCE_REPAIR_DB'] = 'Veritabanı tamir et';
$_ARRAYLANG['TXT_DBM_MAINTENANCE_REPAIR_START'] = 'Tamiri başlat';
$_ARRAYLANG['TXT_DBM_MAINTENANCE_REPAIR_DESC'] = 'Veritabanısunucusunun beklenmedik bir şekilde kapatılması tabloların bozulmasına yol açabilir. Bu durumda sözkonusu tabloların tamir edilmesi gerekir.Böylelikle hasarlar kaldırılmış olur.Problemlerde hatanın veritabanısunucusundan giderilmesi tavsiye edilir';
$_ARRAYLANG['TXT_DBM_MAINTENANCE_TABLES'] = 'Tablolar';
$_ARRAYLANG['TXT_DBM_MAINTENANCE_TABLENAME'] = 'Tabloismi';
$_ARRAYLANG['TXT_DBM_MAINTENANCE_ROWS'] = 'dizi';
$_ARRAYLANG['TXT_DBM_MAINTENANCE_DATA_SIZE'] = 'Veri büyüklüğü';
$_ARRAYLANG['TXT_DBM_MAINTENANCE_INDEX_SIZE'] = 'İçerik (Index) büyüklüğü';
$_ARRAYLANG['TXT_SELECT_ALL'] = 'Tümünü işaretle';
$_ARRAYLANG['TXT_DESELECT_ALL'] = 'İşaretleni sil';
$_ARRAYLANG['TXT_MULTISELECT_SELECT'] = 'İşlem seç';
$_ARRAYLANG['TXT_DBM_SHOW_TABLE_TITLE'] = 'Tablo içeriğini göster';
$_ARRAYLANG['TXT_DBM_MAINTENANCE_OPTIMIZE_DONE'] = 'Veritabanındaki tablolar başariyla uygun duruma getirildi.';
$_ARRAYLANG['TXT_DBM_MAINTENANCE_REPAIR_DONE'] = 'Veritabanındaki tablolar başariyla tamir edildi.';
