<?php

// Data functions (insert, update, delete, form) for table banner_posts


function banner_posts_insert(){
	global $Translation;

	// mm: can member insert record?
	$arrPerm=getTablePermissions('banner_posts');
	if(!$arrPerm[1]){
		return false;
	}

	$data['title'] = makeSafe($_REQUEST['title']);
		if($data['title'] == empty_lookup_value){ $data['title'] = ''; }
	$data['status'] = makeSafe($_REQUEST['status']);
		if($data['status'] == empty_lookup_value){ $data['status'] = ''; }
	if($data['title']== ''){
		echo StyleSheet() . "\n\n<div class=\"alert alert-danger\">" . $Translation['error:'] . " 'Title': " . $Translation['field not null'] . '<br><br>';
		echo '<a href="" onclick="history.go(-1); return false;">'.$Translation['< back'].'</a></div>';
		exit;
	}
	if($data['status']== ''){
		echo StyleSheet() . "\n\n<div class=\"alert alert-danger\">" . $Translation['error:'] . " 'Status': " . $Translation['field not null'] . '<br><br>';
		echo '<a href="" onclick="history.go(-1); return false;">'.$Translation['< back'].'</a></div>';
		exit;
	}

	// hook: banner_posts_before_insert
	if(function_exists('banner_posts_before_insert')){
		$args=array();
		if(!banner_posts_before_insert($data, getMemberInfo(), $args)){ return false; }
	}

	$o = array('silentErrors' => true);
	sql('insert into `banner_posts` set       `title`=' . (($data['title'] !== '' && $data['title'] !== NULL) ? "'{$data['title']}'" : 'NULL') . ', `status`=' . (($data['status'] !== '' && $data['status'] !== NULL) ? "'{$data['status']}'" : 'NULL'), $o);
	if($o['error']!=''){
		echo $o['error'];
		echo "<a href=\"banner_posts_view.php?addNew_x=1\">{$Translation['< back']}</a>";
		exit;
	}

	$recID = db_insert_id(db_link());

	// hook: banner_posts_after_insert
	if(function_exists('banner_posts_after_insert')){
		$res = sql("select * from `banner_posts` where `id`='" . makeSafe($recID, false) . "' limit 1", $eo);
		if($row = db_fetch_assoc($res)){
			$data = array_map('makeSafe', $row);
		}
		$data['selectedID'] = makeSafe($recID, false);
		$args=array();
		if(!banner_posts_after_insert($data, getMemberInfo(), $args)){ return $recID; }
	}

	// mm: save ownership data
	set_record_owner('banner_posts', $recID, getLoggedMemberID());

	return $recID;
}

function banner_posts_delete($selected_id, $AllowDeleteOfParents=false, $skipChecks=false){
	// insure referential integrity ...
	global $Translation;
	$selected_id=makeSafe($selected_id);

	// mm: can member delete record?
	$arrPerm=getTablePermissions('banner_posts');
	$ownerGroupID=sqlValue("select groupID from membership_userrecords where tableName='banner_posts' and pkValue='$selected_id'");
	$ownerMemberID=sqlValue("select lcase(memberID) from membership_userrecords where tableName='banner_posts' and pkValue='$selected_id'");
	if(($arrPerm[4]==1 && $ownerMemberID==getLoggedMemberID()) || ($arrPerm[4]==2 && $ownerGroupID==getLoggedGroupID()) || $arrPerm[4]==3){ // allow delete?
		// delete allowed, so continue ...
	}else{
		return $Translation['You don\'t have enough permissions to delete this record'];
	}

	// hook: banner_posts_before_delete
	if(function_exists('banner_posts_before_delete')){
		$args=array();
		if(!banner_posts_before_delete($selected_id, $skipChecks, getMemberInfo(), $args))
			return $Translation['Couldn\'t delete this record'];
	}

	sql("delete from `banner_posts` where `id`='$selected_id'", $eo);

	// hook: banner_posts_after_delete
	if(function_exists('banner_posts_after_delete')){
		$args=array();
		banner_posts_after_delete($selected_id, getMemberInfo(), $args);
	}

	// mm: delete ownership data
	sql("delete from membership_userrecords where tableName='banner_posts' and pkValue='$selected_id'", $eo);
}

