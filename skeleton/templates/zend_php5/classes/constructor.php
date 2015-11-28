<?=$proto_header?> 
PHP_METHOD(php_<?=$class_name?>, __construct)
{
    #ifdef USE_<?=strtoupper($extension)?>_DEBUG
    php_printf("Invoking <?=$class_name?>::__construct\n");
    php_printf("===========================================\n");
    #endif
    
    /* Variables used thru the code */
    zo_<?=$class_name?>* current_object;
    <?=$class_name?>_php* native_object;
    void* argument_native_object = NULL;
    zval* dummy = NULL;
    bool already_called = false;
    int arguments_received = ZEND_NUM_ARGS();
    
    
    <?=$parse_code?> 
    
    <?=$call_code?> 
    
    <?=$return_code?> 
    
    if(already_called)
    {
        native_object->php_object = getThis();
<?php if($class_object->HasProperties()){ ?>
        
        native_object->InitProperties();
        
<?php } ?>
        current_object = (zo_<?=$class_name?>*) zend_object_store_get_object(getThis() TSRMLS_CC);
        
        current_object->native_object = native_object;
        
        current_object->is_user_initialized = 1;
        
        #ifdef ZTS 
        native_object->TSRMLS_C = TSRMLS_C;
        #endif
    }
    else
    {
        zend_error(E_ERROR, "Abstract class or wrong type/count of parameters passed to: <?=$class_name?>::__construct\n");
    }
    
    #ifdef USE_<?=strtoupper($extension)?>_DEBUG
        php_printf("===========================================\n\n");
    #endif
}
<?=$proto_footer?>
