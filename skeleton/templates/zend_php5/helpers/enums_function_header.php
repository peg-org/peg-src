BEGIN_EXTERN_C()
/* {{{ Enums registration function */
void <?=strtolower($header_define)?>_enums(INIT_FUNC_ARGS)
{
    zend_class_entry ce;
    
    