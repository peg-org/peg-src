<?=$overload_object->return_type->type?>_php value_to_return_<?=$overload?>;
<?php if($namespace_name){ ?>
value_to_return_<?=$overload?> = <?=$namespace_name_cpp?>::<?=$class_name?>::<?=$function_name?>(<?=rtrim($parameters_string, ", ")?>);
<?php } else{ ?>
value_to_return_<?=$overload?> = <?=$class_name?>::<?=$function_name?>(<?=rtrim($parameters_string, ", ")?>);
<?php } ?>

void* ptr = safe_emalloc(
    1, 
    sizeof(<?=$overload_object->return_type->type?>_php),
    0
);

memcpy(
    ptr, 
    (void*) &value_to_return_<?=$overload?>, 
    sizeof(<?=$overload_object->return_type->type?>)
);

object_init_ex(return_value, php_<?=$overload_object->return_type->type?>_ce);

((<?=$overload_object->return_type->type?>_php*)ptr)->phpObj = return_value;

<?php if(class_has_properties($return_type)){ ?>
((<?=$overload_object->return_type->type?>_php*)ptr)->InitProperties();
<?php } ?>

php_<?=$overload_object->return_type->type?>_zo* zo_<?=$overload?> = 
    (php_<?=$overload_object->return_type->type?>_zo*) 
    zend_object_store_get_object(return_value TSRMLS_CC)
;

zo_<?=$overload?>->native_object = (<?=$overload_object->return_type->type?>_php*) ptr;