function banner_posts_update($selected_id){
	global $Translation;

	// mm: can member edit record?
	$arrPerm=getTablePermissions('banner_posts');
	$ownerGroupID=sqlValue("select groupID from membership_userrecords where tableName='banner_posts' and pkValue='".makeSafe($selected_id)."'");
	$ownerMemberID=sqlValue("select lcase(memberID) from membership_userrecords where tableName='banner_posts' and pkValue='".makeSafe($selected_id)."'");
	if(($arrPerm[3]==1 && $ownerMemberID==getLoggedMemberID()) || ($arrPerm[3]==2 && $ownerGroupID==getLoggedGroupID()) || $arrPerm[3]==3){ // allow update?
		// update allowed, so continue ...
	}else{
		return false;
	}

	$data['title'] = makeSafe($_REQUEST['title']);
		if($data['title'] == empty_lookup_value){ $data['title'] = ''; }
	if($data['title']==''){
		echo StyleSheet() . "\n\n<div class=\"alert alert-danger\">{$Translation['error:']} 'Title': {$Translation['field not null']}<br><br>";
		echo '<a href="" onclick="history.go(-1); return false;">'.$Translation['< back'].'</a></div>';
		exit;
	}
	$data['status'] = makeSafe($_REQUEST['status']);
		if($data['status'] == empty_lookup_value){ $data['status'] = ''; }
	if($data['status']==''){
		echo StyleSheet() . "\n\n<div class=\"alert alert-danger\">{$Translation['error:']} 'Status': {$Translation['field not null']}<br><br>";
		echo '<a href="" onclick="history.go(-1); return false;">'.$Translation['< back'].'</a></div>';
		exit;
	}
	$data['selectedID']=makeSafe($selected_id);

	// hook: banner_posts_before_update
	if(function_exists('banner_posts_before_update')){
		$args=array();
		if(!banner_posts_before_update($data, getMemberInfo(), $args)){ return false; }
	}

	$o=array('silentErrors' => true);
	sql('update `banner_posts` set       `title`=' . (($data['title'] !== '' && $data['title'] !== NULL) ? "'{$data['title']}'" : 'NULL') . ', `status`=' . (($data['status'] !== '' && $data['status'] !== NULL) ? "'{$data['status']}'" : 'NULL') . " where `id`='".makeSafe($selected_id)."'", $o);
	if($o['error']!=''){
		echo $o['error'];
		echo '<a href="banner_posts_view.php?SelectedID='.urlencode($selected_id)."\">{$Translation['< back']}</a>";
		exit;
	}


	// hook: banner_posts_after_update
	if(function_exists('banner_posts_after_update')){
		$res = sql("SELECT * FROM `banner_posts` WHERE `id`='{$data['selectedID']}' LIMIT 1", $eo);
		if($row = db_fetch_assoc($res)){
			$data = array_map('makeSafe', $row);
		}
		$data['selectedID'] = $data['id'];
		$args = array();
		if(!banner_posts_after_update($data, getMemberInfo(), $args)){ return; }
	}

	// mm: update ownership data
	sql("update membership_userrecords set dateUpdated='".time()."' where tableName='banner_posts' and pkValue='".makeSafe($selected_id)."'", $eo);

}

