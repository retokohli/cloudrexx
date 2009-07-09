<?php
/**
 * Installer language file
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author        Comvation Development Team <info@comvation.com>
 * @version       1.0.0
 * @package     contrexx
 * @subpackage  installer
 * @todo        Edit PHP DocBlocks!
 */

// help
$_ARRLANG['TXT_HELP_MSG'] = "En cas de problème d'installation de [NAME], vous avez les possibilités suivantes:<br /><ul><li>Chercher de l'aide dans le [FORUM]</li><li>Chercher dans [SUPPORT]</li><li>Nous envoyer un E-mail à l'adresse [EMAIL] avec une description du problème et les données de [PHPINFO]</li></ul>";
$_ARRLANG['TXT_PHP_INFO'] = "Configuration PHP";
$_ARRLANG['TXT_FORUM'] = "Forum";
$_ARRLANG['TXT_SUPPORT'] = "Support";

// titles
$_ARRLANG['TXT_REQUIREMENTS'] = "Configuration requise";
$_ARRLANG['TXT_LICENSE'] = "Contrat de licence";
$_ARRLANG['TXT_CONFIGURATION'] = "Configuration";
$_ARRLANG['TXT_INSTALLATION'] = "Installation";
$_ARRLANG['TXT_SYSTEM_CONFIGURATION'] = "Configuration système";
$_ARRLANG['TXT_ADMIN_ACCOUNT'] = "Compte administrateur";
$_ARRLANG['TXT_TERMINATION'] = "Terminer";
$_ARRLANG['TXT_HELP'] = "Aide";

// welcome
$_ARRLANG['TXT_WELCOME'] = "Bienvenue";
$_ARRLANG['TXT_WELCOME_MSG'] = "<b>Bienvenue dans le programme d'installation de Contrexx</b><br />Vous êtes sur le point d'installer [EDITION] Version [VERSION] de [NAME].";
$_ARRLANG['TXT_LANGUAGE'] = "Langue";
$_ARRLANG['TXT_NEW_VERSION'] = "Une version plus récente de [NAME] a été publiée.<br />Dernière version disponible: [VERSION]";

// general
$_ARRLANG['TXT_NEXT'] = "Suite";
$_ARRLANG['TXT_BACK'] = "Retour";
$_ARRLANG['TXT_CANCEL'] = "Annuler";
$_ARRLANG['TXT_STOP'] = "Interrompre";
$_ARRLANG['TXT_USERNAME'] = "Identifiant (nom utilisateur)";
$_ARRLANG['TXT_PASSWORD'] = "Mot de passe";
$_ARRLANG['TXT_GENERAL'] = "Général";
$_ARRLANG['TXT_FILL_OUT_ALL_FIELDS'] = "Veuillez compléter tous les champs!";
$_ARRLANG['TXT_FTP_PASSIVE_MODE_FAILED'] = "Impossible de passer en mode passif!";

// license
$_ARRLANG['TXT_READ_LICENCE'] = "Veuillez lire/accepter le contrat de licence pour continuer l'installation";
$_ARRLANG['TXT_MUST_ACCEPT_LICENCE'] = "Vous devez accepter le contrat de licence pour pouvoir continuer l'installation!";
$_ARRLANG['TXT_ACCEPT_LICENSE'] = "J'accepte le contrat de licence Contrexx";

