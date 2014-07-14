char parse_references_string[] = "<?=$parse_string_ref?>"; 
                    
zend_parse_parameters_ex(
    ZEND_PARSE_PARAMS_QUIET,
    arguments_received TSRMLS_CC,
    parse_references_string,
    <?=trim($parse_reference, ", ")?>
);
