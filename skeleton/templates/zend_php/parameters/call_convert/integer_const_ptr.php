int <?=$parameter_name?>_<?=$overload?>_index = 0;
zval** <?=$parameter_name?>_<?=$overload?>_temp = 0;
while(<?=$parameter_name?>_<?=$overload?>_continue)
{
    if(
        zend_hash_index_find(
            HASH_OF(<?=$parameter_name?>_<?=$overload?>), 
            <?=$parameter_name?>_<?=$overload?>_index, 
            (void**) &<?=$parameter_name?>_<?=$overload?>_temp
        ) == SUCCESS
    )
    {
        convert_to_long_ex(<?=$parameter_name?>_<?=$overload?>_temp);
        <?=$parameter_name?>_<?=$overload?>_array[<?=$parameter_name?>_<?=$overload?>_index] = (<?=$parameter_object->type?>) Z_LVAL_PP(<?=$parameter_name?>_<?=$overload?>_temp);
        <?=$parameter_name?>_<?=$overload?>_index++;
    }
    else
    {
        <?=$parameter_name?>_<?=$overload?>_continue = false;
    }
}

