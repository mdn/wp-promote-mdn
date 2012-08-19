<?php
/*
Plugin Name: Promote MDN
Version: 1.2.0
Plugin URI: http://github.com/groovecoder/wp-promote-mdn
Author: Luke Crouch
Author URI: http://groovecoder.com
Description: Promote MDN automatically links keywords phrases to MDN docs
Text Domain: promote-mdn
*/

// Avoid name collisions.
if ( !class_exists( 'PromoteMDN' ) ) :

class PromoteMDN {
    public $option_name = 'PromoteMDN';
    public $options;
    public $default_options = array(
        'excludeheading' => 'on',
        'ignore' => 'about,',
        'ignorepost' => 'contact,',
        'maxlinks' => 3,
        'maxsingle' => 1,
        'customkey' => '',
        'customkey_url' => 'https://developer.mozilla.org/en-US/docs/Template:Promote-MDN?raw=1',
        'customkey_url_expire' => 86400,
        'blanko' => 'on',
        'allowfeed' => '',
        'maxsingleurl' => '1',
    );

    function __construct($options = null)
    {
        if ( $options )
            $this->options = $options;
        else
            $this->options = get_option( $this->option_name );

        // WordPress hooks
        add_filter( 'the_content' ,  array( &$this, 'process_text' ), 10 );
        add_action( 'admin_menu' ,  array( &$this, 'admin_menu' ) );

        // Load translated strings
        load_plugin_textdomain( 'promote-mdn', false, 'promote-mdn/languages/' );
    }

    function process_text( $text )
    {
        $options = $this->options;
        $links   = 0;
        if ( is_feed() && !$options['allowfeed'] )
            return $text;

        $arrignorepost = $this->explode_lower_trim( ',' , ( $options['ignorepost'] ) );
        if ( is_page( $arrignorepost ) || is_single( $arrignorepost ) ) {
            return $text;
        }

        $maxlinks     = ( $options['maxlinks'] > 0 ) ? $options['maxlinks'] : 0;
        $maxsingle    = ( $options['maxsingle'] > 0 ) ? $options['maxsingle'] : -1;
        $maxsingleurl = ( $options['maxsingleurl'] > 0 ) ? $options['maxsingleurl'] : 0;

        $urls = array();

        $arrignore = $this->explode_lower_trim( ',' , ( $options['ignore'] ) );
        if ( $options['excludeheading'] == 'on' ) {
            //Here insert special characters
            $text = preg_replace( '%(<h.*?>)(.*?)(</h.*?>)%sie' , "'\\1'.wp_insertspecialchars('\\2').'\\3'" , $text );
        }

        $reg_post = '/(?!(?:[^<\[]+[>\]]|[^>\]]+<\/a>))($name)/imsU';
        $reg      = '/(?!(?:[^<\[]+[>\]]|[^>\]]+<\/a>))\b($name)\b/imsU';
        $text     = " $text ";

        if ( !empty( $options['customkey_url'] ) )
        {
            if ( false === ( $customkey_url_value = get_transient( 'promote_mdn_url_value' ) ) ){
                $customkey_url_value = $this->reload_value( $options['customkey_url'] );
            }
            $options['customkey'] = $options['customkey'] . "\n" . $customkey_url_value;
        }
        // custom keywords
        if ( !empty( $options['customkey'] ) ) {
            $kw_array = array();
            foreach ( explode( "\n" , $options['customkey'] ) as $line ) {
                $chunks = array_map( 'trim', explode( ',' , $line ) );
                $total_chuncks = count( $chunks );
                if ( $total_chuncks > 2 ) {
                    $i   = 0;
                    $url = $chunks[$total_chuncks - 1];
                    while ( $i < $total_chuncks - 1 ) {
                        if ( !empty( $chunks[$i] ) ) $kw_array[$chunks[$i]] = $url;
                            $i++;
                    }
                } else {
                        list( $keyword, $url ) = array_map( 'trim', explode( ',' , $line, 2 ) );
                        if ( !empty( $keyword ) ) $kw_array[$keyword] = $url;
                }
            }
            foreach ( $kw_array as $name => $url )
            {
                if ( in_array( strtolower( $name ), $arrignore ) )
                    continue;
                if (   ( !$maxlinks || ( $links < $maxlinks ) )
                    && ( trailingslashit( $url ) != $thisurl )
                    && ( !in_array( strtolower( $name ), $arrignore ) )
                    && ( !$maxsingleurl || $urls[$url] < $maxsingleurl ) ) {
                   if (
                       ( $options['customkey_preventduplicatelink'] == TRUE ) || stripos( $text, $name ) !== false ) {
                        $name = preg_quote( $name, '/' );

                        if( $options['customkey_preventduplicatelink'] == TRUE ) $name = str_replace( ',' , '|' , $name );

                        if( $options['blanko'] )
                            $target = 'target="_blank"';
                        $replace = "<a $target title=\"$1\" href=\"$url\">$1</a>";
                        $regexp  = str_replace( '$name', $name, $reg );
                        //$regexp="/(?!(?:[^<]+>|[^>]+<\/a>))(?<!\p{L})($name)(?!\p{L})/imsU";
                        $newtext = preg_replace( $regexp, $replace, $text, $maxsingle );
                        if ( $newtext != $text ) {
                            $links++;
                            $text = $newtext;
                            if ( !isset( $urls[$url] ) ) $urls[$url] = 1; else $urls[$url]++;
                        }
                    }
                }
            }
        }


        if ( $options['excludeheading'] == 'on' ) {
            //Here insert special characters
            $text = preg_replace( '%(<h.*?>)(.*?)(</h.*?>)%sie', "'\\1'.wp_removespecialchars('\\2').'\\3'", $text );
            $text = stripslashes( $text );
        }
        return trim( $text );

    }


