<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'bloom_magento');

/** MySQL database username */
define('DB_USER', 'bloom_magento');

/** MySQL database password */
define('DB_PASSWORD', '3UXwcjKK4teyeyAt');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         ']zN5f8/^=?OEtnEjAN.dw@o+9vHqtdKkgE`#[jTF.i+~nUh$Okfo#!UViefn^jO)');
define('SECURE_AUTH_KEY',  '#0Xua;V3=PH/m&{DBJ9iMVbAqR6R= 1UWxj>XVxr?X+TU9+Cplh.jkUhpRqZZrRa');
define('LOGGED_IN_KEY',    '+]8N+Q;sTh!QXJHJl+[Cf976h{7(Y2.o._dkj+!C|HFh%$+uCO*`+>Qr]>Iijk-*');
define('NONCE_KEY',        'gE>J}Y&U`su[b7W6U2ZJQ[-m1k;M+ v!Q-,3/Z`p!`d<5ob,,3pG-^B6bSnxw{J+');
define('AUTH_SALT',        'cF@2.;^JKG:3o@Elk*l$*|<5>5-7M$18)j!sI120C|ni8.Lqzsc7pB]ad*yEK-W0');
define('SECURE_AUTH_SALT', 's18f^nr+v`?3s2@I%-|Bg!.B@u3)BoRg3a~]0qRWs;7Y`$!A,;@2=-uu+T6r0Ngz');
define('LOGGED_IN_SALT',   'FjZ)f}1R-cm5k!zIPz#}Ju+/$~DYbbSG@+R){N4y/;X>KLYnN3A~)v,3y+?^8M{k');
define('NONCE_SALT',       'W@UN`7XY(k&VdP?|&>gf`Q{+c9~w#})%r$CE%03!|L>28r`Qss0n-<M(3%*(@3(t');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
