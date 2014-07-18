ZVAL_STRING(
    return_value, 
<?php if($namespace_name){ ?>
    (char*) <?=$namespace_name_cpp?>::<?=$function_name?>(<?=rtrim($parameters_string, ", ")?>),
<?php } else{ ?>
    (char*) <?=$function_name?>(<?=rtrim($parameters_string, ", ")?>),
<?php } ?>
    1
);