// requirements
$_ARRLANG['TXT_SOFTWARE_REQUIREMENTS'] = "Configuration logicielle requise";
$_ARRLANG['TXT_PHP'] = "PHP";
$_ARRLANG['TXT_PHP_VERSION'] = "PHP Version";
$_ARRLANG['TXT_MYSQL_SERVER_VERSION'] = "La version du serveur MySQL est: %s";
$_ARRLANG['TXT_PHP_EXTENSIONS'] = "Extensions PHP";
$_ARRLANG['TXT_PHP_CONFIGURATION'] = "Configuration PHP";
$_ARRLANG['TXT_ALLOW_URL_FOPEN'] = "allow_url_fopen";
$_ARRLANG['TXT_GD_VERSION']	= "GD (Graphics Draw) Version";
$_ARRLANG['TXT_FTP_SUPPORT'] = "Support FTP";
$_ARRLANG['TXT_YES'] = "Oui";
$_ARRLANG['TXT_NO'] = "Non";
$_ARRLANG['TXT_ON']	= "On";
$_ARRLANG['TXT_OFF'] = "Off";
$_ARRLANG['TXT_PHP_VERSION_REQUIRED'] = "L'installation requière au minimum la version [VERSION] de PHP!";
$_ARRLANG['TXT_MYSQL_SUPPORT'] = "Support MySQL";
$_ARRLANG['TXT_MYSQL_SUPPORT_REQUIRED'] = "L'installatino requière l'extension MySQL de PHP!";
$_ARRLANG['TXT_MYSQL_VERSION_REQUIRED'] = "L'installation requière un serveur MySQL en version au minimum [VERSION]!";
$_ARRLANG['TXT_GD_VERSION_REQUIRED'] = "L'installation requière au minimum la version [VERSION] de l'extension GD de PHP!";
$_ARRLANG['TXT_ALLOW_URL_FOPEN_FOR_RSS_REQUIRED'] = "Le module <i>Syndication aux flux de nouvelles</i> ne fonctionnera pas tant que la directive \"allow_url_fopen\" de la configuration PHP sera désactivée!";
$_ARRLANG['TXT_FTP_SUPPORT_REQUIRED'] = "Le support FTP pour PHP est indispensable à l'installation automatique (PHP fonctionne en Safemode). Hors, elle est interdite sur ce serveur. Veuillez devez soit modifier la configuration PHP avec l'option ('--enable-ftp'), soit installer le système manuellement.";
$_ARRLANG['TXT_IGNORE_PHP_REQUIREMENT'] = "Vous pouvez malgré tout installer Contrexx&reg; sous votre propre responsabilité, mais, dans ce cas, l'éditeur décline toute responsabilité en cas de dysfonctionnement.";
$_ARRLANG['TXT_ACCEPT_NO_SLA'] = "Installer malgré tout Contrexx&reg; sous ces conditions";

