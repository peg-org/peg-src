HashTable* <?=$parameter_name?>_<?=$overload?>_hash;
int <?=$parameter_name?>_<?=$overload?>_hash_count = 1;

if(arguments_received > <?=$parameter_index?>)
{
    <?=$parameter_name?>_<?=$overload?>_hash = Z_ARRVAL_P(<?=$parameter_name?>_<?=$overload?>);
    <?=$parameter_name?>_<?=$overload?>_hash_count = zend_hash_num_elements(<?=$parameter_name?>_<?=$overload?>);
}

bool* <?=$parameter_name?>_<?=$overload?>_array = new bool[<?=$parameter_name?>_<?=$overload?>_hash_count];
bool <?=$parameter_name?>_<?=$overload?>_hash_continue = true;

