zend_class_entry* php_<?=$enum_name?>_entry;
char php_<?=$enum_name?>_name[] = "<?=$enum_name?>";
INIT_CLASS_ENTRY(ce, php_<?=$enum_name?>_name, NULL);
php_<?=$enum_name?>_entry = zend_register_internal_class(&ce TSRMLS_CC);

