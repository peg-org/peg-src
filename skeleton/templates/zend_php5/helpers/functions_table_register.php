if (
    zend_register_functions(NULL, functions_table, NULL, MODULE_PERSISTENT TSRMLS_CC) 
    == 
    FAILURE
) 
{
    const char* name = "<?=$extension?>(<?=$header_name?>)";
    
    zend_error(
        E_CORE_WARNING,
        "%s: Unable to register functions, unable to load", 
        name
    );
}
