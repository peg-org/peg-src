ZVAL_STRING(
    return_value, 
<?php if($namespace_name){ ?>
    &<?=$namespace_name_cpp?>::<?=$class_name?>::<?=$function_name?>(<?=rtrim($parameters_string, ", ")?>),
<?php } else{ ?>
    &<?=$class_name?>::<?=$function_name?>(<?=rtrim($parameters_string, ", ")?>),
<?php } ?>
    1
);
