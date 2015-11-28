<?php if($namespace_name){ ?>
ZEND_BEGIN_ARG_INFO_EX(<?=$namespace_name_var?>_<?=$function_name?>_args_info, 0, 0, <?=$required_parameters?>)
<?php } else{ ?>
ZEND_BEGIN_ARG_INFO_EX(<?=$function_name?>_args_info, 0, 0, <?=$required_parameters?>)
<?php } ?>