// configuration
$_ARRLANG['TXT_FTP_PATH_CONFIG'] = "Configuration du chemin FTP";
$_ARRLANG['TXT_DOCUMENT_ROOT_DESCRIPTION'] = "Dossier racine, où votre serveur stocke les fichiers.";
$_ARRLANG['TXT_DOCUMENT_ROOT'] = "Dossier racine";
$_ARRLANG['TXT_OFFSET_PATH'] = "Dossier relatif";
$_ARRLANG['TXT_OFFSET_PATH_DESCRIPTION'] = "Saisir ici l'emplacement relatif par rapport au dossier racine, où vous avez déposé les fichiers [NAME].";
$_ARRLANG['TXT_DATABASE'] = "Base de donnée MySQL";
$_ARRLANG['TXT_HOSTNAME'] = "Serveur";
$_ARRLANG['TXT_DATABASE_NAME'] = "Nom de la base de données";
$_ARRLANG['TXT_TABLE_PREFIX'] = "Préfixe des tables";
$_ARRLANG['TXT_FTP_PATH'] = "Chemin du dossier racine";
$_ARRLANG['TXT_FTP_PATH_DESCRIPTION'] = "Saisir ici l'emplacement du serveur FTP où vous avez déposé les fichiers du CMS Contrexx.";
$_ARRLANG['TXT_FTP'] = "FTP";
$_ARRLANG['TXT_DOCUMENT_ROOT_NEEDED'] = "Veuillez svp indiquer le dossier racine  des fichiers Contrexx sur votre serveur Web!";
$_ARRLANG['TXT_DB_HOSTNAME_NEEDED'] = "Veuillez svp indiquer le nom du serveur de la base de données!";
$_ARRLANG['TXT_DB_USERNAME_NEEDED'] = "Veuillez svp saisir un nom d'utilisateur valide, avec lequel vous accéderez à la base de données!";
$_ARRLANG['TXT_DB_DATABASE_NEEDED'] = "Veuillez svp saisir un nom de base de donnée, existante ou à créer!";
$_ARRLANG['TXT_DB_TABLE_PREFIX_NEEDED'] = "Veuillez svp indiquer un préfixe de votre choix pour les noms de tables de la base de données!";
$_ARRLANG['TXT_FTP_HOSTNAME_NEEDED'] = "Veuillez svp indiquer le nom du serveur FTP!";
$_ARRLANG['TXT_FTP_USERNAME_NEEDED'] = "Veuillez svp indiquer un nom d'utilisateur ayant les accès au serveur FTP!";
$_ARRLANG['TXT_USE_FTP'] = "Utiliser FTP";
$_ARRLANG['TXT_PATH_DOES_NOT_EXIST'] = "Le dossier \"[PATH]\" n'existe pas!";
$_ARRLANG['TXT_CANNOT_FIND_FIlE'] = "Le fichier \"[FILE]\" n'existe pas dans le dossier du CMS!";
$_ARRLANG['TXT_DIRECTORY_ON_FTP_DOES_NOT_EXIST'] = "Le dossier \"[DIRECTORY]\" n'existe pas sur le serveur FTP!";
$_ARRLANG['TXT_FILE_ON_FTP_DOES_NOT_EXIST'] = "Le fichier \"[FILE]\" n'existe pas sur le serveur FTP!";
$_ARRLANG['TXT_USE_PASSIVE_FTP'] = "Mode passif";
$_ARRLANG['TXT_FTP_DESCRIPTION'] = "Avec cette option, les opérations sur les fichiers seront effectuées via le protocole FTP. Ce protocole est obligatoire pour une installation sur un système Unix ou similaire sur lequel PHP fonctionne en Safemode.";
$_ARRLANG['TXT_DB_TABLE_PREFIX_INVALID'] = "Le préfixe des tables ne peut être composé que de caractères alphanumériques (a-z/A-Z/0-9) et du caractère: _";
$_ARRLANG['TXT_OPEN_BASEDIR_TMP_MISSING'] = "Impossible d'afficher la structure des dossiers sur ce serveur, car la Direktive PHP open_basedir est active, mais ne contient pas le chemin du dossier temporaire Temp (/tmp)!";
$_ARRLANG['TXT_DATABASE_CONNECTION_COLLATION'] = "Jeu de caractères";
$_ARRLANG['TXT_DB_COLLATION_DESCRIPTION'] = "Le jeu de caractères de la connexion MySQL, utile pour l'ordre de tri et les recherches.<br  /><br />Si vous désirez publier votre site en plusieurs langues, il est conseillé d'utiliser soit <strong>utf8_unicode_ci</strong>, soit <strong>utf8_general_ci</strong>, le premier supportant les extensions, tandis que le deuxième est plus rapide.<br /><br />Si votre site est publié dans une seule langue, vous pouvez utiliser un jeu de caractères spécifique.";
$_ARRLANG['TXT_REGISTER_GLOBALS_ON'] = "La directive <a href=\"http://www.php.net/manual/fr/ini.core.php#ini.register-globals\" title=\"register_globals PHP documentation\">register_globals</a> est activée. Il est conseill� de désactiver cette PHP directive.";

