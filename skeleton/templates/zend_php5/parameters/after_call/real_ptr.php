size_t <?=$parameter_name?>_<?=$overload?>_elements_count = 
    sizeof(<?=$parameter_name?>_<?=$overload?>)
    /
    sizeof(*<?=$parameter_name?>_<?=$overload?>);\n"
;

array_init(<?=$parameter_name?>_<?=$overload?>_ref);

for(size_t i=0; i<<?=$parameter_name?>_<?=$overload?>_elements_count; i++)
{
    add_next_index_double(
        <?=$parameter_name?>_<?=$overload?>_ref, 
        <?=$parameter_name?>_<?=$overload?>[i]
    );
}