    function reload_value( $url )
    {
        $body = wp_remote_retrieve_body(
            wp_remote_get(
                $url,
                array(
                    'headers' =>
                        array( 'cache-control' => 'no-cache, must-revalidate' ) 
                )
            )
        );
        $customkey_url_value = strip_tags( $body );
        set_transient( 'promote_mdn_url_value', $customkey_url_value, 86400 );
        return $customkey_url_value;
    }

    function explode_lower_trim( $separator, $text )
    {
        $arr = explode( $separator, $text );

        $ret = array();
        foreach ( $arr as $e )
        {
          $ret[] = strtolower( trim( $e ) );
        }
        return $ret;
    }


    function handle_options()
    {
        $options = $this->options;
        if ( isset( $_POST['submitted'] ) ) {
            check_admin_referer( 'seo-smart-links' );

            if ( isset( $_POST['reload_now'] ) ) {
                $customkey_url       = stripslashes( $options['customkey_url'] );
                $customkey_url_value = $this->reload_value( $customkey_url );
                $reloaded_message    = __( 'Reloaded values from the URL.', 'promote-mdn' );
                $message_box         = '<div class="updated fade"><p>' . $reloaded_message . '</p></div>';
                echo $message_box;
            } else {
                $options['excludeheading']       = $_POST['excludeheading'];
                $options['ignore']               = $_POST['ignore'];
                $options['ignorepost']           = $_POST['ignorepost'];
                $options['maxlinks']             = (int) $_POST['maxlinks'];
                $options['maxsingle']            = (int) $_POST['maxsingle'];
                $options['maxsingleurl']         = (int) $_POST['maxsingleurl'];
                $options['customkey']            = $_POST['customkey'];
                $options['customkey_url']        = $_POST['customkey_url'];
                $options['customkey_url_expire'] = $_POST['customkey_url_expire'];
                $options['blanko']               = $_POST['blanko'];
                $options['allowfeed']            = $_POST['allowfeed'];

                update_option( $this->PromoteMDN_DB_option, $options );
                $settings_message = __( 'Plugin settings saved.', 'promote-mdn' );
                echo '<div class="updated fade"><p>' . $settings_message . '</p></div>';
            }
        }

        $action_url = $_SERVER['REQUEST_URI'];

        $comment = $options['comment'] == 'on' ? 'checked' : '';
        $excludeheading = $options['excludeheading'] == 'on' ? 'checked' : '';
        $ignore = $options['ignore'];
        $ignorepost = $options['ignorepost'];
        $maxlinks = $options['maxlinks'];
        $maxsingle = $options['maxsingle'];
        $maxsingleurl = $options['maxsingleurl'];
        $customkey = stripslashes( $options['customkey'] );
        $customkey_url = stripslashes( $options['customkey_url'] );
        $customkey_url_expire = stripslashes( $options['customkey_url_expire'] );
        $blanko = $options['blanko'] == 'on' ? 'checked' : '';
        $allowfeed = $options['allowfeed'] == 'on' ? 'checked' : '';

        $nonce = wp_create_nonce( 'seo-smart-links' );
?>
<style type="text/css">
    #mainblock { width:600px; }
    #logo { float: right; margin-bottom: 1em; }
    .full-width { width: 100% }
    input { padding: .5em; }
    h4 { color: white; background: black; clear: both; padding: .5em; }
    pre { margin-bottom: -1em; }
</style>

<div class="wrap">
     <div id="mainblock">
        <div class="dbx-content">

<?php
        $top_img_title = __( 'MDN is your Web Developer Toolbox for docs, demos and more on HTML, CSS, JavaScript and other Web standards and open technologies.' , 'promote-mdn' );
?>
        <a href="https://developer.mozilla.org/web/?WT.mc_id=mdn37" title="<?php echo $top_img_title ?>"><img src="https://developer.mozilla.org/media/img/promote/promobutton_mdn37.png" id="logo" alt="<?php echo $top_img_title ?>" /></a>
        <p><?php _e( 'MDN is the best online resource - for web developers, by web developers.', 'promote-mdn' ) ?> </p>
        <p><?php _e( 'Promote MDN automatically links keywords and phrases in your posts and pages to MDN URLs.' , 'promote-mdn' ) ?></p>

        <form name="PromoteMDN" action="<?php echo $action_url ?>" method="post">
        <input type="hidden" id="_wpnonce" name="_wpnonce" value="<?php echo $nonce ?>" />
                <input type="hidden" name="submitted" value="1" />


                <h4><?php _e( 'Settings' , 'promote-mdn' ) ?></h4>
                <p><?php _e( 'Load keywords from URL' , 'promote-mdn' ) ?> (<em id="preview"><a href="<?php echo $customkey_url ?>" target="_blank"><?php _e( 'Preview' , 'promote-mdn' ) ?></a></em>):
                <input type="text" name="customkey_url" value="<?php echo $customkey_url ?>" class="full-width" />
                <?php _e( 'Reload keywords after (seconds):' , 'promote-mdn' ) ?> <input type="text" name="customkey_url_expire" size="10" value="<?php echo $customkey_url_expire ?>"/>
                <button type="submit" name="reload_now"><?php _e( 'Reload now' , 'promote-mdn' ) ?></button>
                </p>
                <input type="checkbox" name="allowfeed" <?php echo $allowfeed ?>/> <label for="allowfeed"><?php _e( 'Add links to RSS feeds' , 'promote-mdn' ) ?></label><br/>
                <input type="checkbox" name="blanko" <?php echo $blanko ?>/> <label for="blanko"><?php _e( 'Open links in new window' , 'promote-mdn' ) ?></label> <br/>


                <h4><?php _e( 'Exceptions' , 'promote-mdn' ) ?></h4>
                <input type="checkbox" name="excludeheading" <?php echo $excludeheading ?>/> <label for="excludeheading"><?php _e( 'Do not add links in heading tags (h1,h2,h3,h4,h5,h6).' , 'promote-mdn' ) ?></label><br/>
                <p><?php _e( 'Do not add links to the following posts or pages (comma-separated id, slug, name):' , 'promote-mdn' ) ?></p>
                <input type="text" name="ignorepost" value="<?php echo $ignorepost ?>" class="full-width"/>
                <p><?php _e( 'Do not add links on the following phrases (comma-separated):' , 'promote-mdn' ) ?></p>
                <input type="text" name="ignore" class="full-width" value="<?php echo $ignore ?>"/>


                <h4><?php _e( 'Limits' , 'promote-mdn' ) ?></h4>
                <?php _e( 'Max links to generate per post:' , 'promote-mdn' ) ?> <input type="text" name="maxlinks" size="2" value="<?php echo $maxlinks ?>"/><br/>
                <?php _e( 'Max links to generate for a single keyword/phrase:' , 'promote-mdn' ) ?> <input type="text" name="maxsingle" size="2" value="<?php echo $maxsingle ?>"/><br/>
                <?php _e( 'Max links to generate for a single URL:' , 'promote-mdn' ) ?> <input type="text" name="maxsingleurl" size="2" value="<?php echo $maxsingleurl ?>"/>


                <h4><?php _e( 'Custom Keywords' , 'promote-mdn' ) ?></h4>
                <p><?php _e( 'Extra keywords to automaticaly link. Use comma to seperate keywords and add target url at the end. Use a new line for new url and set of keywords. e.g.,' , 'promote-mdn' ) ?><br/>
                <pre>addons, amo, http://addons.mozilla.org/
sumo, http://support.mozilla.org/
                </pre>
                </p>

                <textarea name="customkey" id="customkey" rows="10" cols="90"  ><?php echo $customkey ?></textarea>
                <em><?php _e( 'Note: These keywords will take priority over those loaded at the URL. If you have too many custom keywords here, you may not link to MDN at all.' , 'promote-mdn' ) ?></em>
                <div class="submit"><input type="submit" name="Submit" value="<?php _e( 'Update options' , 'promote-mdn' ) ?>" class="button-primary" /></div>
            </form>

        </div>
    </div>
</div>
<?php

    }

