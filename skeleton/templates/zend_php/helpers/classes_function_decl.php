<?php if($namespace_name){ ?>
extern zend_class_entry* php_<?=$namespace_name_var?>_<?=$class_name?>_ce;

/* <?=$namespace_name_cpp?>::<?=$class_name?> registration function */
void <?=strtolower($header_define)?>_<?=$namespace_name_var?>_<?=strtolower($class_name)?>(INIT_FUNC_ARGS);
<?php } else{ ?>
extern zend_class_entry* php_<?=$class_name?>_ce;

/* <?=$class_name?> registration function */
void <?=strtolower($header_define)?>_<?=strtolower($class_name)?>(INIT_FUNC_ARGS);
<?php } ?>
