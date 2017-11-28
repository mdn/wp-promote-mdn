<?php

// ** MySQL settings ** /** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress_unit_tests' );
if ( strpos( getcwd(), 'VVV' ) === false ) {
	/** MySQL database username */
	define( 'DB_USER', 'root' );
	/** MySQL database password */
	define( 'DB_PASSWORD', '' );
	/** MySQL hostname */
	define( 'DB_HOST', '127.0.0.1' );
} else {
	/** MySQL database username */
	define( 'DB_USER', 'wp' );
	/** MySQL database password */
	define( 'DB_PASSWORD', 'wp' );
	/** MySQL hostname */
	define( 'DB_HOST', '192.168.50.4' );
}
/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );
/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );
$table_prefix = 'wp_';
