<?php
/**
 * Objects dconstructor
 */
?>
BEGIN_EXTERN_C()
zend_object_value php_<?=$class_name?>_new(zend_class_entry *class_type TSRMLS_DC)
{
    #ifdef USE_<?=strtoupper($extension)?>_DEBUG
    php_printf(
	"Calling php_<?=$class_name?>_new on %s at line %i\n", 
	zend_get_executed_filename(TSRMLS_C), 
	zend_get_executed_lineno(TSRMLS_C)
    );
    
    php_printf("===========================================\n");
    #endif
    
    zval *temp;
    zend_object_value retval;
    zo_<?=$class_name?>* custom_object;
    custom_object = (zo_<?=$class_name?>*) emalloc(sizeof(zo_<?=$class_name?>));

    zend_object_std_init(&custom_object->zo, class_type TSRMLS_CC);

#if PHP_VERSION_ID < 50399
    ALLOC_HASHTABLE(custom_object->zo.properties);
    
    zend_hash_init(
	custom_object->zo.properties, 
	0, 
	NULL, 
	ZVAL_PTR_DTOR, 
	0
    );
    
    zend_hash_copy(
	custom_object->zo.properties, 
	&class_type->default_properties, 
	(copy_ctor_func_t) zval_add_ref,
	(void *) &temp, sizeof(zval *)
    );
#else
    object_properties_init(&custom_object->zo, class_type);
#endif

    custom_object->native_object = NULL;
    custom_object->object_type = PHP_<?=strtoupper($class_name)?>_TYPE;
    custom_object->is_user_initialized = 0;

    retval.handle = zend_objects_store_put(
	custom_object, 
	NULL, 
	php_<?=$class_name?>_free, 
	NULL TSRMLS_CC
    );
    
    retval.handlers = zend_get_std_object_handlers();
    
    return retval;
}
END_EXTERN_C()

