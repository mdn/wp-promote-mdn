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
if ( !class_exists('PromoteMDN') ) :

class PromoteMDN {
	var $PromoteMDN_DB_option = 'PromoteMDN';
	var $PromoteMDN_options; 
	
	// Initialize WordPress hooks
	function PromoteMDN() {	
        add_filter('the_content',  array(&$this, 'PromoteMDN_the_content_filter'), 10);	
        // Add Options Page
        add_action('admin_menu',  array(&$this, 'PromoteMDN_admin_menu'));
	}


    function PromoteMDN_process_text($text)
    {
        error_log("PromoteMDN_process_text");
        global $wpdb, $post;
        $options = $this->get_options();
        $links=0;
        if (is_feed() && !$options['allowfeed'])
             return $text;
            
        $arrignorepost=$this->explode_trim(",", ($options['ignorepost']));
        if (is_page($arrignorepost) || is_single($arrignorepost)) {
            return $text;
        }
        
        $maxlinks=($options['maxlinks']>0) ? $options['maxlinks'] : 0;	
        $maxsingle=($options['maxsingle']>0) ? $options['maxsingle'] : -1;
        $maxsingleurl=($options['maxsingleurl']>0) ? $options['maxsingleurl'] : 0;
        $minusage = ($options['minusage']>0) ? $options['minusage'] : 1;

        $urls = array();
            
        $arrignore=$this->explode_trim(",", ($options['ignore']));
        if ($options['excludeheading'] == "on") {
            //Here insert special characters
            $text = preg_replace('%(<h.*?>)(.*?)(</h.*?>)%sie', "'\\1'.wp_insertspecialchars('\\2').'\\3'", $text);
        }
        
        $reg_post		=	$options['casesens'] ? '/(?!(?:[^<\[]+[>\]]|[^>\]]+<\/a>))($name)/msU' : '/(?!(?:[^<\[]+[>\]]|[^>\]]+<\/a>))($name)/imsU';	
        $reg			=	$options['casesens'] ? '/(?!(?:[^<\[]+[>\]]|[^>\]]+<\/a>))\b($name)\b/msU' : '/(?!(?:[^<\[]+[>\]]|[^>\]]+<\/a>))\b($name)\b/imsU';
        $strpos_fnc		=	$options['casesens'] ? 'strpos' : 'stripos';
        
        $text = " $text ";

        error_log(var_export($options, true));
        if (!empty($options['customkey_url']))
        {
            $now = time();
            if ($options['customkey_url_datetime']){
                $last_update = $options['customkey_url_datetime'];
            } else {
                $last_update = 0;
            }
            if ($now - $last_update > 86400) {
                $body = wp_remote_retrieve_body(wp_remote_get($options['customkey_url']));
                $options['customkey_url_value'] = strip_tags($body);
                $options['customkey_url_datetime'] = $now;
                update_option($this->PromoteMDN_DB_option, $options);
            }
            $options['customkey'] = $options['customkey'] . "\n" . $options['customkey_url_value'];
        }
        // custom keywords
        if (!empty($options['customkey'])) {		
            $kw_array = array();
            // thanks PK for the suggestion
            foreach (explode("\n", $options['customkey']) as $line) {
                $line = trim($line);
                $lastDelimiterPos=strrpos($line, ',');
                $url = substr($line, $lastDelimiterPos + 1 );
                $keywords = substr($line, 0, $lastDelimiterPos);
                
                if(!empty($keywords) && !empty($url)){
                    $kw_array[$keywords] = $url;
                }
                
                $keywords='';
                $url='';
            }
            foreach ($kw_array as $name=>$url) 
            {
                if ((!$maxlinks || ($links < $maxlinks)) && (trailingslashit($url)!=$thisurl) && !in_array( $options['casesens'] ? $name : strtolower($name), $arrignore) && (!$maxsingleurl || $urls[$url]<$maxsingleurl) )
                {
                    if (($options['customkey_preventduplicatelink'] == TRUE) || $strpos_fnc($text, $name) !== false) {		// credit to Dominik Deobald -- TODO: change string search for preg_match
                        $name= preg_quote($name, '/');
                        
                        if($options['customkey_preventduplicatelink'] == TRUE) $name = str_replace(',','|',$name); //Modifying RegExp for count all grouped keywords as the same one
                        
                        $replace="<a title=\"$1\" href=\"$url\">$1</a>";
                        $regexp=str_replace('$name', $name, $reg);	
                        //$regexp="/(?!(?:[^<]+>|[^>]+<\/a>))(?<!\p{L})($name)(?!\p{L})/imsU";
                        $newtext = preg_replace($regexp, $replace, $text, $maxsingle);			
                        if ($newtext!=$text) {							
                            $links++;
                            $text=$newtext;
                            if (!isset($urls[$url])) $urls[$url]=1; else $urls[$url]++;
                        }	
                    }
                }		
            }
        }
        
        
        if ($options['excludeheading'] == "on") {
            //Here insert special characters
            $text = preg_replace('%(<h.*?>)(.*?)(</h.*?>)%sie', "'\\1'.wp_removespecialchars('\\2').'\\3'", $text);
            $text = stripslashes($text);
        }
        return trim( $text );

    } 

