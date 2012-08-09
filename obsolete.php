<?php
function drawCheckboxText($chkname, $description) {
	return draw_container("span", $description, array("class"=>"clickme", "onclick"=>"javascript:toggleCheckbox('$chkname');"));
}

function drawdrawColumnDelete($prompt=false, $id=false, $action="delete", $adminOnly=true) {
	//if we're going to backend all the table stuff, then this should be incorporated somehow.  perhaps we will need to extend the class
	global $page, $_josh;
	if ($adminOnly && !$page['is_admin']) return false;
	if (!$id) return '<td width="16">&nbsp;</td>';
	return draw_tag("td", array("width"=>"16"), draw_img("/images/icons/delete.png", drawDeleteLink($prompt, $id, $action)));
}

function drawDeleteLink($prompt="Are you sure?", $id=false, $action="delete", $index="id") {
	global $_GET;
	if (!$id && isset($_GET[$index])) $id = $_GET[$index];
	$prompt = "'" . str_replace("'", '"', $prompt) . "'";
	return "javascript:url_prompt('" . url_query_add(array("action"=>$action, $index=>$id), false) . "', " . $prompt . ");";
}

function drawEmptyResult($text="None found.", $colspan=1) {
	//todo ~ obsolete.  tables should use joshlib's table class
	return draw_tag("tr", false, draw_tag("td", array("class"=>"empty", "colspan"=>$colspan), $text));
}

function drawNavigationRow($pages, $module=false, $pq=false) {
	global $_josh;
	$count = count($pages);
	if ($count < 2) return false;
	$return = '<table class="navigation" cellspacing="1">
		<tr class="hilite">';
	$cellwidth = round(100 / $count, 2);
	$match = ($pq) ? $_josh["request"]["path_query"] : $_josh["request"]["path"];
	//echo $match;  don't put url_base in match, if you can help it
	foreach ($pages as $url=>$name) {
		if (($url == $match) || ($url == url_base() . $match)) {
			$cell = ' class="selected">' . $name . '';
		} else {
			$cell = '><a href="' . $url . '">' . $name . '</a>';
		}
		$return .= '<td width="' . $cellwidth . '%"' . $cell . '</td>';
	}
	return $return . '</tr>
		</table>';
}
	
function drawHeaderRow($name=false, $colspan=1, $link1text=false, $link1link=false, $link2text=false, $link2link=false) {
	global $_josh, $modules, $modulettes, $page;
	if (!$name) $name = $page['breadcrumbs'] . $page['title'];
	//urls are absolute because it could be used in an email
	$header ='<tr>
			<td class="head" colspan="' . $colspan . '">
				<div class="left">' . $name . '</div>';
	if ($link2link && $link2text) $header .= '<a class="right" href="' . $link2link . '">' . $link2text . '</a>';
	if ($link1link && $link1text) $header .= '<a class="right" href="' . $link1link . '">' . $link1text . '</a>';
	$header .='</td></tr>';
	return $header;
}

class intranet_form {
	//todo ~ this is obsolete.  we should either use or extend the form class in joshlib
	var $rows, $js;
	
	function addUser($name="user_id", $desc="User", $default=false, $nullable=false, $admin=false) {
		global $rows;
		$class = ($admin) ? "admin hilite" : false;
		$rows .= draw_container("tr", 
			draw_container("td", $desc, array("class"=>"left")) . 
			draw_container("td", drawSelectUser($name, $default, $nullable), array("class"=>$class))
		);
	}
	
	function addCheckbox($name="", $desc="", $default=false, $additionalText="(check if yes)", $admin=false) {
		global $rows;
		$rows .= '<tr>
			<td class="left">' . $desc . '</td>
			<td';
		if ($admin) $rows .= ' class="admin hilite"';
		$rows .= '><table class="nospacing">
					<tr>
						<td>' . draw_form_checkbox($name, $default) . '</td>
						<td>' . drawCheckboxText($name, $additionalText) . '</td>
					</tr>
				</table>
			</td>
		</tr>';
	}
	
