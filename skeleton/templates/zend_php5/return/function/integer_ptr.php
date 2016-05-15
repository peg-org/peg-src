ZVAL_LONG(
    return_value, 
<?php if($namespace_name){ ?>
    (long) *(<?=$namespace_name_cpp?>::<?=$function_name?>(<?=rtrim($parameters_string, ", ")?>))
<?php } else{ ?>
    (long) *(<?=$function_name?>(<?=rtrim($parameters_string, ", ")?>))
<?php } ?>
);
