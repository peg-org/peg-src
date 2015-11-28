<?php if($namespace_name){ ?>
<?=$namespace_name_cpp?>::<?=$function_name?>(<?=rtrim($parameters_string, ", ")?>);
<?php } else{ ?>
<?=$function_name?>(<?=rtrim($parameters_string, ", ")?>);
<?php } ?>

