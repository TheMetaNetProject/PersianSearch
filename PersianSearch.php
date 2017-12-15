<?php
# Alert the user that this is not a valid access point to MediaWiki if
# they try to access the special pages file directly.
if ( !defined( 'MEDIAWIKI' ) ) {
    echo <<<EOT
To install my extension, put the following line in LocalSettings.php:
require_once( "\$IP/extensions/PersianSearch/PersianSearch.php" );
EOT;
    exit( 1 );
}
 
$wgExtensionCredits[ 'specialpage' ][] = array(
        'path' => __FILE__,
        'name' => 'PersianSearch',
        'author' => 'Jisup Hong',
        'url' => 'https://metaphor.icsi.berkeley.edu/metaphor',
        'descriptionmsg' => 'persiansearch-desc',
        'version' => '0.0.1',
);
$mnIP = dirname( __FILE__ );
$wgExtensionMessagesFiles[ 'PersianSearch' ] = $mnIP . '/PersianSearch.i18n.php'; # Location of a messages file (Tell MediaWiki to load this file)
$wgExtensionMessagesFiles[ 'PersianSearchAlias' ] = $mnIP . '/PersianSearch.alias.php'; # Location of an aliases file (Tell MediaWiki to load this file)

$wgSpecialPages[ 'PersianSearch' ] = 'SpecialPersianSearch'; # Tell MediaWiki about the new special page and its class name
$wgSpecialPageGroups[ 'PersianSearch' ] = 'other';

$wgAutoloadClasses[ 'SpecialPersianSearch' ] = $mnIP . '/SpecialPersianSearch.php'; # Location of the SpecialMyExtension class (Tell MediaWiki to load this file)
