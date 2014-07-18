<?=$overload_object->return_type->type?>_php* value_to_return_<?=$overload?>;
<?php if($namespace_name){ ?>
value_to_return_<?=$overload?> = (<?=$overload_object->return_type->type?>_php*) &<?=$namespace_name_cpp?>::<?=$class_name?>::<?=$function_name?>(<?=rtrim($parameters_string, ", ")?>);
<?php } else{ ?>
value_to_return_<?=$overload?> = (<?=$overload_object->return_type->type?>_php*) &<?=$class_name?>::<?=$function_name?>(<?=rtrim($parameters_string, ", ")?>);
<?php } ?>

if(value_to_return_<?=$overload?>->references.IsUserInitialized())
{
    if(value_to_return_<?=$overload?>->phpObj != NULL)
    {
        *return_value = *value_to_return_<?=$overload?>->phpObj;
        zval_add_ref(&value_to_return_<?=$overload?>->phpObj);
        return_is_user_initialized = true;
    }
    else
    {
        zend_error(E_ERROR, "Could not retreive original zval.");
    }
}
else
{
    object_init_ex(return_value, php_<?=$overload_object->return_type->type?>_ce);
    (
        (php_<?=$overload_object->return_type->type?>_zo*) 
        zend_object_store_get_object(return_value TSRMLS_CC)
    )->native_object = (<?=$overload_object->return_type->type?>_php*) value_to_return_<?=$overload?>;
}
