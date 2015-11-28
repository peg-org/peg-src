zend_class_entry* php_<?=$enum_name?>_ce;
char php_<?=$enum_name?>_name[] = "<?=$enum_name?>";
<?php if($namespace_name){ ?>
INIT_NS_CLASS_ENTRY(ce, "<?=$namespace_name?>", php_<?=$enum_name?>_name, NULL);
<?php } else{ ?>
INIT_CLASS_ENTRY(ce, php_<?=$enum_name?>_name, NULL);
<?php } ?>
php_<?=$enum_name?>_ce = zend_register_internal_class_ex(&ce, peg_enum_ce, NULL TSRMLS_CC);

