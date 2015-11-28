zend_declare_class_constant_long(
    php_<?=$enum_name?>_ce,
    "<?=$enum_option?>",
    <?=strlen($enum_option)?>,
<?php if($namespace_name){ ?>
    <?=$namespace_name_cpp?>::<?=$enum_name?>::<?=$enum_option?> 
<?php } else{ ?>
    <?=$enum_name?>::<?=$enum_option?> 
<?php } ?>
    TSRMLS_CC
);

