<?php if($namespace_name){ ?>
REGISTER_NS_STRING_CONSTANT(
    "<?=$namespace_name?>"
    "<?=$constant_name?>",
    (char*) &<?=$namespace_name_cpp?>::<?=$constant_name?>,
    CONST_CS | CONST_PERSISTENT
);
<?php } else{ ?>
REGISTER_STRING_CONSTANT(
    "<?=$constant_name?>",
    (char*) &<?=$constant_name?>,
    CONST_CS | CONST_PERSISTENT
);
<?php } ?>