    function admin_menu()
    {
        add_options_page( 'Promote MDN Options', 'Promote MDN', 8, basename( __FILE__ ), array( &$this, 'handle_options' ) );
    }

    // Set up everything
    function install()
    {
        $options = get_option( $this->option_name );
        if (!$options)
            update_option( $this->option_name, $this->default_options );
    }
}

endif;

if ( class_exists( 'PromoteMDN' ) ) :
    $in_phpunit = false;
    if ( array_key_exists( 'argv', $GLOBALS ) ) {
        foreach ($GLOBALS['argv'] as $arg) {
            if ( stripos( $arg, 'phpunit' ) !== false )
                $in_phpunit = true;
        }
    }
    if ( !$in_phpunit ) {
        $PromoteMDN = new PromoteMDN();
        if ( isset( $PromoteMDN ) ) {
            register_activation_hook( __FILE__, array( &$PromoteMDN, 'install' ) );
        }
    }
endif;

function wp_insertspecialchars( $str ) {
    $strarr = wp_str2arr( $str );
    $str    = implode( '<!---->', $strarr );
    return $str;
}
function wp_removespecialchars( $str ) {
    $strarr = explode( '<!---->', $str );
    $str    = implode( '', $strarr );
    $str    = stripslashes( $str );
    return $str;
}
function wp_str2arr( $str ) {
    $chararray = array();
    for ( $i = 0; $i < strlen( $str ); $i++ ) {
        array_push( $chararray,$str{$i} );
    }
    return $chararray;
}