// installation
$_ARRLANG['TXT_COULD_NOT_DEACTIVATE_UNUSED_LANGUAGES'] = "Impossible de désactiver les langues inutilisées!";
$_ARRLANG['TXT_COULD_NOT_ACTIVATE_DEFAULT_LANGUAGE'] = "Impossible d'activer la langue standard!";
$_ARRLANG['TXT_COULD_NOT_CHANGE_PERMISSIONS'] = "Impossible de modifier les droits d'accès: ";
$_ARRLANG['TXT_CANNOT_OPEN_FILE'] = "Impossible d'ouvrir le fichier [FILENAME]";
$_ARRLANG['TXT_CANNOT_CREATE_FILE'] = "Impossible de créer le fichier %s";
$_ARRLANG['TXT_CANNOT_CONNECT_TO_DB_SERVER'] = "Impossible d'établir la connexion au serveur de base de données!";
$_ARRLANG['TXT_DATABASE_ALREADY_EXISTS'] = "Il existe déjà une base de données avec le nom \"[DATABASE]\"!";
$_ARRLANG['TXT_DATABASE_DOES_NOT_EXISTS'] = "La base de données \"[DATABASE]\" n'existe pas!";
$_ARRLANG['TXT_COULD_NOT_CREATE_DATABASE'] = "Impossible de créer la base de données!";
$_ARRLANG['TXT_CANNOT_CONNECT_TO_FTP_HOST'] = "Impossible d'établir une connexion au serveur FTP!";
$_ARRLANG['TXT_FTP_AUTH_FAILED'] = "Authentification avec ces identifiants refusée par le serveur FTP!";
$_ARRLANG['TXT_FTP_PATH_DOES_NOT_EXISTS'] = "Le chemin du dossier indiqué pour le CMS Contrexx n'existe pas sur le serveur FTP!";
$_ARRLANG['TXT_COULD_NOT_READ_SQL_DUMP_FILE'] = "Impossible d'ouvrir le fichier Dump SQL \"[FILENAME]\"!";
$_ARRLANG['TXT_SQL_QUERY_ERROR'] = "Erreur de la requête SQL: ".
$_ARRLANG['TXT_CORRECT_THE_FOLLOWING_ERROR'] = "Veuillez corriger les erreurs suivantes, puis actualiser cette page pour recommencer l'installation:";
$_ARRLANG['TXT_SET_PERMISSIONS'] = "Droits d'accès affectés";
$_ARRLANG['TXT_SUCCESSFULLY'] = "Réussi";
$_ARRLANG['TXT_FAILED'] = "Echec";
$_ARRLANG['TXT_CREATE_DATABASE'] = "Créer la base de données";
$_ARRLANG['TXT_DATABASE_CREATED'] = "Base de donnée crée";
$_ARRLANG['TXT_CREATE_DATABASE_TABLES'] = "Tables créées";
$_ARRLANG['TXT_INSERT_DATABASE_DATA'] = "Données démo créées";
$_ARRLANG['TXT_TABLE_NOT_AVAILABLE'] = "La table \"[TABLE]\" n'existe pas";
$_ARRLANG['TXT_CREATE_DATABAES_TABLE_MANUALLY'] = "Veuillez créer la(les) table(s) manuellement et introduire le contenu à l'aide du <a href=\"[FILEPATH]\" title=\"Fichier SQL\">fichier SQL</a>!";
$_ARRLANG['TXT_CHECK_DATABASE_TABLES'] = "Structure de la base de données vérifiée";
$_ARRLANG['TXT_PRESS_REFRESH_TO_CONTINUE_INSTALLATION'] = "Cliquer sur <b>Actualiser</b> pour continuer l'installation!";
$_ARRLANG['TXT_REFRESH'] = "Actualiser";
$_ARRLANG['TXT_CREATE_CONFIG_FILE'] = "Fichier de configuration créé";
$_ARRLANG['TXT_SET_WRITE_PERMISSION_TO_FILES'] = "Vous devez affecter un droit d'écriture sur les fichiers suivants:";
$_ARRLANG['TXT_CREATE_VERSION_FILE'] = "Fichier de version créé";
$_ARRLANG['TXT_COULD_NOT_GATHER_ALL_DATABASE_TABLES'] = "Impossible de détecter toutes les tables!";
$_ARRLANG['TXT_NO_DB_UTF8_SUPPORT_MSG'] = "Le serveur de base de données ne supporte pas UTF-8! Veuillez utiliser une Version de Contrexx avec le jeu de caractère latin1!";
$_ARRLANG['TXT_COULD_NOT_SET_DATABASE_CHARSET'] = "Impossible de paramétrer la collation de la base de données!";
$_ARRLANG['TXT_CONFIG_DATABASE'] = "Configuration de la base de données";

