zval z_<?=$constant_name?>;
object_init_ex(&z_<?=$constant_name?>, php_<?=$plain_type?>_entry);
((zo_<?=$plain_type?>*) zend_object_store_get_object(
        &z_<?=$constant_name?> TSRMLS_CC
))->native_object = (<?=$plain_type?>_php*) &<?=$constant_name?>;
CUSTOM_REGISTER_OBJECT_CONSTANT(
    "<?=$constant_name?>",
    z_<?=$constant_name?>,
    CONST_CS | CONST_PERSISTENT
);