	function addCheckboxes($name, $desc, $table, $linking_table=false, $table_col=false, $link_col=false, $id=false, $admin=false) {
		global $rows;
		$rows .= '<tr';
		if ($admin) $rows .= ' class="admin"';
		
		$title = "title";
		$checked = 0;
		
		//special addition for permissions
		$where = ($name == "permissions") ? " AND l.is_admin = 1" : "";
		
		//special exception for channels table
		if ($table == "channels") {
			$title = "title" . langExt();
			$checked = 1;
		}

		$rows .= '>
			<td class="left">' . $desc . '</td>
			<td>';
			if ($id) {
				$result = db_query("SELECT 
						t.id, 
						t.$title,
						(SELECT COUNT(*) FROM $linking_table l WHERE l.$table_col = $id AND l.$link_col = t.id $where) checked
					FROM $table t
					WHERE t.is_active = 1
					ORDER BY t.$title");
			} else {
				$result = db_query("SELECT id, $title, $checked checked FROM $table WHERE is_active = 1 ORDER BY $title");
			}
			if ($total = db_found($result)) {
				$counter = 0;
				$max = ceil($total / 3);
				$rows .= '<table class="nospacing" width="100%"><tr>';
				while ($r = db_fetch($result)) {
					if ($counter == 0) $rows .= '<td width="33%" style="vertical-align:top;"><table class="nospacing">';
					$chkname = "chk-" . $name . "-" . $r["id"];
					$rows .= '
						<tr>
						<td>' . draw_form_checkbox($chkname, $r["checked"]) . '</td>
						<td>' . drawCheckboxText($chkname, $r[$title]) . '</td>
						</tr>';
					if ($counter == ($max - 1)) {
						$rows .= '</table></td>';
						$counter = 0;
					} else {
						$counter++;
					}
				}
				if ($counter != 0) $rows .= '</table></td>';
				$rows .= '</tr></table>';
			}
			$rows .= '
			</td>
		</tr>';
	}
	
	function addSelect($name="", $desc="", $sql="", $default=0, $nullable=false, $bgcolor=false) {
		global $rows;
		$rows .= draw_container("tr", 
			draw_container("td", $desc) . 
			draw_container("td", draw_form_select($name, $sql, $default, $nullable))
		);
	}
	
	function addJavascript($conditions, $message) {
		global $js;
		$js .= "
			if (" . $conditions . ") errors[errors.length] = '" . addslashes($message) . "';
		";
	}
	
	function addRaw($row) {
		global $rows;
		$rows .= $row;
	}
	
	function addGroup($text="") {
		global $rows;
		$rows .= draw_container("tr", draw_container("td", $text, array("colspan"=>2)), array("class"=>"group"));
	}
		
	function addRow($type, $title, $name="", $value="", $default="", $required=false, $maxlength=50, $onchange=false) {
		global $rows, $js, $months, $month, $today, $year, $_josh;
		$textlength = ($maxlength > 50) ? 50 : $maxlength;
		$value = trim($value);
		if ($type == "raw") {
			$rows .= $title;
		} else {
			$rows .= '<tr>';
			if (($type != "button") && ($type != "submit") && ($type != "hidden") && ($type != "raw")) $rows .= '<td class="left">' . $title . '</td>';
			
			if ($type == "text") { //output text, no form element
				$rows .= '<td>' . $value . '</td>';
			} elseif ($type == "date") {
				$rows .= '<td>' . draw_form_date($name, $value, false, false, $required) . '</td>';
			} elseif ($type == "datetime") {
				$rows .= '<td>' . draw_form_date($name, $value, true) . '</td>';
			} elseif ($type == "checkbox") {
				$rows .= '<td>' . draw_form_checkbox($name, $value) . '</td>';
			} elseif ($type == "itext") {
				$rows .= '<td>' . draw_form_text($name, $value, false, $maxlength) . '</td>';
				if ($required) $js .= "if (!form." . $name . ".value.length) errors[errors.length] = 'the \'" . $title . "\' field is empty';" . NEWLINE;
			} elseif ($type == "phone") {
				$rows .= '<td>' . draw_form_text($name, $value, 14, $maxlength) . '</td>';
				if ($required) $js .= "if (!form." . $name . ".value.length) errors[errors.length] = 'the \'" . $title . "\' field is empty';" . NEWLINE;
			} elseif ($type == "extension") {
				$rows .= '<td>' . draw_form_text($name, $value, 4, $maxlength) . '</td>';
				if ($required) $js .= "if (!form." . $name . ".value.length) errors[errors.length] = 'the \'" . $title . "\' field is empty';" . NEWLINE;
			} elseif ($type == "password") {
				$rows .= '<td>' . draw_form_password($name, $value, $textlength, $maxlength) . '</td>';
				if ($required) $js .= "if (!form." . $name . ".value.length) errors[errors.length] = 'the \'" . $title . "\' field is empty';" . NEWLINE;
			} elseif ($type == "select") {
				$rows .= '<td>';
				$rows .= draw_form_select($name, $value, $default, $required, false, $onchange);
				$rows .= '</td>';
			} elseif ($type == "user") {
				$result = db_query("SELECT 
										id, 
										ISNULL(nickname, firstname) first,
										lastname last 
									FROM users
									WHERE is_active = 1
									ORDER by lastname");
				while ($r = db_fetch($result)) {
					$options[$r["user_id"]] = $r["first"] . ", " . $r["last"];
				}
				$rows .= '<td>';
				$rows .= draw_form_select($name, $options, $default, $required, false, $onchange);
				$rows .= '</td>';
			} elseif ($type == "department") {
				$rows .= '<td><select name="' . $name . '">';
				if (!$required) $rows .= "<option></option>";
				$result = db_query("SELECT 
										departmentID,
										departmentName,
										quoteLevel
									FROM departments
									WHERE is_active = 1
									ORDER by precedence");
				while ($r = db_fetch($result)) {
					$rows .= '<option value="' . $r["departmentID"] . '"';
					if ($r["departmentID"] == $default) $rows .= ' selected';
					$rows .= '>';
					if ($r["quoteLevel"] == 2) {
						$rows .= "&nbsp;&#183;&nbsp;";
					} elseif ($r["quoteLevel"] == 3) {
						$rows .= "&nbsp;&nbsp;&nbsp;-&nbsp;";
					}
					$rows .= $r["departmentName"] . '</option>';
				}
				$rows .= '</select></td>';
			} elseif ($type == "userpic") {
				$rows .= '<td>' . drawName($name, $value, $default, true, " ") . '</td>';
			} elseif ($type == "textarea") {
				$rows .= '<td>' . draw_form_textarea($name, $value, "mceEditor") . '</td>';
				$js .= " tinyMCE.triggerSave();" .  NEWLINE;
				if ($required) $js .= "if (!form." . $name . ".value.length || (form." . $name . ".value == '<p>&nbsp;</p>')) errors[errors.length] = 'the \'" . $title . "\' field is empty';" . NEWLINE;
			} elseif ($type == "textarea-plain") {
				$rows .= '<td>' . draw_form_textarea($name, $value, "noMceEditor") . '</td>';
				if ($required) $js .= "if (!form." . $name . ".value.length) errors[errors.length] = 'the \'" . $title . "\' field is empty';" . NEWLINE;
			} elseif ($type == "hidden") {
				$rows .= draw_form_hidden($name, $value);
			} elseif ($type == "submit") {
				$rows .= '<td colspan="2" align="center" class="bottom">' . draw_form_submit($title, "button") . '</td>';
			} elseif ($type == "button") {
				$rows .= '<td colspan="2" align="center" class="bottom">' . draw_form_button($title, $value, "button") . '</td>';
			} elseif ($type == "file") {
				$rows .= '<td>' . draw_form_file($name, "file", $onchange) . '</td>';
			}
			$rows .= '</tr>' . NEWLINE;
		}
	}	
	
	function draw($pageTitle) {
		global $rows, $_josh, $js;
		if ($js) {
		echo draw_javascript("function validate(form) {
				var errors = new Array();
				" . $js . "
				return form_errors(errors);
			}");
		}?>
		<a name="bottom"></a>
		<table class="left" cellspacing="1">
			<tr>
				<td class="head" colspan="2"><?=$pageTitle?></td>
			</tr>
			<form method="post" action="<?=$_josh["request"]["path_query"]?>" enctype="multipart/form-data" accept-charset="utf-8" onsubmit="javascript:return validate(this);">
			<?
			if (isset($_josh["referrer"])) {
				echo draw_form_hidden("return_to", $_josh["referrer"]["url"]);
			}
			echo $rows;
			?>
			</form>
		</table>
		<?php
	}
}