ZVAL_STRING(
    return_value, 
<?php if($namespace_name){ ?>
    (char*) <?=$namespace_name_cpp?>::<?=$class_name?>::<?=$function_name?>(<?=rtrim($parameters_string, ", ")?>),
<?php } else{ ?>
    (char*) <?=$class_name?>::<?=$function_name?>(<?=rtrim($parameters_string, ", ")?>),
<?php } ?>
    1
);