    function PromoteMDN_the_content_filter($text) {
        error_log("PromoteMDN_the_content_filter");
        
        $result=$this->PromoteMDN_process_text($text);
        
        $options = $this->get_options();
        $link=parse_url(get_bloginfo('wpurl'));
        $host='http://'.$link['host'];
        
        if ($options['blanko'])
            $result = preg_replace('%<a(\s+.*?href=\S(?!' . $host . '))%i', '<a target="_blank"\\1', $result); // credit to  Kaf Oseo
        
        return $result;
    }
	
    function explode_trim($separator, $text)
    {
        $arr = explode($separator, $text);
        
        $ret = array();
        foreach($arr as $e)
        {        
          $ret[] = trim($e);        
        }
        return $ret;
    }
	
    // Handle our options
    function get_options() {
        error_log("get_options");
     $options = array(
         'post' => 'on',
         'page' => 'on',
         'excludeheading' => 'on', 
         'ignore' => 'about,', 
        'ignorepost' => 'contact', 
         'maxlinks' => 3,
         'maxsingle' => 1,
         'minusage' => 1,
         'customkey' => '',
         'customkey_url' => '',
         'customkey_url_value' => '',
         'customkey_url_datetime' => '',
         'blankn' =>'',
         'blanko' =>'',
         'casesens' =>'',
         'allowfeed' => '',
         'maxsingleurl' => '1',
         );
         
        $saved = get_option($this->PromoteMDN_DB_option);
     
     
         if (!empty($saved)) {
             foreach ($saved as $key => $option)
                    $options[$key] = $option;
         }
        
         if ($saved != $options)	
            update_option($this->PromoteMDN_DB_option, $options);
        
         return $options;
    }

	// Set up everything
	function install() {
		$PromoteMDN_options = $this->get_options();		
	}
	
