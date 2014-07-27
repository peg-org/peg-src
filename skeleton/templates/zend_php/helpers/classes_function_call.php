<?php if($namespace_name){ ?>
<?=strtolower($header_define)?>_<?=$namespace_name_var?>_<?=strtolower($class_name)?>(INIT_FUNC_ARGS_PASSTHRU);
<?php } else{ ?>
<?=strtolower($header_define)?>_<?=strtolower($class_name)?>(INIT_FUNC_ARGS_PASSTHRU);
<?php } ?>
