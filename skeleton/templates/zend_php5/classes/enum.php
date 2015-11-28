zend_declare_class_constant_long(
    php_<?=$class_name?>_entry,
    "<?=$enum_option?>",
    <?=strlen($enum_option)?>,
    <?=$class_name?>::<?=$enum_option?> 
    TSRMLS_CC
);

