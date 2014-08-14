<?php
/**
 * 生成OPTION数据
 * @param array  $value_list
 * @param string $select
 * @return string
 */
function html_option($value_list, $select){
	$rt = "";
	foreach($value_list as $value => $name){
		$rt .= "<option value=\"{$value}\"" . ($value == $select ? " selected" : "") . ">{$name}</option>";
	}
	return $rt;
}

/**
 * 生成一个图集的静态标签表单
 * @param string      $name        一个符合标准的标签，包含小写字母和下划线
 * @param string|null $help        帮助文字，留空为不需要
 * @param string|null $display     显示名留空为默认名称
 * @param string      $form_type   可选 input 和 textarea
 * @param string      $input_type  当$form_type参数为input 表示值类型，为textarea 时为rows的行数
 * @param string      $value       默认的传递值
 * @param null|string $placeholder 替换的显示文本
 * @return string 返回字符串
 */
function html_gallery_static_meta($name, $help = NULL, $display = NULL, $form_type = 'input', $input_type = 'text', $value = '', $placeholder = NULL){
	if(!preg_match("/^[a-z_]+$/", $name)){
		return "";
	}
	if($display === NULL){
		$display = $name;
	}
	$display = htmlspecialchars($display);
	$help = htmlspecialchars($help);
	if(!empty($help)){
		$help = "<p class='help-block'>$help</p>";
	} else{
		$help = "";
	}
	$form_type = htmlspecialchars(strtolower($form_type));
	$con = "";
	$placeholder = htmlspecialchars($placeholder);
	switch(trim($form_type)){
		case "textarea":
			$rows = is_numeric($input_type) ? intval($input_type) : 2;
			$con = '<textarea class="form-control" placeholder="' . $placeholder . '" rows="' . $rows . '" id="G_META_' . $name . '" name="' . $name . '">' . $con . '</textarea>';
			break;
		default:
			$input_type = strtolower($input_type);
			if(!in_array($input_type, [
				'button',
				'checkbox',
				'hidden',
				'image',
				'password',
				'radio',
				'reset',
				'submit',
				'text',
				'email',
				'url',
				'range',
				'number',
				'search',
				'color',
				'Date pickers',
			])
			){
				$input_type = "text";
			}
			$con = "<input name='meta[$name]' class='form-control' id='G_META_$name' type='$input_type' value='" . htmlspecialchars($value) . "' placeholder='$placeholder'>";
			if($input_type === "hidden"){
				return $con;
			}
	}
	return "<div class='form-group'><label class='control-label col-sm-2' for='G_META_$name'>$display</label><div class='col-sm-10'>$con" . "\n" . "$help</div></div>";

}