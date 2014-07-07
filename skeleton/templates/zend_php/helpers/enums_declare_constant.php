zend_declare_class_constant_long(
    php_<?=$enum_name?>_entry,
    "<?=$enum_option?>",
    <?=strlen($enum_option)?>,
    <?=$enum_name?>::<?=$enum_option?> 
    TSRMLS_CC
);

