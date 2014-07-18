HashTable* <?=$parameter_name?>_<?=$overload?>_hash;
int <?=$overload?>_<?=$parameter_index?>_hash_count = 1;

if(arguments_received > <?=$parameter_index?>)
{
    <?=$parameter_name?>_<?=$overload?>_hash = Z_ARRVAL_P(<?=$parameter_name?>_<?=$overload?>);
    <?=$overload?>_<?=$parameter_index?>_hash_count = zend_hash_num_elements(<?=$parameter_name?>_<?=$overload?>);
}

<?=$parameter_object->type?>* <?=$parameter_name?>_<?=$overload?>_array = new <?=$parameter_object->type?>[<?=$overload?>_<?=$parameter_index?>_hash_count];
bool <?=$parameter_name?>_<?=$overload?>_hash_continue = true;

