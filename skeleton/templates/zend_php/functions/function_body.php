#ifdef USE_<?=strtoupper($extension)?>_DEBUG
php_printf("Invoking function <?=$namespace_name."\\".$function_name?>\n");
php_printf("===========================================\n");
#endif

void* argument_native_object = NULL;

/* Variables used thru the code */
int arguments_received = ZEND_NUM_ARGS();
zval* dummy;
bool already_called = false;
bool return_is_user_initialized = false;

<?=$parameters_code?>
<?=$parse_code?>
<?=$call_code?>
<?=$return_code?>

/* In case wrong type/count of parameters was passed */
if(!already_called)
{
    zend_error(E_ERROR, "Wrong type or count of parameters passed to <?=$function_name?>()\n");
}
