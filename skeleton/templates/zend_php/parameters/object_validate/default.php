if(arguments_received >= <?=$parameter_index+1?>)
{
    if(Z_TYPE_P(<?=$parameter_name?>_<?=$overload?>) == IS_OBJECT)
    {
        <?=strtolower($extension)?>_object_type argument_type = (
            (php_<?=$parameter_object->type?>_zo*) 
            zend_object_store_get_object(
                <?=$parameter_name?>_<?=$overload?> TSRMLS_CC
            )
        )->object_type;
        
        argument_native_object = (void*) (
            (php_<?=$parameter_object->type?>_zo*)
            zend_object_store_get_object(
                <?=$parameter_name?>_<?=$overload?> TSRMLS_CC
            )
        )->native_object;
        
        <?=$parameter_name?>_<?=$overload?>_native = (<?=$parameter_object->type?>*) argument_native_object;
        
        if(!<?=$parameter_name?>_<?=$overload?>_native || ($typeVerifierStr))
        {
<?php if(($overload+1) != $overloads_count && $overloads_count > 1){ ?>
            goto overload_<?=$overload+1?>;
<?php } else{ ?>
            zend_error(
                E_ERROR, 
                "Parameter '<?=$parameter_name?>' could not be retrieved correctly."
            );
<?php } ?>
        }
    }
    else if(Z_TYPE_P(<?=$parameter_name?>_<?=$overload?>) != IS_NULL)
    {
<?php if(($overload+1) != $overloads_count && $overloads_count > 1){ ?>
        goto overload_<?=$overload+1?>;
<?php } else{ ?>
        zend_error(
            E_ERROR, 
            "Parameter '<?=$parameter_name?>' not null, could not be retrieved correctly."
        );
<?php } ?>
    }
}