function banner_posts_form($selected_id = '', $AllowUpdate = 1, $AllowInsert = 1, $AllowDelete = 1, $ShowCancel = 0, $TemplateDV = '', $TemplateDVP = ''){
	// function to return an editable form for a table records
	// and fill it with data of record whose ID is $selected_id. If $selected_id
	// is empty, an empty form is shown, with only an 'Add New'
	// button displayed.

	global $Translation;

	// mm: get table permissions
	$arrPerm=getTablePermissions('banner_posts');
	if(!$arrPerm[1] && $selected_id==''){ return ''; }
	$AllowInsert = ($arrPerm[1] ? true : false);
	// print preview?
	$dvprint = false;
	if($selected_id && $_REQUEST['dvprint_x'] != ''){
		$dvprint = true;
	}

	$filterer_title = thisOr(undo_magic_quotes($_REQUEST['filterer_title']), '');

	// populate filterers, starting from children to grand-parents

	// unique random identifier
	$rnd1 = ($dvprint ? rand(1000000, 9999999) : '');
	// combobox: title
	$combo_title = new DataCombo;
	// combobox: status
	$combo_status = new Combo;
	$combo_status->ListType = 0;
	$combo_status->MultipleSeparator = ', ';
	$combo_status->ListBoxHeight = 10;
	$combo_status->RadiosPerLine = 1;
	if(is_file(dirname(__FILE__).'/hooks/banner_posts.status.csv')){
		$status_data = addslashes(implode('', @file(dirname(__FILE__).'/hooks/banner_posts.status.csv')));
		$combo_status->ListItem = explode('||', entitiesToUTF8(convertLegacyOptions($status_data)));
		$combo_status->ListData = $combo_status->ListItem;
	}else{
		$combo_status->ListItem = explode('||', entitiesToUTF8(convertLegacyOptions("active;;dissabled")));
		$combo_status->ListData = $combo_status->ListItem;
	}
	$combo_status->SelectName = 'status';
	$combo_status->AllowNull = false;

	if($selected_id){
		// mm: check member permissions
		if(!$arrPerm[2]){
			return "";
		}
		// mm: who is the owner?
		$ownerGroupID=sqlValue("select groupID from membership_userrecords where tableName='banner_posts' and pkValue='".makeSafe($selected_id)."'");
		$ownerMemberID=sqlValue("select lcase(memberID) from membership_userrecords where tableName='banner_posts' and pkValue='".makeSafe($selected_id)."'");
		if($arrPerm[2]==1 && getLoggedMemberID()!=$ownerMemberID){
			return "";
		}
		if($arrPerm[2]==2 && getLoggedGroupID()!=$ownerGroupID){
			return "";
		}

		// can edit?
		if(($arrPerm[3]==1 && $ownerMemberID==getLoggedMemberID()) || ($arrPerm[3]==2 && $ownerGroupID==getLoggedGroupID()) || $arrPerm[3]==3){
			$AllowUpdate=1;
		}else{
			$AllowUpdate=0;
		}

		$res = sql("select * from `banner_posts` where `id`='".makeSafe($selected_id)."'", $eo);
		if(!($row = db_fetch_array($res))){
			return error_message($Translation['No records found'], 'banner_posts_view.php', false);
		}
		$urow = $row; /* unsanitized data */
		$hc = new CI_Input();
		$row = $hc->xss_clean($row); /* sanitize data */
		$combo_title->SelectedData = $row['title'];
		$combo_status->SelectedData = $row['status'];
	}else{
		$combo_title->SelectedData = $filterer_title;
		$combo_status->SelectedText = ( $_REQUEST['FilterField'][1]=='3' && $_REQUEST['FilterOperator'][1]=='<=>' ? (get_magic_quotes_gpc() ? stripslashes($_REQUEST['FilterValue'][1]) : $_REQUEST['FilterValue'][1]) : "");
	}
	$combo_title->HTML = '<span id="title-container' . $rnd1 . '"></span><input type="hidden" name="title" id="title' . $rnd1 . '" value="' . html_attr($combo_title->SelectedData) . '">';
	$combo_title->MatchText = '<span id="title-container-readonly' . $rnd1 . '"></span><input type="hidden" name="title" id="title' . $rnd1 . '" value="' . html_attr($combo_title->SelectedData) . '">';
	$combo_status->Render();

	ob_start();
	?>

	<script>
		// initial lookup values
		AppGini.current_title__RAND__ = { text: "", value: "<?php echo addslashes($selected_id ? $urow['title'] : $filterer_title); ?>"};

		jQuery(function() {
			setTimeout(function(){
				if(typeof(title_reload__RAND__) == 'function') title_reload__RAND__();
			}, 10); /* we need to slightly delay client-side execution of the above code to allow AppGini.ajaxCache to work */
		});
		function title_reload__RAND__(){
		<?php if(($AllowUpdate || $AllowInsert) && !$dvprint){ ?>

			$j("#title-container__RAND__").select2({
				/* initial default value */
				initSelection: function(e, c){
					$j.ajax({
						url: 'ajax_combo.php',
						dataType: 'json',
						data: { id: AppGini.current_title__RAND__.value, t: 'banner_posts', f: 'title' },
						success: function(resp){
							c({
								id: resp.results[0].id,
								text: resp.results[0].text
							});
							$j('[name="title"]').val(resp.results[0].id);
							$j('[id=title-container-readonly__RAND__]').html('<span id="title-match-text">' + resp.results[0].text + '</span>');
							if(resp.results[0].id == '<?php echo empty_lookup_value; ?>'){ $j('.btn[id=blogs_view_parent]').hide(); }else{ $j('.btn[id=blogs_view_parent]').show(); }


							if(typeof(title_update_autofills__RAND__) == 'function') title_update_autofills__RAND__();
						}
					});
				},
				width: '100%',
				formatNoMatches: function(term){ return '<?php echo addslashes($Translation['No matches found!']); ?>'; },
				minimumResultsForSearch: 10,
				loadMorePadding: 200,
				ajax: {
					url: 'ajax_combo.php',
					dataType: 'json',
					cache: true,
					data: function(term, page){ return { s: term, p: page, t: 'banner_posts', f: 'title' }; },
					results: function(resp, page){ return resp; }
				},
				escapeMarkup: function(str){ return str; }
			}).on('change', function(e){
				AppGini.current_title__RAND__.value = e.added.id;
				AppGini.current_title__RAND__.text = e.added.text;
				$j('[name="title"]').val(e.added.id);
				if(e.added.id == '<?php echo empty_lookup_value; ?>'){ $j('.btn[id=blogs_view_parent]').hide(); }else{ $j('.btn[id=blogs_view_parent]').show(); }


				if(typeof(title_update_autofills__RAND__) == 'function') title_update_autofills__RAND__();
			});

			if(!$j("#title-container__RAND__").length){
				$j.ajax({
					url: 'ajax_combo.php',
					dataType: 'json',
					data: { id: AppGini.current_title__RAND__.value, t: 'banner_posts', f: 'title' },
					success: function(resp){
						$j('[name="title"]').val(resp.results[0].id);
						$j('[id=title-container-readonly__RAND__]').html('<span id="title-match-text">' + resp.results[0].text + '</span>');
						if(resp.results[0].id == '<?php echo empty_lookup_value; ?>'){ $j('.btn[id=blogs_view_parent]').hide(); }else{ $j('.btn[id=blogs_view_parent]').show(); }

						if(typeof(title_update_autofills__RAND__) == 'function') title_update_autofills__RAND__();
					}
				});
			}

		<?php }else{ ?>

			$j.ajax({
				url: 'ajax_combo.php',
				dataType: 'json',
				data: { id: AppGini.current_title__RAND__.value, t: 'banner_posts', f: 'title' },
				success: function(resp){
					$j('[id=title-container__RAND__], [id=title-container-readonly__RAND__]').html('<span id="title-match-text">' + resp.results[0].text + '</span>');
					if(resp.results[0].id == '<?php echo empty_lookup_value; ?>'){ $j('.btn[id=blogs_view_parent]').hide(); }else{ $j('.btn[id=blogs_view_parent]').show(); }

					if(typeof(title_update_autofills__RAND__) == 'function') title_update_autofills__RAND__();
				}
			});
		<?php } ?>

		}
	</script>
	<?php

	$lookups = str_replace('__RAND__', $rnd1, ob_get_contents());
	ob_end_clean();


	// code for template based detail view forms

	// open the detail view template
	if($dvprint){
		$template_file = is_file("./{$TemplateDVP}") ? "./{$TemplateDVP}" : './templates/banner_posts_templateDVP.html';
		$templateCode = @file_get_contents($template_file);
	}else{
		$template_file = is_file("./{$TemplateDV}") ? "./{$TemplateDV}" : './templates/banner_posts_templateDV.html';
		$templateCode = @file_get_contents($template_file);
	}

	// process form title
	$templateCode = str_replace('<%%DETAIL_VIEW_TITLE%%>', 'Banner post details', $templateCode);
	$templateCode = str_replace('<%%RND1%%>', $rnd1, $templateCode);
	$templateCode = str_replace('<%%EMBEDDED%%>', ($_REQUEST['Embedded'] ? 'Embedded=1' : ''), $templateCode);
	// process buttons
	if($AllowInsert){
		if(!$selected_id) $templateCode = str_replace('<%%INSERT_BUTTON%%>', '<button type="submit" class="btn btn-success" id="insert" name="insert_x" value="1" onclick="return banner_posts_validateData();"><i class="glyphicon glyphicon-plus-sign"></i> ' . $Translation['Save New'] . '</button>', $templateCode);
		$templateCode = str_replace('<%%INSERT_BUTTON%%>', '<button type="submit" class="btn btn-default" id="insert" name="insert_x" value="1" onclick="return banner_posts_validateData();"><i class="glyphicon glyphicon-plus-sign"></i> ' . $Translation['Save As Copy'] . '</button>', $templateCode);
	}else{
		$templateCode = str_replace('<%%INSERT_BUTTON%%>', '', $templateCode);
	}

	// 'Back' button action
	if($_REQUEST['Embedded']){
		$backAction = 'AppGini.closeParentModal(); return false;';
	}else{
		$backAction = '$j(\'form\').eq(0).attr(\'novalidate\', \'novalidate\'); document.myform.reset(); return true;';
	}

	if($selected_id){
		if(!$_REQUEST['Embedded']) $templateCode = str_replace('<%%DVPRINT_BUTTON%%>', '<button type="submit" class="btn btn-default" id="dvprint" name="dvprint_x" value="1" onclick="$$(\'form\')[0].writeAttribute(\'novalidate\', \'novalidate\'); document.myform.reset(); return true;" title="' . html_attr($Translation['Print Preview']) . '"><i class="glyphicon glyphicon-print"></i> ' . $Translation['Print Preview'] . '</button>', $templateCode);
		if($AllowUpdate){
			$templateCode = str_replace('<%%UPDATE_BUTTON%%>', '<button type="submit" class="btn btn-success btn-lg" id="update" name="update_x" value="1" onclick="return banner_posts_validateData();" title="' . html_attr($Translation['Save Changes']) . '"><i class="glyphicon glyphicon-ok"></i> ' . $Translation['Save Changes'] . '</button>', $templateCode);
		}else{
			$templateCode = str_replace('<%%UPDATE_BUTTON%%>', '', $templateCode);
		}
		if(($arrPerm[4]==1 && $ownerMemberID==getLoggedMemberID()) || ($arrPerm[4]==2 && $ownerGroupID==getLoggedGroupID()) || $arrPerm[4]==3){ // allow delete?
			$templateCode = str_replace('<%%DELETE_BUTTON%%>', '<button type="submit" class="btn btn-danger" id="delete" name="delete_x" value="1" onclick="return confirm(\'' . $Translation['are you sure?'] . '\');" title="' . html_attr($Translation['Delete']) . '"><i class="glyphicon glyphicon-trash"></i> ' . $Translation['Delete'] . '</button>', $templateCode);
		}else{
			$templateCode = str_replace('<%%DELETE_BUTTON%%>', '', $templateCode);
		}
		$templateCode = str_replace('<%%DESELECT_BUTTON%%>', '<button type="submit" class="btn btn-default" id="deselect" name="deselect_x" value="1" onclick="' . $backAction . '" title="' . html_attr($Translation['Back']) . '"><i class="glyphicon glyphicon-chevron-left"></i> ' . $Translation['Back'] . '</button>', $templateCode);
	}else{
		$templateCode = str_replace('<%%UPDATE_BUTTON%%>', '', $templateCode);
		$templateCode = str_replace('<%%DELETE_BUTTON%%>', '', $templateCode);
		$templateCode = str_replace('<%%DESELECT_BUTTON%%>', ($ShowCancel ? '<button type="submit" class="btn btn-default" id="deselect" name="deselect_x" value="1" onclick="' . $backAction . '" title="' . html_attr($Translation['Back']) . '"><i class="glyphicon glyphicon-chevron-left"></i> ' . $Translation['Back'] . '</button>' : ''), $templateCode);
	}

	// set records to read only if user can't insert new records and can't edit current record
	if(($selected_id && !$AllowUpdate && !$AllowInsert) || (!$selected_id && !$AllowInsert)){
		$jsReadOnly .= "\tjQuery('#title').prop('disabled', true).css({ color: '#555', backgroundColor: '#fff' });\n";
		$jsReadOnly .= "\tjQuery('#title_caption').prop('disabled', true).css({ color: '#555', backgroundColor: 'white' });\n";
		$jsReadOnly .= "\tjQuery('#status').replaceWith('<div class=\"form-control-static\" id=\"status\">' + (jQuery('#status').val() || '') + '</div>'); jQuery('#status-multi-selection-help').hide();\n";
		$jsReadOnly .= "\tjQuery('.select2-container').hide();\n";

		$noUploads = true;
	}elseif($AllowInsert){
		$jsEditable .= "\tjQuery('form').eq(0).data('already_changed', true);"; // temporarily disable form change handler
			$jsEditable .= "\tjQuery('form').eq(0).data('already_changed', false);"; // re-enable form change handler
	}

	// process combos
	$templateCode = str_replace('<%%COMBO(title)%%>', $combo_title->HTML, $templateCode);
	$templateCode = str_replace('<%%COMBOTEXT(title)%%>', $combo_title->MatchText, $templateCode);
	$templateCode = str_replace('<%%URLCOMBOTEXT(title)%%>', urlencode($combo_title->MatchText), $templateCode);
	$templateCode = str_replace('<%%COMBO(status)%%>', $combo_status->HTML, $templateCode);
	$templateCode = str_replace('<%%COMBOTEXT(status)%%>', $combo_status->SelectedData, $templateCode);

	/* lookup fields array: 'lookup field name' => array('parent table name', 'lookup field caption') */
	$lookup_fields = array(  'title' => array('blogs', 'Title'));
	foreach($lookup_fields as $luf => $ptfc){
		$pt_perm = getTablePermissions($ptfc[0]);

		// process foreign key links
		if($pt_perm['view'] || $pt_perm['edit']){
			$templateCode = str_replace("<%%PLINK({$luf})%%>", '<button type="button" class="btn btn-default view_parent hspacer-md" id="' . $ptfc[0] . '_view_parent" title="' . html_attr($Translation['View'] . ' ' . $ptfc[1]) . '"><i class="glyphicon glyphicon-eye-open"></i></button>', $templateCode);
		}

		// if user has insert permission to parent table of a lookup field, put an add new button
		if($pt_perm['insert'] && !$_REQUEST['Embedded']){
			$templateCode = str_replace("<%%ADDNEW({$ptfc[0]})%%>", '<button type="button" class="btn btn-success add_new_parent hspacer-md" id="' . $ptfc[0] . '_add_new" title="' . html_attr($Translation['Add New'] . ' ' . $ptfc[1]) . '"><i class="glyphicon glyphicon-plus-sign"></i></button>', $templateCode);
		}
	}

	// process images
	$templateCode = str_replace('<%%UPLOADFILE(id)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(title)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(status)%%>', '', $templateCode);

	// process values
	if($selected_id){
		if( $dvprint) $templateCode = str_replace('<%%VALUE(id)%%>', safe_html($urow['id']), $templateCode);
		if(!$dvprint) $templateCode = str_replace('<%%VALUE(id)%%>', html_attr($row['id']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(id)%%>', urlencode($urow['id']), $templateCode);
		if( $dvprint) $templateCode = str_replace('<%%VALUE(title)%%>', safe_html($urow['title']), $templateCode);
		if(!$dvprint) $templateCode = str_replace('<%%VALUE(title)%%>', html_attr($row['title']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(title)%%>', urlencode($urow['title']), $templateCode);
		if( $dvprint) $templateCode = str_replace('<%%VALUE(status)%%>', safe_html($urow['status']), $templateCode);
		if(!$dvprint) $templateCode = str_replace('<%%VALUE(status)%%>', html_attr($row['status']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(status)%%>', urlencode($urow['status']), $templateCode);
	}else{
		$templateCode = str_replace('<%%VALUE(id)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(id)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(title)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(title)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(status)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(status)%%>', urlencode(''), $templateCode);
	}

	// process translations
	foreach($Translation as $symbol=>$trans){
		$templateCode = str_replace("<%%TRANSLATION($symbol)%%>", $trans, $templateCode);
	}

	// clear scrap
	$templateCode = str_replace('<%%', '<!-- ', $templateCode);
	$templateCode = str_replace('%%>', ' -->', $templateCode);

	// hide links to inaccessible tables
	if($_REQUEST['dvprint_x'] == ''){
		$templateCode .= "\n\n<script>\$j(function(){\n";
		$arrTables = getTableList();
		foreach($arrTables as $name => $caption){
			$templateCode .= "\t\$j('#{$name}_link').removeClass('hidden');\n";
			$templateCode .= "\t\$j('#xs_{$name}_link').removeClass('hidden');\n";
		}

		$templateCode .= $jsReadOnly;
		$templateCode .= $jsEditable;

		if(!$selected_id){
		}

		$templateCode.="\n});</script>\n";
	}

	// ajaxed auto-fill fields
	$templateCode .= '<script>';
	$templateCode .= '$j(function() {';


	$templateCode.="});";
	$templateCode.="</script>";
	$templateCode .= $lookups;

	// handle enforced parent values for read-only lookup fields

	// don't include blank images in lightbox gallery
	$templateCode = preg_replace('/blank.gif" data-lightbox=".*?"/', 'blank.gif"', $templateCode);

	// don't display empty email links
	$templateCode=preg_replace('/<a .*?href="mailto:".*?<\/a>/', '', $templateCode);

	/* default field values */
	$rdata = $jdata = get_defaults('banner_posts');
	if($selected_id){
		$jdata = get_joined_record('banner_posts', $selected_id);
		if($jdata === false) $jdata = get_defaults('banner_posts');
		$rdata = $row;
	}
	$cache_data = array(
		'rdata' => array_map('nl2br', array_map('addslashes', $rdata)),
		'jdata' => array_map('nl2br', array_map('addslashes', $jdata))
	);
	$templateCode .= loadView('banner_posts-ajax-cache', $cache_data);

	// hook: banner_posts_dv
	if(function_exists('banner_posts_dv')){
		$args=array();
		banner_posts_dv(($selected_id ? $selected_id : FALSE), getMemberInfo(), $templateCode, $args);
	}

	return $templateCode;
}
?>