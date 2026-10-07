<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress_cuoiki' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         ':|EexAB^@_34N8,.e;j[~~p!~DiLq?X^y{W)C}&.!~A0R|m-|[5=}F:7-M/gqxvV' );
define( 'SECURE_AUTH_KEY',  'cJcbUm5ehv+],vQx4gOnHhlbHIeCxl%n;!0C[LtWQ6~H8!d^-g@raEHMOh[BmSP@' );
define( 'LOGGED_IN_KEY',    '_RZqKexn(36=CyX`*GziK2Ve6/%zthx.[g$!>A<4Kfx+;cB@u-FfXk;kjGY.8)3P' );
define( 'NONCE_KEY',        'peF#tg7!h3KQkc=d+}HP^Lwx}tBv1jktmSL:BD:jY @_QoWF1@Qpnb!g[DwZHe$8' );
define( 'AUTH_SALT',        'Tht?n#B<7/P[1Er1gmx%4YJ4]|s)iPUM?$()&+zNfCRd`k^thBg9RlC5{=UC|+b>' );
define( 'SECURE_AUTH_SALT', 'EAiWR_CoJb`GC+e,_Cx(EaPN2SQTi39sK]#Gu>/k<`LgQ*tYZA8+y59zNo#Go772' );
define( 'LOGGED_IN_SALT',   '^_7,!C%F)|ivIzfX%j/=Dt:b2Oej5>!pDhEfRE4;t?w<z>Xt+FC=fBUj<bvi>4m<' );
define( 'NONCE_SALT',       'I_v:Lz!GXEc9mOC@aS!7`ah-LxRi^OMt>DB58l?<EEl|Ct,`6DO}TQ:oj4us`I(}' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
