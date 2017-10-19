
<div id="tabs-2" class="wrap">
	<?php
	$cmb = new_cmb2_box( array(
		'id' => PM_TEXTDOMAIN . '_keywords',
		'hookup' => false,
		'show_on' => array( 'key' => 'options-page', 'value' => array( PM_TEXTDOMAIN ), ),
		'show_names' => true,
			) );
	$cmb->add_field( array(
		'name' => __( 'Custom Keywords', PM_TEXTDOMAIN ),
		'id' => 'first_title',
		'type' => 'title',
	) );
	$cmb->add_field( array(
		'name' => __( 'Extra keywords to automaticaly link.<br> Use comma to seperate keywords and add target url at the end.<br> Use a new line for new url and set of keywords.', PM_TEXTDOMAIN ),
		'desc' => __( 'Note: These keywords will take priority over those loaded at the URL. If you have too many custom keywords here, you may not link to MDN at all.', PM_TEXTDOMAIN ),
		'id' => 'customkey',
		'before_field' => 'Examples:<br><pre>addons, amo, http://addons.mozilla.org/</pre><pre>sumo, http://support.mozilla.org/</pre>',
		'type' => 'textarea',
	) );
	cmb2_metabox_form( PM_TEXTDOMAIN . '_keywords', PM_TEXTDOMAIN . '-keywords' );
	?>
</div>
