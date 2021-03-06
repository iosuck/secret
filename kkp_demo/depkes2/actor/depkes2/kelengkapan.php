<?php

#$GLOBALS[my_table] = 'kelengkapan_alkes';
#$GLOBALS[my_title] = 'Registrasi Izin Produksi';

	require_once 'init.php';
	require_once 'auth.php';

if (class_exists('kelengkapan_controller')) {
	// do nothing
} else if (defined('CLASS_KELENGKAPAN_ALKES_CONTROLLER')) {
	// do nothing
} else {
	define('CLASS_KELENGKAPAN_ALKES_CONTROLLER', TRUE);

#include_once 'class.kelengkapan.inc.php';
class kelengkapan_controller extends depkes2_manager {
	var $kelengkapan_label;
	var $optional_arr;
	function kelengkapan_controller() {
		$this->kelengkapan_label = array (
			'id_kelengkapan' => 'Id Kelengkapan',
			'nama_kelengkapan' => 'Nama Kelengkapan',
			'insert_by' => 'Insert By',
			'date_insert' => 'Date Insert',
			'sifat' => 'Sifat'
		);
		$this->depkes2_manager();
		$this->optional_arr = array (
			'id_kelengkapan' => TRUE,
			'nama_kelengkapan' => FALSE,
			'insert_by' => TRUE,
			'date_insert' => TRUE,
			'sifat' => FALSE// could be toupper, tolower, protect, user_defined, user_rules
		);
	}


	// create add Kelengkapan form
	function add_kelengkapan_form() {
		include_once 'class.xform.inc.php';
	
//		if (! $this->get_permission('fill_this')) return $this->intruder();
		global $_SESSION, $_GET, $adodb, $ses;
		$record = $_GET;
		$label_arr = $this->kelengkapan_label;
		$optional_arr = $this->optional_arr;
		
		$field_arr[] = xform::xf('id_kelengkapan','N','4');
		$field_arr[] = xform::xf('nama_kelengkapan','X','-1');
		$field_arr[] = xform::xf('insert_by','C','16');
		$field_arr[] = xform::xf('date_insert','N','8');
		
		$rs = $adodb->Execute("SELECT * FROM ".$GLOBALS[my_table]." WHERE id_kelengkapan='{$record['id_kelengkapan']}'");
		if ($rs && ! $rs->EOF) {
			$value_arr = $rs->fields;
			$optional_arr['id_kelengkapan'] = 'protect';
			$mode = 'edit';
			
		} else {
			$value_arr = array ();
			$mode = 'add';
		}
		
		$optional_arr['nama_kelengkapan_rule'] = '';
		$optional_arr['insert_by_rule'] = '';
		$optional_arr['date_insert_rule'] = '';
		$optional_arr['sifat_rule'] = '';
		
		eval($this->save_config);
				
                if($value_arr['sifat']=='perlu'){$check1='checked';$check2='';}else{$check1='';$check2='checked';}
                $optional_arr['sifat']='user_defined';
                $value_arr['sifat']='<input type="radio" name="sifat" value="perlu" class="text" '.$check1.'>Perlu <input type="radio" name="sifat" value="tidak perlu" class="text" '.$check2.'>Tidak perlu';


		$label_arr['submit_val'] = "Submit";
		$label_arr['form_extra']  = "<input type=hidden name=action     value='post$mode'>"; // default null
		$label_arr['form_extra'] .= "<input type=hidden name=oldpkvalue value='{$record['id_kelengkapan']}'>";
		$label_arr['form_title'] = "Form ".ucwords($mode)." Kelengkapan ".$GLOBALS[my_title]."";
		$label_arr['form_width'] = '100%';
		$label_arr['form_name'] = 'theform';

		$_form = new form();
		$_form->set_config(
			array (
				'field_arr'	=> $field_arr,
				'label_arr'	=> $label_arr,
				'value_arr'	=> $value_arr,
				'optional_arr'	=> $optional_arr
			)
		);
		return $_form->parse_field();
	}

	// create update Kelengkapan form
	function update_kelengkapan_form() {
		return $this->add_kelengkapan_form();
	}

	// handle event add Kelengkapan 
	function add_kelengkapan() {
//		if (! $this->get_permission('fill_this')) return $this->intruder();
		global $_POST, $adodb, $ses;
		$record = $_POST;
		foreach ($record as $k => $v) $record[$k] = trim($v);
		
		
		$rs = $adodb->Execute("SELECT * FROM ".$GLOBALS[my_table]." WHERE id_kelengkapan = '{$record['oldpkvalue']}'");
		if ($rs && ! $rs->EOF) {
			$adodb->Execute($adodb->GetUpdateSQL($rs, $record, 1));
			$st = "Updated";
		} else {
			$record['insert_by'] = $ses->loginid;
			$record['date_insert'] = time();
			$rs = $adodb->Execute("SELECT * FROM ".$GLOBALS[my_table]." WHERE id_kelengkapan = NULL");
			$adodb->Execute($adodb->GetInsertSQL($rs, $record));
			$st = "Added";
		}
		#$status = "Successfull $st '<b>{$record['id_kelengkapan']}</b>'";
		$status = "Successfull $st Kelengkapan ".$GLOBALS[my_title]." '<b>{$record['nama_kelengkapan']}</b>'";
		$this->log($status);
		
		$_block = new block();
		$_block->set_config('title', 'Status');
		$_block->set_config('width', "90%");
		$_block->parse(array("*".$status));
		return $_block->get_str();
	}
	
	// handle event update Kelengkapan
	function update_kelengkapan() {
		return $this->add_kelengkapan();
	}

	// handle delete Kelengkapan
	function delete_kelengkapan() {
//		if (! $this->get_permission('fill_this')) return $this->intruder();
		global ${$GLOBALS['get_vars']}, ${$GLOBALS['post_vars']};

		global $adodb;
		$query = "";
		$query3 = "";
		for ($i=0;$i<count(${$GLOBALS['post_vars']}['pk_str']);$i++) {
			if ($query) $query .= " OR ";
			if ($query3) $query3 .= " OR ";

			$var_pie = explode("&", ${$GLOBALS['post_vars']}['pk_str'][$i]);
			$query2 = "";
			$query4 = "";
			foreach ($var_pie as $key => $value) {
				list($myvar, $myval) = explode("=", $value);
				if ($query2) $query2 .= " AND ";
				$query2 .= "$myvar='".urldecode($myval)."'";
				if ($myvar=='fill_this') {
					if ($query4) $query4 .= " AND ";
					$query4 .= "$myvar='".urldecode($myval)."'";
				}
			}
			if ($query2) $query .= "($query2)";
			if ($query4) $query3 .= "($query4)";
		}

		global $self_close_js;
		if ($query) {
                        $rs = $adodb->Execute("SELECT nama_kelengkapan FROM ".$GLOBALS[my_table]." WHERE $query");
                        while (!$rs->EOF) {
                                if ($msg) $msg .= ", ";
                                $msg .= "'".$rs->fields[nama_kelengkapan]."'";
                                $rs->MoveNext();
                        }
                        $success = $adodb->Execute("delete from ".$GLOBALS[my_table]." where ".$query);
		}

                if ($success){
                        $status = '<font color=green><b>'.__('Successfull Deleted').'</b></font> '.
                                'Kelengkapan '.$GLOBALS[my_title].' <font color=red>'.$msg.'</font>';
                } else {
                        $GLOBALS['self_close_js'] = $adodb->ErrorMsg();
                        $status = '<font color=red><b>'.__('Failed Deleted').'</b></font> '.
                                'Kelengkapan '.$GLOBALS[my_title].' <font color=red>'.$msg.'</font>';
                }
                $this->log($status);
		
                $_block = new block();
                $_block->set_config('title', ('Delete Kelengkapan '.$GLOBALS[my_title].' Status'));
                $_block->set_config('width', 595);
                $_block->parse(array("*".$status));
                return $_block->get_str();
	}

	// handle list Kelengkapan
	function list_kelengkapan($extra_config='') {
//		if (! $this->get_permission('fill_this')) return $this->intruder();
		global $adodb, ${$GLOBALS['get_vars']};
		$sql = 'SELECT 
		id_kelengkapan,
		nama_kelengkapan,
		insert_by,
		date_insert
		FROM '.$GLOBALS[my_table].'
		ORDER BY nama_kelengkapan ASC
		';
		$optional_arr = $this->optional_arr;
		$optional_arr['insert_by'] = FALSE;
		$optional_arr['date_insert'] = FALSE;
		

		$vsel_arr = array (
			'id_kelengkapan' => TRUE,
			'nama_kelengkapan' => TRUE,
			'insert_by' => FALSE,
			'date_insert' => FALSE,
			'sifat' => TRUE
		);
		$eval_arr = array ();
		$pk = array (
			'id_kelengkapan' => TRUE
		);
//		if ($this->get_permission('fill_this')) {
			$add_anchor = pager::pager_button(array("link"=>'javascript:'.
			'win=openIT(\'' . $GLOBALS['PHP_SELF'] .
			'?action=add' . 
			'\', 600, 400, null, null, \'add_kelengkapan\');'.
			'win.focus()', 
			"label"=>__("Add"),
			"image"=>$GLOBALS['path_theme'].'/images/new.gif',
			"type"=>"button"));
//		}
//		if ($this->get_permission('fill_this')) {
			$edit_anchor = pager::pager_button(array("link"=>'javascript:'.
			'win=openIT(\'' . $GLOBALS['PHP_SELF'] .
			'?action=edit%s\', 600, 400, null, null, \'edit_kelengkapan\');'.
			'win.focus()', 
			"label"=>__('Edit'),
			"image"=>$GLOBALS['path_theme'].'/images/update.gif',
			"type"=>"button"));
//		}
//		if ($this->get_permission('fill_this')) {
			$del_anchor = pager::pager_button(array(
			"link"=>'javascript:confirm(\''.
			__('Confirm Delete').'?\')?(' . 
			'win=openIT(\'' . $GLOBALS['PHP_SELF'] .
			'?action=del%s\', 600, 400, null, null, \'del_kelengkapan\')'.
			'win.focus()):' . 
			'alert(\''.__('Cancelling Delete').'\');', 
			"label"=>__('Delete'),
			"image"=>$GLOBALS['path_theme'].'/images/delete.gif',
			"type"=>"button"));
//		}
		$print_anchor = pager::pager_button(array(
			"link"=>'javascript:win=openIT(\'' . $GLOBALS['PHP_SELF'] .
			'?action=print\''.
			', 600, 400, null, null, \'print_kelengkapan\');'.
			'win.focus()', 
			"label"=>__('Print'),
			"type"=>"button", 
			"image"=>$GLOBALS['path_theme'].'/images/print.gif'));

		$label_arr = $this->kelengkapan_label;
		$config = array (
			'id'		=> 'kelengkapan',
			'db'		=> &$GLOBALS['adodb'],
			'optional_arr'	=> $optional_arr,
			'label_arr'	=> $label_arr,
			'vsel_arr'	=> $vsel_arr,
			'eval_arr'	=> $eval_arr,
			'sql'		=> $sql,
			'extra_param'	=> 'action=find',
			'add_anchor'	=> $add_anchor,
			'edit_anchor'	=> $edit_anchor,
			'del_anchor'	=> $del_anchor,
			'print_anchor'	=> $print_anchor,
			'pk'		=> $pk,
			'form_width'	=> 595,
			'form_title'	=> __('List').' Kelengkapan '.$GLOBALS[my_title].''.' - '.date($GLOBALS['date_format']));
		if (is_array($extra_config)) $config = array_merge($config, $extra_config);
		$_pager = new pager($config);
		return $_pager->render();
	}

	// handle print Kelengkapan
	function print_kelengkapan() {
		$config['header_view'] = FALSE;
		$config['add_anchor'] = FALSE;
		$config['del_anchor'] = FALSE;
		$config['edit_anchor'] = FALSE;
		return $this->list_kelengkapan($config);
	}
}

} // end of define

