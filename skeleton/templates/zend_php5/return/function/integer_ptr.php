ZVAL_LONG(
    return_value, 
<?php if($namespace_name){ ?>
    (<?=$overload_object->return_type->type?>) *(<?=$namespace_name_cpp?>::<?=$function_name?>(<?=rtrim($parameters_string, ", ")?>))
<?php } else{ ?>
    (<?=$overload_object->return_type->type?>) *(<?=$function_name?>(<?=rtrim($parameters_string, ", ")?>))
<?php } ?>
);
