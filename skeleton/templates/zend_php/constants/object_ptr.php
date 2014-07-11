<?php if($namespace_name){ ?>
zval z_<?=$namespace_name_var?>_<?=$constant_name?>;
object_init_ex(
    &z_<?=$namespace_name_var?>_<?=$constant_name?>, 
    php_<?=$namespace_name_var?>_<?=$plain_type?>_ce
);
((php_<?=$namespace_name_var?>_<?=$plain_type?>_zo*) zend_object_store_get_object(
        &z_<?=$namespace_name_var?>_<?=$constant_name?> TSRMLS_CC
))->native_object = (<?=$namespace_name_var?>_<?=$plain_type?>_php*) <?=$namespace_name_cpp?>::<?=$constant_name?>;
CUSTOM_REGISTER_OBJECT_CONSTANT(
    "<?=$namespace_name?>\<?=$constant_name?>",
    z_<?=$namespace_name_var?>_<?=$constant_name?>,
    CONST_CS | CONST_PERSISTENT
);
<?php } else{ ?>
zval z_<?=$constant_name?>;
object_init_ex(&z_<?=$constant_name?>, php_<?=$plain_type?>_ce);
((php_<?=$plain_type?>_zo*) zend_object_store_get_object(
        &z_<?=$constant_name?> TSRMLS_CC
))->native_object = (<?=$plain_type?>_php*) <?=$constant_name?>;
CUSTOM_REGISTER_OBJECT_CONSTANT(
    "<?=$constant_name?>",
    z_<?=$constant_name?>,
    CONST_CS | CONST_PERSISTENT
);
<?php } ?>

