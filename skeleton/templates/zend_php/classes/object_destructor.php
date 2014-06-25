<?php
/**
 * Objects destructor
 */
?>
BEGIN_EXTERN_C()
void php_<?=$class_name?>_free(void *object TSRMLS_DC) 
{
    zo_<?=$class_name?>* custom_object = (zo_<?=$class_name?>*) object;
    
    #ifdef USE_<?=strtoupper($extension)?>_DEBUG
    php_printf("Calling php_<?=$class_name?>_free on %s at line %i\n", zend_get_executed_filename(TSRMLS_C), zend_get_executed_lineno(TSRMLS_C));
    php_printf("===========================================\n");
    #endif

    if(custom_object->native_object != NULL)
    {
	#ifdef USE_<?=strtoupper($extension)?>_DEBUG
	php_printf("Pointer not null\n");
	php_printf("Pointer address %x\n", (unsigned int)(size_t)custom_object->native_object);
	#endif

	if(custom_object->is_user_initialized)
	{
	    #ifdef USE_<?=strtoupper($extension)?>_DEBUG
	    php_printf("Deleting pointer of <?=$class_name?> with delete\n");
	    #endif

	    delete custom_object->native_object;		
	    custom_object->native_object = NULL;
	}

	#ifdef USE_<?=strtoupper($extension)?>_DEBUG
	php_printf("===========================================\n\n");
	#endif
    }
    else
    {
	#ifdef USE_<?=strtoupper($extension)?>_DEBUG
	php_printf("Not user space initialized\n");
	#endif
    }

    zend_object_std_dtor(&custom_object->zo TSRMLS_CC);
    efree(custom_object);
}
END_EXTERN_C()