	function handle_options() {
        error_log("handle_options");
		$options = $this->get_options();
		if ( isset($_POST['submitted']) ) {
			check_admin_referer('seo-smart-links');		
			
			$options['post']=$_POST['post'];					
			$options['postself']=$_POST['postself'];					
			$options['page']=$_POST['page'];					
			$options['pageself']=$_POST['pageself'];					
			$options['excludeheading']=$_POST['excludeheading'];									
			$options['ignore']=$_POST['ignore'];	
			$options['ignorepost']=$_POST['ignorepost'];					
			$options['maxlinks']=(int) $_POST['maxlinks'];					
			$options['maxsingle']=(int) $_POST['maxsingle'];					
			$options['maxsingleurl']=(int) $_POST['maxsingleurl'];
			$options['minusage']=(int) $_POST['minusage'];			// credit to Dominik Deobald		
			$options['customkey']=$_POST['customkey'];	
            $options['customkey_url']=$_POST['customkey_url'];
			$options['blankn']=$_POST['blankn'];	
			$options['blanko']=$_POST['blanko'];	
			$options['casesens']=$_POST['casesens'];	
			$options['allowfeed']=$_POST['allowfeed'];	
			
			update_option($this->PromoteMDN_DB_option, $options);
			$this->PromoteMDN_delete_cache(0);
			echo '<div class="updated fade"><p>Plugin settings saved.</p></div>';
		}

		$action_url = $_SERVER['REQUEST_URI'];	

		$post=$options['post']=='on'?'checked':'';
		$postself=$options['postself']=='on'?'checked':'';
		$page=$options['page']=='on'?'checked':'';
		$pageself=$options['pageself']=='on'?'checked':'';
		$comment=$options['comment']=='on'?'checked':'';
		$excludeheading=$options['excludeheading']=='on'?'checked':'';
		$lposts=$options['lposts']=='on'?'checked':'';
		$lpages=$options['lpages']=='on'?'checked':'';
		$lcats=$options['lcats']=='on'?'checked':'';
		$ltags=$options['ltags']=='on'?'checked':'';
		$ignore=$options['ignore'];
		$ignorepost=$options['ignorepost'];
		$maxlinks=$options['maxlinks'];
		$maxsingle=$options['maxsingle'];
		$maxsingleurl=$options['maxsingleurl'];
		$minusage=$options['minusage'];
		$customkey=stripslashes($options['customkey']);
        $customkey_url=stripslashes($options['customkey_url']);
		$nofoln=$options['nofoln']=='on'?'checked':'';
		$nofolo=$options['nofolo']=='on'?'checked':'';
		$blankn=$options['blankn']=='on'?'checked':'';
		$blanko=$options['blanko']=='on'?'checked':'';
		$casesens=$options['casesens']=='on'?'checked':'';
		$allowfeed=$options['allowfeed']=='on'?'checked':'';

		if (!is_numeric($minusage)) $minusage = 1;
		
		$nonce=wp_create_nonce( 'seo-smart-links');
		
		$imgpath=trailingslashit(get_option('siteurl')). 'wp-content/plugins/seo-automatic-links/i';	
		echo <<<END

<div class="wrap" style="">
	<h2>Promote MDN</h2>

	 <div id="mainblock" style="width:710px">
	 
		<div class="dbx-content">
		 	<form name="PromoteMDN" action="$action_url" method="post">
		 		    <input type="hidden" id="_wpnonce" name="_wpnonce" value="$nonce" />
					<input type="hidden" name="submitted" value="1" /> 
					<p>Promote MDN automatically links keywords and phrases in your posts and pages to MDN URLs.</p>
					
                    <p>Load keywords from URL:<br/>
                    <input type="text" name="customkey_url" size="90" value="$customkey_url" />
                    </p>
					<input type="checkbox" name="allowfeed" $allowfeed /> <label for="allowfeed">Add links to RSS feeds</label><br/>
					<input type="checkbox" name="blanko" $blanko /> <label for="blanko">Open links in new window</label> <br/>
					
					<h4>Exceptions</h4>				
					<input type="checkbox" name="excludeheading"  $excludeheading/> <label for="excludeheading">Do not add links in heading tags (h1,h2,h3,h4,h5,h6).</label><br/>
					<p>Do not add links to the following posts or pages: (comma-separated id, slug, name)</p>
					<input type="text" name="ignorepost" size="90" value="$ignorepost"/> 
					<br>
                    
					<p>Do not add links on the following phrases: (comma-separated)</p>
					<input type="text" name="ignore" size="90" value="$ignore"/> 
					<br><br>                 
                    
                    <h4>Limits</h4>				
					Max links to generate per post: <input type="text" name="maxlinks" size="2" value="$maxlinks"/><br/>
					Max links to generate for a single keyword/phrase: <input type="text" name="maxsingle" size="2" value="$maxsingle"/><br/>
					Max links to generate for a single URL: <input type="text" name="maxsingleurl" size="2" value="$maxsingleurl"/>
					 					 
					<h4>Custom Keywords</h4>
					<p>Extra keywords to automaticaly link. Use comma to seperate keywords and add target url at the end. Use a new line for new url and set of keywords. e.g.,<br/>
                    <pre>addons, amo, http://addons.mozilla.org/
support, sumo, http://support.mozilla.org/
                    </pre>
					</p>
					
					<textarea name="customkey" id="customkey" rows="10" cols="90"  >$customkey</textarea>
					<br><br>

					<div class="submit"><input type="submit" name="Submit" value="Update options" class="button-primary" /></div>
			</form>
		</div>
		
		<br/><br/><h3>&nbsp;</h3>	
	 </div>

	</div>
	
</div>
END;
		
		
	}
	
	function PromoteMDN_admin_menu()
	{
        error_log("PromoteMDN_admin_menu");
		add_options_page('Promote MDN Options', 'Promote MDN', 8, basename(__FILE__), array(&$this, 'handle_options'));
	}

    function PromoteMDN_delete_cache($id) {
        error_log("PromoteMDN_delete_cache");
         wp_cache_delete( 'seo-links-categories', 'seo-smart-links' );
         wp_cache_delete( 'seo-links-tags', 'seo-smart-links' );
         wp_cache_delete( 'seo-links-posts', 'seo-smart-links' );
    }
}

endif; 

if ( class_exists('PromoteMDN') ) :
	
	$PromoteMDN = new PromoteMDN();
	if (isset($PromoteMDN)) {
		register_activation_hook( __FILE__, array(&$PromoteMDN, 'install') );
	}
endif;

function wp_insertspecialchars($str) {
    $strarr = wp_str2arr($str);
    $str = implode("<!---->", $strarr);
    return $str;
}
function wp_removespecialchars($str) {
    $strarr = explode("<!---->", $str);
    $str = implode("", $strarr);
    $str = stripslashes($str);
    return $str;
}
function wp_str2arr($str) {
    $chararray = array();
    for($i=0; $i < strlen($str); $i++){
        array_push($chararray,$str{$i});
    }
    return $chararray;
}