// system configuration
$_ARRLANG['TXT_ADMIN_EMAIL'] = "Adresse E-mail de l'administrateur";
$_ARRLANG['TXT_ADMIN_NAME'] = "Nom de l'administrateur";
$_ARRLANG['TXT_NEWS_SYTEM'] = "Module de nouvelles";
$_ARRLANG['TXT_RSS_TITLE'] = "Titre RSS";
$_ARRLANG['TXT_RSS_DESCRIPTION'] = "Description RSS";
$_ARRLANG['TXT_CONTACT'] = "Formulaire de contact";
$_ARRLANG['TXT_CONTACT_EMAIL'] = "Adresse E-mail";
$_ARRLANG['TXT_COULD_NOT_SET_ADMIN_EMAIL'] = "Impossible de configurer l'adresse E-mail de l'administrateur!";
$_ARRLANG['TXT_COULD_NOT_SET_NEWSLETTER_EMAILS'] = "Impossible de configurer l'adresse E-mail du module de nouvelles!";
$_ARRLANG['TXT_COULD_NOT_SET_NEWSLETTER_SENDER'] = "Impossible de configurer l'expéditeur du module de nouvelles!";
$_ARRLANG['TXT_COULD_NOT_SET_ADMIN_NAME'] = "Impossible de configurer le nom de l'administrateur!";
$_ARRLANG['TXT_COULD_NOT_SET_RSS_TITLE'] = "Impossible de configurer le titre RSS!";
$_ARRLANG['TXT_COULD_NOT_SET_RSS_DESCRIPTION'] = "Impossible de configurer la description RSS!";
$_ARRLANG['TXT_COULD_NOT_SET_CONTACT_EMAIL'] = "Impossible de configurer l'adresse E-mail du formulaire de contact!";
$_ARRLANG['TXT_DOMAIN_URL'] = "URL du domaine";
$_ARRLANG['TXT_DOMAIN_URL_EXPLANATION'] = "Veuillez saisir le nom de domaine sur lequel l'installation a lieu, p.ex. 'www.monsite.com' (sans http(s):// ni dossier supplémentaire)";
$_ARRLANG['TXT_COULD_NOT_SET_DOMAIN_URL'] = "Impossible de configurer le nom de domaine!";
$_ARRLANG['TXT_SET_VALID_DOMAIN_URL'] = "Veuillez saisir le nom de domaine sans http(s):// ni dossier supplémentaire!";
$_ARRLANG['TXT_SETTINGS_ERROR_WRITABLE'] = "Impossible d'écrire dans le fichier %s. Veuillez vérifier les droits d'accès (666) au fichier.";

// admin account
$_ARRLANG['TXT_ADMIN_ACCOUNT_DESC'] = "Veuillez définir les identifiants (nom d'utilisateur et mot de passe) qui permettront de se connecter en tant qu'administrateur.";
$_ARRLANG['TXT_SET_USERNAME'] = "Un nom d'utilisateur est requis!";
$_ARRLANG['TXT_SET_PASSWORD'] = "Un mot de passe est requis!";
$_ARRLANG['TXT_PASSWORD_LENGTH_DESC'] = "(min. 6 caractères)";
$_ARRLANG['TXT_PASSWORD_LENGTH'] = "Le mot de passe doit comporter au minimum 6 caractères!";
$_ARRLANG['TXT_PASSWORD_NOT_VERIFIED'] = "Le mot de passe et sa confirmation ne sont pas identiques!";
$_ARRLANG['TXT_PASSWORD_VERIFICATION'] = "Confirmation du mot de passe";
$_ARRLANG['TXT_EMAIL'] = "Adresse E-mail";
$_ARRLANG['TXT_EMAIL_VERIFICATION'] = "Confirmation de l'adresse E-mail";
$_ARRLANG['TXT_SET_EMAIL'] = "Veuillez saisir une adresse E-mail valable!";
$_ARRLANG['TXT_EMAIL_NOT_VERIFIED'] = "L'adresse E-mail et sa confirmation ne sont pas identiques!";
$_ARRLANG['TXT_PASSWORD_LIKE_USERNAME'] = "Le mot de passe ne peut pas être identique au nom d'utilisateur!";
$_ARRLANG['TXT_CREATE_ADMIN_ACCOUNT'] = "Créer le compte administrateur";
$_ARRLANG['TXT_COULD_NOT_CREATE_ADMIN_ACCOUNT'] = "Impossible de créer le compte administrateur!";

// termination
$_ARRLANG['TXT_CONGRATULATIONS'] = "Félicitations";
$_ARRLANG['TXT_CONGRATULATIONS_MESSAGE'] = "Le CMS Contrexx [VERSION] [EDITION] est correctement installé.";
$_ARRLANG['TXT_INTERNET_SITE_FOR_VISITORS'] = "Adresse Internet pour les visiteurs";
$_ARRLANG['TXT_INTERNET_SITE_MESSAGE'] = "Le site est dès à présent disponible à l'adresse suivante: <br /><b>[WEB_URL]</b>";
$_ARRLANG['TXT_ADMIN_SITE'] = "Console d'administration";
$_ARRLANG['TXT_ADMIN_SITE_MESSAGE'] = "Adresse pour accéder à la console d'administration: <br /><b>[ADMIN_URL]</b>";
?>
