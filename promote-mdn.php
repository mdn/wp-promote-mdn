<?php
/*
Plugin Name: Promote MDN
Version: 1.6.0
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
    public $install_options = array(
        'exclude_elems' => 'blockquote, code, h, pre, q, script',
        'ignore' => 'about,',
        'ignorepost' => 'contact,',
        'maxlinks' => 3,
        'maxsingle' => 1,
        'customkey' => '',
        'customkey_url' => 'https://developer.mozilla.org/en-US/docs/Template:Promote-MDN?raw=1',
        'customkey_url_expire' => 86400,
        'blanko' => 'on',
        'add_src_param' => 'on',
        'allowfeed' => '',
        'maxsingleurl' => '1',
        'hide_notices' => array( '1.3' => 1, '1.4' => 1, '1.5' => 1 ),
    );
public $tracking_querystring = '?utm_source=wordpress%%20blog&utm_medium=content%%20link&utm_campaign=promote%%20mdn';

    function __construct( $options = null )
    {
        if ( $options )
            $this->options = $options;
        else {
            $this->options = get_option( $this->option_name );
            // if the options were cleared in the db, reinstall defaults
            if ( $this->options == '' ) {
                update_option( $this->option_name, $this->install_options );
                $this->options = get_option( $this->option_name );
            }
        }

        // WordPress hooks
        add_filter( 'the_content' ,  array( &$this, 'process_text' ), 10 );
        add_action( 'admin_menu' ,  array( &$this, 'admin_menu' ) );
        add_action( 'admin_notices' , array( &$this, 'admin_notices' ) );
        add_action( 'widgets_init', create_function( '', 'register_widget( "PromoteMDN_Widget" );' ) );

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
        $maxsingle    = ( $options['maxsingle'] > 0 ) ? $options['maxsingle'] : 0 - 1;
        $maxsingleurl = ( $options['maxsingleurl'] > 0 ) ? $options['maxsingleurl'] : 0;

        $urls = array();

        $arrignore     = $this->explode_lower_trim( ',' , ( $options['ignore'] ) );
        $exclude_elems = $this->explode_lower_trim( ',', ( $options['exclude_elems'] ) );
        if ( $exclude_elems ) {
            // add salt to elements
            foreach ( $exclude_elems as $el ) {
                $re   = sprintf( '|(<%s.*?>)(.*?)(</%s.*?>)|si', $el, $el );
                $text = preg_replace_callback( $re, create_function( '$matches', 'return $matches[1] . wp_insertspecialchars($matches[2]) . $matches[3];' ), $text );
            }
        }

        $reg_post = '/(?!(?:[^<\[]+[>\]]|[^>\]]+<\/a>))($name)/imsU';
        $reg      = '/(?!(?:[^<\[]+[>\]]|[^>\]]+<\/a>))\b($name)\b/imsU';
        $text     = " $text ";

        if ( !empty( $options['customkey_url'] ) ) {
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
                        $pieces_array = explode( ',' , $line, 2 );
                        if ( count( $pieces_array ) > 1)
                            list( $keyword, $url ) = array_map( 'trim', $pieces_array  );
                        if ( !empty( $keyword ) ) $kw_array[$keyword] = $url;
                }
            }
            foreach ( $kw_array as $name => $url ) {
                if ( in_array( strtolower( $name ), $arrignore ) )
                    continue;
                if (   ( !$maxlinks || ( $links < $maxlinks ) )
                    && ( !in_array( strtolower( $name ), $arrignore ) )
                    && ( !$maxsingleurl || !isset( $urls[$url] ) || $urls[$url] < $maxsingleurl ) 
                   ) {
                       if ( !isset( $options['customkey_preventduplicatelink'] ) )
                            $options['customkey_preventduplicatelink'] = FALSE;
                       if ( $options['customkey_preventduplicatelink'] == TRUE || stripos( $text, $name ) !== false ) {
                        $name = preg_quote( $name, '/' );

                        if ( $options['customkey_preventduplicatelink'] == TRUE ) $name = str_replace( ',' , '|' , $name );

                        $target = '';
                        if ( $options['blanko'] ) {
                            $target = 'target="_blank"';
                        }
                        $href = $url;
                        if ( $options['add_src_param'] == TRUE ) $href .= $this->tracking_querystring;
                        $link = "<a $target title=\"%s\" href=\"$href\">%s</a>";
                        $regexp  = str_replace( '$name', $name, $reg );
                        $replace = 'return sprintf(\'' . $link . '\', $matches[1], $matches[1]);';
                        $newtext = preg_replace_callback( $regexp, create_function( '$matches', $replace ), $text, $maxsingle );
                        if ( $newtext != $text ) {
                            $links++;
                            $text = $newtext;
                            if ( !isset( $urls[$url] ) ) $urls[$url] = 1; else $urls[$url]++;
                        }
                    }
                }
            }
        }

        if ( $exclude_elems ) {
            // remove salt from elements
            foreach ( $exclude_elems as $el ) {
                $re   = sprintf( '|(<%s.*?>)(.*?)(</%s.*?>)|si', $el, $el );
                $text = preg_replace_callback( $re, create_function( '$matches', 'return $matches[1] . wp_removespecialchars($matches[2]) . $matches[3];' ), $text );
            }
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
        $tmp = explode('.', $customkey_url_value);
        $endtmp = end($tmp);
        $customkey_url_value = strip_tags( $body );
        set_transient( 'promote_mdn_url_value', $customkey_url_value, 86400 );
        return $customkey_url_value;
    }

    function explode_lower_trim( $separator, $text )
    {
        $arr = explode( $separator, $text );

        $ret = array();
        foreach ( $arr as $e ) {
          $ret[] = strtolower( trim( $e ) );
        }
        // return empty array for single empty string element
        // for simpler if-checks
        if ( count( $ret ) == 1 && $ret[0] == '' )
            $ret = array();
        return $ret;
    }


    function handle_options()
    {
        $options = $this->options;
        if ( isset( $_POST['submitted'] ) ) {
            check_admin_referer( 'promote-mdn' );

            if ( isset( $_POST['reload_now'] ) ) {
                $customkey_url            = stripslashes( $_POST['customkey_url'] );
                $options['customkey_url'] = $customkey_url;
                $customkey_url_value      = $this->reload_value( $customkey_url );
                $reloaded_message         = __( 'Reloaded values from the URL.', 'promote-mdn' );
                update_option( $this->option_name, $options );
                echo '<div class="updated fade"><p>' . $reloaded_message . '</p></div>';
            } else {
                $options['exclude_elems']        = $_POST['exclude_elems'];
                $options['ignore']               = $_POST['ignore'];
                $options['ignorepost']           = $_POST['ignorepost'];
                $options['maxlinks']             = (int) $_POST['maxlinks'];
                $options['maxsingle']            = (int) $_POST['maxsingle'];
                $options['maxsingleurl']         = (int) $_POST['maxsingleurl'];
                $options['customkey']            = $_POST['customkey'];
                $options['customkey_url']        = $_POST['customkey_url'];
                $options['customkey_url_expire'] = $_POST['customkey_url_expire'];
                $options['blanko']               = $_POST['blanko'];
                $options['add_src_param']        = $_POST['add_src_param'];

            if (isset($options['allowfeed'] ))
                $options['allowfeed']            = $_POST['allowfeed'];

                update_option( $this->option_name, $options );
                $settings_message = __( 'Plugin settings saved.', 'promote-mdn' );
                echo '<div class="updated fade"><p>' . $settings_message . '</p></div>';
            }
        }

        
        

        $action_url = $_SERVER['REQUEST_URI'];

        $checked = '';

        $exclude_elems = $options['exclude_elems'];
        $ignore = $options['ignore'];
        $ignorepost = $options['ignorepost'];
        $maxlinks = $options['maxlinks'];
        $maxsingle = $options['maxsingle'];
        $maxsingleurl = $options['maxsingleurl'];
        $customkey = stripslashes( $options['customkey'] );
        $customkey_url = stripslashes( $options['customkey_url'] );
        $customkey_url_expire = stripslashes( $options['customkey_url_expire'] );
        $blanko = $options['blanko'] == 'on' ? 'checked' : '';
        $add_src_param = $options['add_src_param'] == 'on' ? 'checked' : '';
        $allowfeed = $options['allowfeed'] == 'on' ? 'checked' : '';

        $nonce = wp_create_nonce( 'promote-mdn' );
?>

        <div class="dbx-content">
            <a href="https://developer.mozilla.org/web/?WT.mc_id=mdn37" title="<?php echo __( 'MDN is your Web Developer Toolbox for docs, demos and more on HTML, CSS, JavaScript and other Web standards and open technologies.' , 'promote-mdn' ); ?>">
            <img src="https://developer.cdn.mozilla.net/media/redesign/img/mdn_logo-wordmark-full_color.svg" id="logo" alt="<?php echo __( 'MDN is your Web Developer Toolbox for docs, demos and more on HTML, CSS, JavaScript and other Web standards and open technologies.' , 'promote-mdn' ); ?>" />
            <p><?php _e( 'MDN is the best online resource - for web developers, by web developers.', 'promote-mdn' ); ?> </p>
            <p><?php _e( 'Promote MDN automatically links keywords and phrases in your posts and pages to MDN URLs.' , 'promote-mdn' ); ?></p>
            </a>
          </div>

        <form name="PromoteMDN" action="<?php echo esc_html( $action_url ) ?>" method="post">
        <input type="hidden" id="_wpnonce" name="_wpnonce" value="<?php echo esc_html( $nonce ); ?>" />
                <input type="hidden" name="submitted" value="1" />


                <h4><?php _e( 'Settings' , 'promote-mdn' ) ?></h4>
                <p><?php _e( 'Load keywords from URL:' , 'promote-mdn' ) ?>
                <input type="text" name="customkey_url" id="customkey_url" value="<?php echo esc_html( $customkey_url ); ?>" />
                <a class="button-secondary" id="preview" href="<?php echo esc_html( $customkey_url ) ?>" target="_blank;\"><?php _e( 'Preview' , 'promote-mdn' ) ?></a>
                <a id="use_local_url" class="button-secondary" href="#"  title="<?php echo esc_html( sprintf( __( 'Use keywords and links specifically for %s', 'promote-mdn' ), WPLANG ) ); ?>"><?php echo __( 'Switch to locale-specific list' , 'promote-mdn' ) ?></a><br />
                <?php _e( 'Reload keywords after (seconds):' , 'promote-mdn' ) ?> <input type="text" name="customkey_url_expire" size="10" value="<?php echo esc_html( $customkey_url_expire ) ?>"/>
                <button class="button-secondary" type="submit" name="reload_now" id="reload_now"><?php _e( 'Reload now' , 'promote-mdn' ) ?></button>
                </p>
                <input type="checkbox" name="allowfeed" <?php echo esc_html( $allowfeed ) ?>/> <label for="allowfeed"><?php _e( 'Add links to RSS feeds' , 'promote-mdn' ) ?></label><br/>
                <input type="checkbox" name="add_src_param" <?php echo esc_html( $add_src_param ) ?>/> <label for="add_src_param"><?php _e( 'Include src url params (Helps MDN measure effectiveness)' , 'promote-mdn' ) ?></label> <br/>
                <input type="checkbox" name="blanko" <?php echo esc_html( $blanko ) ?>/> <label for="blanko"><?php _e( 'Open links in new window' , 'promote-mdn' ) ?></label> <br/>


                <h4><?php _e( 'Exceptions' , 'promote-mdn' ) ?></h4>

                <p><?php _e( 'Do not add links inside the following HTML elements (comma-separated, partial-matching):' , 'promote-mdn' ) ?></p>
                <input type="text" name="exclude_elems" value="<?php echo esc_html( $exclude_elems ) ?>" class="full-width"/>
                <p><?php _e( 'Do not add links to the following posts or pages (comma-separated id, slug, name):' , 'promote-mdn' ) ?></p>
                <input type="text" name="ignorepost" value="<?php echo esc_html( $ignorepost ) ?>" class="full-width"/>
                <p><?php _e( 'Do not add links on the following phrases (comma-separated):' , 'promote-mdn' ) ?></p>
                <input type="text" name="ignore" class="full-width" value="<?php echo esc_html( $ignore ) ?>"/>


                <h4><?php _e( 'Limits' , 'promote-mdn' ) ?></h4>
                <?php _e( 'Max links to generate per post:' , 'promote-mdn' ) ?> <input type="text" name="maxlinks" size="2" value="<?php echo esc_html( $maxlinks ) ?>"/><br/>
                <?php _e( 'Max links to generate for a single keyword/phrase:' , 'promote-mdn' ) ?> <input type="text" name="maxsingle" size="2" value="<?php echo esc_html( $maxsingle ) ?>"/><br/>
                <?php _e( 'Max links to generate for a single URL:' , 'promote-mdn' ) ?> <input type="text" name="maxsingleurl" size="2" value="<?php echo esc_html( $maxsingleurl ) ?>"/>


                <h4><?php _e( 'Custom Keywords' , 'promote-mdn' ) ?></h4>
                <p><?php _e( 'Extra keywords to automaticaly link. Use comma to seperate keywords and add target url at the end. Use a new line for new url and set of keywords. e.g.,' , 'promote-mdn' ) ?><br/>
                <pre>addons, amo, http://addons.mozilla.org/
sumo, http://support.mozilla.org/
                </pre>
                </p>

                <textarea class="full-width" name="customkey" id="customkey" rows="10" cols="90"  ><?php echo esc_html( $customkey ) ?></textarea>
                <em><?php _e( 'Note: These keywords will take priority over those loaded at the URL. If you have too many custom keywords here, you may not link to MDN at all.' , 'promote-mdn' ) ?></em>
                <div class="submit"><input type="submit" name="Submit" value="<?php _e( 'Update options' , 'promote-mdn' ) ?>" class="button-primary" /></div>
            </form>

        </div>
    </div>
</div>
<script type="text/javascript">
var localUrlEl = document.getElementById("use_local_url");
localUrlEl.onclick = function() {
    var urlInput = document.getElementById("customkey_url"),
        reloadBtn = document.getElementById("reload_now"),
        re = /([\w-]+)\/docs/;
    urlInput.value = urlInput.value.replace( re, '<?php echo esc_html( str_replace( '_', '-', WPLANG  ) ); ?>/docs' );
    reloadBtn.click();
}
</script>
<?php

    }

    public function get_version_notices()
    {
        return array(
        'new' => sprintf( __( 'Thanks for installing! Go to the <a href="%s">settings</a> page to configure, and the <a href="%s">widgets</a> page to add widget.', 'promote-mdn' ), 'options-general.php?page=promote-mdn.php', 'widgets.php' ),
        '1.3' => sprintf( __( 'fr_FR translation, new sidebar <a href="%s">widget</a>, <a href="%s">setting</a> for a locale-specific URL for keywords.', 'promote-mdn' ) , 'widgets.php', 'options-general.php?page=promote-mdn.php' ),
        '1.4' => sprintf( __( 'You can exclude links from any HTML elements, not just headers; include a src url param on links; text and color options for the <a href="%s">widget</a>', 'promote-mdn' ), 'widgets.php' ),
        '1.5' => __( 'Security fixes.', 'promote-mdn' ),
        '1.6' => sprintf( __( 'You can now notify Mozilla Press and DevRel teams via email when you publish your posts!', 'promote-mdn' ), 'widgets.php' ),
        );
    }

    function admin_menu()
    {
        add_options_page( 'Promote MDN Options', 'Promote MDN', 'manage_options', basename( __FILE__ ), array( &$this, 'handle_options' ) );
    }

    function hide_href( $version ) {
        $param_char = '?';
        if ( isset( $_SERVER['REQUEST_URI'] ) ) {
            if ( strpos( $_SERVER['REQUEST_URI'], '?' ) !== false )
                $param_char = '&';
            return $_SERVER['REQUEST_URI'] . $param_char . 'hide=' . $version;
        } else {
            return 'hide=' . $version;
        }
    }
    
    function register_mdn_styles() {
        wp_register_style( 'promote-mdn', plugins_url( 'wp-promote-mdn/promote-mdn-styles.css' ) );
        wp_enqueue_style( 'promote-mdn' );
    }
    
	function admin_notices() {
        $hide_notices = isset( $this->options['hide_notices']) ? $this->options['hide_notices'] : array();
        if ( isset( $_GET['hide'] ) ) {
            $version = $_GET['hide'];
            $this->options['hide_notices'][$version] = true;
            update_option( $this->option_name, $this->options );
            $hide_notices[$version] = true;
        }
        foreach ( $this->get_version_notices() as $version => $notice ) {
            if ( !array_key_exists( $version, $hide_notices ) ) {
                // overload notice action to call upgrade methods as necessary
                $upgrade_method = 'upgrade_' . str_replace( '.', '', $version );
                if ( method_exists( $this, $upgrade_method ) )
                    $this->$upgrade_method();
?>
<?php
            }
        }
	}

    // Set up everything
    function install()
    {
        $options = get_option( $this->option_name );
        if (!$options)
            update_option( $this->option_name, $this->install_options );
    }

    function upgrade_14()
    {
        $options                  = get_option( $this->option_name );
        $options['exclude_elems'] = 'blockquote, code, h, pre, q';
        $options['add_src_param'] = 'on';
        unset( $options['exclude_heading'] );
        update_option( $this->option_name, $options );
    }
}
endif;

if ( !class_exists( 'PromoteMDN_Widget' ) ) :
    class PromoteMDN_Widget extends WP_Widget {

        public function __construct() {
            parent::__construct(
                'promote_mdn_widget', // Base ID
                __( 'Promote MDN', 'promote-mdn' ), // Name
                array( 'description' => __( 'Sidebar image and links to MDN.', 'promote-mdn' ), ) // Args
            );
        }

        public function widget( $args, $instance ) {
            $img = 'promobutton_mdn4.png';
            $img_array = array(
                'gray_css'          => 'promobutton_mdn1.png',
                'gray_html'         => 'promobutton_mdn2.png',
                'gray_javascript'   => 'promobutton_mdn3.png',
                'gray_web'          => 'promobutton_mdn4.png',
                'orange_css'        => 'promobutton_mdn5.png',
                'orange_html'       => 'promobutton_mdn6.png',
                'orange_javascript' => 'promobutton_mdn7.png',
                'orange_web'        => 'promobutton_mdn8.png',
                'red_css'           => 'promobutton_mdn9.png',
                'red_html'          => 'promobutton_mdn10.png',
                'red_javascript'    => 'promobutton_mdn11.png',
                'red_web'           => 'promobutton_mdn12.png',
            );
            extract( $args );
           
           echo $before_widget;

            if ( isset( $instance['color'] ) && isset( $instance['text'] ) )
                $img = $img_array[$instance['color'].'_'.strtolower( $instance['text'] )];
?>
    <div class="promote-mdn">
        <a href="https://developer.mozilla.org" target="_blank"><img src="https://developer.mozilla.org/media/img/promote/<?php echo esc_html( $img ); ?>" />
        <p><a href="https://developer.mozilla.org/promote" target="_blank"><?php _e( 'Help Promote MDN!', 'promote-mdn' ) ?></p>
        <p><a href="http://wordpress.org/extend/plugins/promote-mdn/" target="_blank"><?php _e( 'Get the WordPress plugin', 'promote-mdn' ) ?></p></a>
    </div>
<?php

                echo $after_widget;
        }

        public function form( $instance ) {
            $colors = array(
                'gray'      => __( 'Gray' ),
                'red'       => __( 'Red' ),
                'orange'    => __( 'Orange' ),
            );
            $texts          = array( 'HTML', 'CSS', 'JavaScript', 'Web' );
            $selected_color = 'grey';
            $selected_text  = 'Web';
            if ( isset( $instance['color'] ) )
                $selected_color = $instance['color'];
            if ( isset( $instance['text'] ) )
                $selected_text = $instance['text'];
?>
        <label for="<?php echo esc_html( $this->get_field_id( 'color' ) ); ?>"><?php _e( 'Color:' ); ?></label>
        <select id="<?php echo esc_html( $this->get_field_id( 'color' ) ); ?>" name="<?php echo esc_html( $this->get_field_name( 'color' ) ); ?>">
<?php
            foreach ( $colors as $value => $color ) {
                $selected = ( $value == $selected_color ) ? 'selected' : '';
?>
            <option value="<?php echo esc_html( $value ) ?>" <?php echo esc_html( $selected ) ?>><?php echo esc_html( $color ) ?></option>
<?php
            }
?>
        </select><br/>
        <label for="<?php echo esc_html( $this->get_field_id( 'text' ) ); ?>"><?php _e( 'Text:' ); ?></label>
        <select id="<?php echo esc_html( $this->get_field_id( 'text' ) ); ?>" name="<?php echo esc_html( $this->get_field_name( 'text' ) ); ?>">
<?php
            foreach ( $texts as $text ) {
                $selected = ( $text == $selected_text ) ? 'selected' : '';
?>
            <option value="<?php echo esc_html( $text ) ?>" <?php echo esc_html( $selected ) ?>><?php echo esc_html( $text ) ?></option>
<?php
            }
?>
        </select>
<?php
        }

        public function update( $new_instance, $old_instance ) {
            $instance = $old_instance;
            return $instance;
        }
    }
endif;

if ( !class_exists( 'PromoteMDN_Notifier' ) ) :
    class PromoteMDN_Notifier {

        public function __construct() {
            add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
            add_action( 'publish_post', array( $this, 'notify_mozilla' ) );
        }

        public function add_meta_box( $post_type ) {
            $post_types = array( 'post' ); // limit meta box to certain types
            if ( in_array( $post_type, $post_types ) ) {
                add_meta_box(
                    'major-publishing-actions'
                    ,__( 'Promote MDN', 'promote-mdn' )
                    ,array( $this, 'render_meta_box_content' )
                    ,$post_type
                    ,'side'
                    ,'high'
                );
            }
        }

        public function render_meta_box_content( $post ) {
?>
    <input name="notify_mozilla" type="checkbox" value="1" /><?php echo esc_html( __( 'Notify Mozilla of this post', 'promote-mdn' ) ); ?>
<?php
        }

        public function notify_mozilla( $post_id ) {
            if(    ( $_POST['post_status'] == 'publish' )
                && ( $_POST['original_post_status'] != 'publish' )
                && ( $_POST['notify_mozilla'] == '1' ) ) {
                $post = get_post($post_id);
                $author = get_userdata($post->post_author);
                $author_email = $author->user_email;
                $recipients = array('devrel@mozilla.com', 'press@mozilla.com');
                $email_subject = $author_email . ' published post "' . get_the_title( $post ) . '" to ' . get_bloginfo( 'name' );

                ob_start();
?>
View post at: <?php echo get_permalink( $post ); ?> \n
Email author at: <?php echo $author_email; ?>
<?php

                $message = ob_get_contents();

                ob_end_clean();

                error_log( "Mail:" );
                error_log( "Recipients: " . var_export( $recipients, true ) );
                error_log( "Subject: " . $email_subject );
                error_log( "Message: " . $message );

                wp_mail( $recipients, $email_subject, $message );
            }
        }

    }
endif;

if ( class_exists( 'PromoteMDN' ) ) :
    $in_phpunit = false;
    if ( array_key_exists( 'argv', $GLOBALS ) ) {
        foreach ( $GLOBALS['argv'] as $arg ) {
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

function call_notifier() {
    new PromoteMDN_Notifier();
}

if ( class_exists( 'PromoteMDN_Notifier' ) ) :
    if ( is_admin() ) {
        add_action( 'load-post.php', 'call_notifier' );
        add_action( 'load-post-new.php', 'call_notifier' );
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

add_filter( 'plugin_action_links', 'promote_mdn_settings_plugin_link', 10, 2 );

function promote_mdn_settings_plugin_link( $links, $file ) 
{
    if ( $file == plugin_basename(dirname(__FILE__) . '/promote-mdn.php') ) 
    {
        /*
         * Insert the link at the beginning
         */
        $in = '<a href="options-general.php?page=promote-mdn.php">' . __('Settings','promote-mdn') . '</a>';
        array_unshift($links, $in);

        /*
         * Insert at the end
         */
        // $links[] = '<a href="options-general.php?page=many-tips-together">'.__('Settings','mtt').'</a>';
    }
    return $links;
}