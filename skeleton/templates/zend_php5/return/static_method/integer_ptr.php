ZVAL_LONG(
    return_value, 
<?php if($namespace_name){ ?>
    (<?=$overload_object->return_type->type?>) *(<?=$namespace_name_cpp?>::<?=$class_name?>::<?=$function_name?>(<?=rtrim($parameters_string, ", ")?>))
<?php } else{ ?>
    (<?=$overload_object->return_type->type?>) *(<?=$class_name?>::<?=$function_name?>(<?=rtrim($parameters_string, ", ")?>))
<?php } ?>
);
