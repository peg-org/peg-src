<?php if($namespace_name){ ?>
PHP_NS_FALIAS("<?=$namespace_name?>", <?=$function_name?>, php_<?=$namespace_name_var?>_<?=$function_name?>, NULL)
<?php } else{ ?>
PHP_FALIAS(<?=$function_name?>, php_<?=$function_name?>, NULL)
<?php } ?>
