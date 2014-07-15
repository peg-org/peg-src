#ifdef USE_<?=strtoupper($extension)?>_DEBUG
<?php if($namespace_name){ ?>
php_printf("Invoking function <?=$namespace_name."\\".$function_name?>\n");
<?php } else{ ?>
php_printf("Invoking function <?=$function_name?>\n");
<?php } ?>
php_printf("===========================================\n");
#endif

/* Variables used thru the code */
int arguments_received = ZEND_NUM_ARGS();
zval* dummy;
bool already_called = false;
bool return_is_user_initialized = false;
void* argument_native_object = NULL;

<?=$parameters_code?>
<?=$parse_code?>
<?=$call_code?>
<?=$return_code?>

/* In case wrong type/count of parameters was passed */
if(!already_called)
{
    zend_error(
        E_ERROR, 
<?php if($namespace_name){ ?>
        "Wrong type or count of parameters passed to <?=$namespace_name."\\".$function_name?>()\n"
<?php } else{ ?>
        "Wrong type or count of parameters passed to <?=$function_name?>()\n"
<?php } ?>
    );
}