if (! $GLOBALS['_CONTROLLED']) {
	$GLOBALS['_CONTROLLED'] = TRUE;

	$kelengkapan_controller = new kelengkapan_controller();
	$action = ${$GLOBALS['post_vars']}['action'] ? ${$GLOBALS['post_vars']}['action'] : ${$GLOBALS['get_vars']}['action'];
	switch ($action) {
		case 'add':
			$out_extra_body =  'onLoad="DocumentLoad()"';
			$out_content = $kelengkapan_controller->add_kelengkapan_form();
			$out_content .= $back_to_menu;
			break;
		case 'postadd':
			$out_content = $kelengkapan_controller->add_kelengkapan();
			$out_content .= $self_close_js;
			$out_content .= $back_to_menu;
			break;
		case 'edit':
			$out_extra_body =  'onLoad="DocumentLoad()"';
			$out_content = $kelengkapan_controller->update_kelengkapan_form();
			$out_content .= $back_to_menu;
			break;
		case 'postedit':
			$out_content = $kelengkapan_controller->update_kelengkapan();
			$out_content .= $self_close_js;
			$out_content .= $back_to_menu;
			break;
		case 'del':
			$out_content = $kelengkapan_controller->delete_kelengkapan();
			$out_content .= $self_close_js;
			$out_content .= $back_to_menu;
			break;
		case 'import':
			$out_extra_body =  'onLoad="DocumentLoad()"';
			$out_content = $kelengkapan_controller->import_kelengkapan_form();
			$out_content .= $back_to_menu;
			break;
		case 'postimport':
			$out_content = $kelengkapan_controller->import_kelengkapan();
			$out_content .= $self_close_js;
			$out_content .= $back_to_menu;
			break;
		case 'print':
			$out_content = $kelengkapan_controller->print_kelengkapan();
			$out_extra_body = 'onLoad=top.window.print()';
			break;
		case 'find':
		default:
			$out_content = $kelengkapan_controller->list_kelengkapan();
			include_once 'depkes2_menu.php';
			exit;
			break;
	}

	$_title = 'Kelengkapan '.$GLOBALS[my_title].' Administration';
	include_once 'depkes2_nonmenu.php';
} // end of CONTROLLED
?>
