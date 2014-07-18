BEGIN_EXTERN_C()
<?=$proto_header?>
<?php if($namespace_name){ ?>
PHP_FUNCTION(php_<?=$namespace_name_var?>_<?=$function_name?>)
<?php } else{ ?>
PHP_FUNCTION(php_<?=$function_name?>)
<?php } ?>
{
    