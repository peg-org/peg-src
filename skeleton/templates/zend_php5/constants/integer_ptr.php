<?php if($namespace_name){ ?>
REGISTER_NS_LONG_CONSTANT(
    "<?=$namespace_name?>"
    "<?=$constant_name?>",
    *<?=$namespace_name_cpp?>::<?=$constant_name?>,
    CONST_CS | CONST_PERSISTENT
);
<?php } else{ ?>
REGISTER_LONG_CONSTANT(
    "<?=$constant_name?>",
    *<?=$constant_name?>,
    CONST_CS | CONST_PERSISTENT
);
<?php } ?>

