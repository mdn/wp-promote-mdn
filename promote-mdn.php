<?php
/*
Plugin Name: Promote MDN
Version: 1.0.0
Plugin URI: http://github.com/groovecoder/wp-promote-mdn
Author: Luke Crouch
Author URI: http://groovecoder.com
Description: Promote MDN automatically links keywords phrases to MDN docs
*/

// Avoid name collisions.
if ( !class_exists( 'PromoteMDN' ) ) :

class PromoteMDN {
    var $PromoteMDN_DB_option = 'PromoteMDN';
    var $PromoteMDN_options;

    // Initialize WordPress hooks
    function PromoteMDN()
    {
        add_filter( 'the_content' ,  array( &$this, 'promote_mdn_the_content_filter' ), 10 );
        // Add Options Page
        add_action( 'admin_menu' ,  array( &$this, 'promote_mdn_admin_menu' ) );
    }


    function promote_mdn_process_text( $text )
    {
        $options = $this->get_options();
        $links   = 0;
        if ( is_feed() && !$options['allowfeed'] )
            return $text;

        $arrignorepost = $this->explode_trim( ',' , ( $options['ignorepost'] ) );
        if ( is_page( $arrignorepost ) || is_single( $arrignorepost ) ) {
            return $text;
        }

        $maxlinks     = ( $options['maxlinks'] > 0 ) ? $options['maxlinks'] : 0;
        $maxsingle    = ( $options['maxsingle'] > 0 ) ? $options['maxsingle'] : -1;
        $maxsingleurl = ( $options['maxsingleurl'] > 0 ) ? $options['maxsingleurl'] : 0;

        $urls = array();

        $arrignore = $this->explode_trim( ',' , ( $options['ignore'] ) );
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
                $body = wp_remote_retrieve_body( wp_remote_get( $options['customkey_url'] ) );
                $customkey_url_value = strip_tags( $body );
                set_transient( 'promote_mdn_url_value', $customkey_url_value, 86400 );
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
                if (   ( !$maxlinks || ( $links < $maxlinks ) )
                    && ( trailingslashit( $url ) != $thisurl )
                    && ( !in_array( strtolower( $name ), $arrignore ) )
                    && ( !$maxsingleurl || $urls[$url] < $maxsingleurl ) ) {
                   if (
                       ( $options['customkey_preventduplicatelink'] == TRUE ) || stripos( $text, $name ) !== false ) {
                        $name = preg_quote( $name, '/' );

                        if( $options['customkey_preventduplicatelink'] == TRUE ) $name = str_replace( ',' , '|' , $name );

                        $replace = "<a title=\"$1\" href=\"$url\">$1</a>";
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

    function promote_mdn_the_content_filter( $text )
    {
        $result  = $this->promote_mdn_process_text( $text );
        $options = $this->get_options();
        $link    = parse_url( get_bloginfo( 'wpurl' ) );
        $host    = 'http://' . $link['host'];

        if ( $options['blanko'] )
            $result = preg_replace( '%<a(\s+.*?href=\S(?!' . $host . '))%i', '<a target="_blank"\\1', $result );

        return $result;
    }

    function explode_trim( $separator, $text )
    {
        $arr = explode( $separator, $text );

        $ret = array();
        foreach ( $arr as $e )
        {
          $ret[] = trim( $e );
        }
        return $ret;
    }

    // Handle our options
    function get_options()
    {
     $options = array(
         'excludeheading' => 'on',
         'ignore' => 'about,',
         'ignorepost' => 'contact,',
         'maxlinks' => 3,
         'maxsingle' => 1,
         'customkey' => '',
         'customkey_url' => 'https://developer.mozilla.org/en-US/docs/Template:Promote-MDN?raw=1',
         'customkey_url_expire' => 60 * 60 * 24,
         'blanko' => 'on',
         'allowfeed' => '',
         'maxsingleurl' => '1',
         );

        $saved = get_option( $this->PromoteMDN_DB_option );


         if ( !empty( $saved ) ) {
             foreach ( $saved as $key => $option )
                    $options[$key] = $option;
         }

         if ( $saved != $options )
            update_option( $this->PromoteMDN_DB_option, $options );

         return $options;
    }

    // Set up everything
    function install()
    {
        $PromoteMDN_options = $this->get_options();
    }

    function handle_options()
    {
        $options = $this->get_options();
        if ( isset( $_POST['submitted'] ) ) {
            check_admin_referer( 'seo-smart-links' );

            $options['excludeheading'] = $_POST['excludeheading'];
            $options['ignore'] = $_POST['ignore'];
            $options['ignorepost'] = $_POST['ignorepost'];
            $options['maxlinks'] = (int) $_POST['maxlinks'];
            $options['maxsingle'] = (int) $_POST['maxsingle'];
            $options['maxsingleurl'] = (int) $_POST['maxsingleurl'];
            $options['customkey'] = $_POST['customkey'];
            $options['customkey_url'] = $_POST['customkey_url'];
            $options['customkey_url_expire'] = $_POST['customkey_url_expire'];
            $options['blanko'] = $_POST['blanko'];
            $options['allowfeed'] = $_POST['allowfeed'];

            update_option( $this->PromoteMDN_DB_option, $options );
            echo '<div class="updated fade"><p>Plugin settings saved.</p></div>';
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

        $imgpath = trailingslashit( get_option( 'siteurl' ) ). 'wp-content/plugins/seo-automatic-links/i';
        echo <<<END
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

            <a href="https://developer.mozilla.org/web/?WT.mc_id=mdn37" title="MDN is your Web Developer Toolbox for docs, demos and more on HTML, CSS, JavaScript and other Web standards and open technologies."><img src="https://developer.mozilla.org/media/img/promote/promobutton_mdn37.png" id="logo" alt="MDN is your Web Developer Toolbox for docs, demos and more on HTML, CSS, JavaScript and other Web standards and open technologies." /></a>
            <p>MDN is the best online resource - for web developers, by web developers.</p>
            <p>Promote MDN automatically links keywords and phrases in your posts and pages to MDN URLs.</p>


            <form name="PromoteMDN" action="$action_url" method="post">
                <input type="hidden" id="_wpnonce" name="_wpnonce" value="$nonce" />
                <input type="hidden" name="submitted" value="1" />


                <h4>Settings</h4>
                <p>Load keywords from URL (<em id="preview"><a href="$customkey_url" target="_blank">Preview</a></em>):
                <input type="text" name="customkey_url" value="$customkey_url" class="full-width" />
                Wait <input type="text" name="customkey_url_expire" size="10" value="$customkey_url_expire"/> <label for="customkey_url_expire">seconds between reloading</label>
                </p>
                <input type="checkbox" name="allowfeed" $allowfeed /> <label for="allowfeed">Add links to RSS feeds</label><br/>
                <input type="checkbox" name="blanko" $blanko /> <label for="blanko">Open links in new window</label> <br/>


                <h4>Exceptions</h4>
                <input type="checkbox" name="excludeheading"  $excludeheading/> <label for="excludeheading">Do not add links in heading tags (h1,h2,h3,h4,h5,h6).</label><br/>
                <p>Do not add links to the following posts or pages (comma-separated id, slug, name):</p>
                <input type="text" name="ignorepost" value="$ignorepost" class="full-width"/>
                <p>Do not add links on the following phrases (comma-separated):</p>
                <input type="text" name="ignore" class="full-width" value="$ignore"/>


                <h4>Limits</h4>
                Max links to generate per post: <input type="text" name="maxlinks" size="2" value="$maxlinks"/><br/>
                Max links to generate for a single keyword/phrase: <input type="text" name="maxsingle" size="2" value="$maxsingle"/><br/>
                Max links to generate for a single URL: <input type="text" name="maxsingleurl" size="2" value="$maxsingleurl"/>


                <h4>Custom Keywords</h4>
                <p>Extra keywords to automaticaly link. Use comma to seperate keywords and add target url at the end. Use a new line for new url and set of keywords. e.g.,<br/>
                <pre>addons, amo, http://addons.mozilla.org/
sumo, http://support.mozilla.org/
                </pre>
                </p>

                <textarea name="customkey" id="customkey" rows="10" cols="90"  >$customkey</textarea>
                <em>Note: These keywords will take priority over those loaded at the URL. If you have too many custom keywords here, you may not link to MDN at all.</em>
                <div class="submit"><input type="submit" name="Submit" value="Update options" class="button-primary" /></div>
            </form>

        </div>
    </div>
</div>
END;


    }

    function promote_mdn_admin_menu()
    {
        add_options_page( 'Promote MDN Options', 'Promote MDN', 8, basename( __FILE__ ), array( &$this, 'handle_options' ) );
    }
}

endif;

if ( class_exists( 'PromoteMDN' ) ) :
    $PromoteMDN = new PromoteMDN();
    if ( isset( $PromoteMDN ) ) {
        register_activation_hook( __FILE__, array( &$PromoteMDN, 'install' ) );
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
