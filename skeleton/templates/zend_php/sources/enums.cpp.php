/*
  +----------------------------------------------------------------------+
  | PHP Version 5                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2012 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_01.txt                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Author: Elizabeth M Smith <auroraeosrose@php.net>                    |
  +----------------------------------------------------------------------+
*/

#include "enums.h"

#include <zend_exceptions.h>
#include <ext/spl/spl_exceptions.h>

/* enum object */
struct _peg_enum_object {
    zend_object std;
    zend_bool   is_constructed;
    long        value;
    HashTable  *elements;
};

/* enum struct object */
typedef struct _peg_enum_object peg_enum_object;

#define PHP_PEG_EXCEPTIONS \
    zend_error_handling error_handling; \
    zend_replace_error_handling(EH_THROW, spl_ce_InvalidArgumentException, &error_handling TSRMLS_CC);

#define PHP_PEG_RESTORE_ERRORS \
    zend_restore_error_handling(&error_handling TSRMLS_CC);

/* ----------------------------------------------------------------
    Helper functions prototype
------------------------------------------------------------------*/

static int peg_enum_apply_set(long *option TSRMLS_DC, int num_args, va_list args, zend_hash_key *hash_key);
static int peg_enum_collect_elements(long *value TSRMLS_DC, int num_args, va_list args, zend_hash_key *hash_key);
static int peg_enum_collect_constants(zval **pzconst TSRMLS_DC, int num_args, va_list args, zend_hash_key *hash_key);
static void peg_enum_object_free(void *object TSRMLS_DC);


/* ----------------------------------------------------------------
    Peg\Enum C API
------------------------------------------------------------------*/

BEGIN_EXTERN_C()
/* {{{ exported function to take a zval** enum instance and give you back the long value */

ZEND_API long php_peg_get_enum_value(zval** enumclass TSRMLS_DC)
{
    peg_enum_object *enum_object;

    enum_object = (peg_enum_object *) zend_object_store_get_object(*enumclass TSRMLS_CC);
    return enum_object->value;
}
/* }}} */

/* {{{ exported function to take long and stick it in an enum class
        WARNING: This does NOT check values, so make sure you don't screw up */
ZEND_API void php_peg_set_enum_value(zval** enumclass, long value TSRMLS_DC)
{
    peg_enum_object *enum_object;

    enum_object = (peg_enum_object *) zend_object_store_get_object(*enumclass TSRMLS_CC);
    enum_object->value = value;
}
/* }}} */
END_EXTERN_C()

/* ----------------------------------------------------------------
    Peg\Enum class API
------------------------------------------------------------------*/

ZEND_BEGIN_ARG_INFO(Peg_Enum___construct_args, ZEND_SEND_BY_VAL)
    ZEND_ARG_INFO(0, value)
ZEND_END_ARG_INFO()

/* {{{ proto void Enum->__construct(mixed value)
                  constructor for enum class, string can be a special case
                  anything else is cast and we attempt to assign */
PHP_METHOD(Peg_Enum, __construct)
{
    peg_enum_object *enum_object;
    long *new_val;
    zend_bool found = FALSE;

    /* for zpp */
    long value;
    zval *cast = NULL;

    /* Valid - send a valid long, send a string of a set item, send any other item and it will
     * be cast to a long and see if it's set */
    PHP_PEG_EXCEPTIONS
    if (FAILURE == zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET, ZEND_NUM_ARGS() TSRMLS_CC,"l", &value)) {
        if (FAILURE == zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &cast)) {
            return;
        }
    }
    PHP_PEG_RESTORE_ERRORS

    enum_object = (peg_enum_object *) zend_object_store_get_object(getThis() TSRMLS_CC);

    /* handle cast zval */
    if(NULL != cast) {
        if (Z_TYPE_P(cast) == IS_STRING &&
            SUCCESS == zend_hash_find(enum_object->elements, Z_STRVAL_P(cast), Z_STRLEN_P(cast) + 1, (void **)&new_val)) {
            enum_object->value = *new_val;
            return;
        }
        convert_to_long(cast);
        value = Z_LVAL_P(cast);
    }

    /* handle the "easy" case of a long */
    zend_hash_apply_with_arguments(enum_object->elements TSRMLS_CC, (apply_func_args_t)peg_enum_apply_set, 2, &value, &found TSRMLS_CC);

    if(found) {
        enum_object->value = value;
        return;
    }

    zend_throw_exception_ex(spl_ce_UnexpectedValueException, 0 TSRMLS_CC, "Value provided is not a const in enum %s", enum_object->std.ce->name);
}
/* }}} */

/* {{{ proto string Enum->getName()
get member name of current enum value */
PHP_METHOD(Peg_Enum, getName)
{
    peg_enum_object *enum_object;
    char *key;
    ulong pos;
    long *value;

    PHP_PEG_EXCEPTIONS
    if (FAILURE == zend_parse_parameters_none()) {
        return;
    }
    PHP_PEG_RESTORE_ERRORS

    enum_object = (peg_enum_object *) zend_object_store_get_object(getThis() TSRMLS_CC);

    zend_hash_internal_pointer_reset(enum_object->elements);

    while (zend_hash_get_current_key(enum_object->elements, &key, &pos, 0) == HASH_KEY_IS_STRING) {
        if (zend_hash_get_current_data(enum_object->elements, (void **)&value) == SUCCESS &&
            *value == enum_object->value) {
            RETURN_STRING(key, 1);
        }
        zend_hash_move_forward(enum_object->elements);
    }

}
/* }}} */

/* {{{ proto array Enum->getElements()
                   get array of available name => value pairs */
PHP_METHOD(Peg_Enum, getElements)
{
    peg_enum_object *enum_object;

    PHP_PEG_EXCEPTIONS
    if (FAILURE == zend_parse_parameters_none()) {
        return;
    }
    PHP_PEG_RESTORE_ERRORS

    enum_object = (peg_enum_object *) zend_object_store_get_object(getThis() TSRMLS_CC);

    array_init(return_value);
    zend_hash_apply_with_arguments(enum_object->elements TSRMLS_CC, (apply_func_args_t)peg_enum_collect_elements, 1, return_value);
}
/* }}} */

/* ----------------------------------------------------------------
    Peg\Enum Object management
------------------------------------------------------------------*/

/* {{{ peg_enum_object_create */
static zend_object_value peg_enum_object_create(zend_class_entry *ce TSRMLS_DC)
{
    zend_object_value retval;
    peg_enum_object *enum_object;

    enum_object = ecalloc(1, sizeof(peg_enum_object));
    zend_object_std_init((zend_object *) enum_object, ce TSRMLS_CC);
    enum_object->is_constructed = FALSE;
    enum_object->value = 0;
    ALLOC_HASHTABLE(enum_object->elements);
    zend_hash_init(enum_object->elements, 8, NULL, NULL, 0);

    object_properties_init(&enum_object->std, ce);

    zend_update_class_constants(ce TSRMLS_CC);

    zend_hash_apply_with_arguments(&ce->constants_table TSRMLS_CC, (apply_func_args_t)peg_enum_collect_constants, 2, enum_object->elements, enum_object->std.ce->name);

    retval.handle = zend_objects_store_put(enum_object,
        (zend_objects_store_dtor_t) zend_objects_destroy_object,
        (zend_objects_free_object_storage_t) peg_enum_object_free,
        NULL TSRMLS_CC);

    retval.handlers = &peg_enum_handlers;
    return retval;
}
/* }}} */

/* {{{ peg_enum_get */
static zval* peg_enum_get(zval *zobject TSRMLS_DC)
{
    peg_enum_object *enum_object = (peg_enum_object *) zend_object_store_get_object(zobject TSRMLS_CC);
    zval *value;

    MAKE_STD_ZVAL(value);
    ZVAL_LONG(value, enum_object->value);
    Z_SET_REFCOUNT_P(value, 0);

    return value;
}
/* }}} */

/* {{{ peg_enum_set */
static void peg_enum_set(zval **zobject, zval *value TSRMLS_DC)
{
    peg_enum_object *enum_object = (peg_enum_object *) zend_object_store_get_object(*zobject TSRMLS_CC);
    zend_bool found = FALSE;
    long lvalue;
    zval *juggled;

    if (Z_TYPE_P(value) == IS_OBJECT && Z_OBJ_HANDLER_P(value, get)) {
        juggled = Z_OBJ_HANDLER_P(value, get)(value TSRMLS_CC);
    } else {
        juggled = value;
    }

    /* if we have a string, do a zend_hash_find FIRST */
    if (Z_TYPE_P(juggled) == IS_STRING) {
        long *new_val;

        if (SUCCESS == zend_hash_find(enum_object->elements, Z_STRVAL_P(juggled), Z_STRLEN_P(juggled) + 1, (void **)&new_val)) {
            enum_object->value = *new_val;

            if (Z_TYPE_P(value) == IS_OBJECT && Z_OBJ_HANDLER_P(value, get)) {
                zval_dtor(juggled);
                FREE_ZVAL(juggled);
            }
            return;
        }
    }

    /* If juggled is long or a string, leave them alone, otherwise juggle */
    if (Z_TYPE_P(juggled) != IS_LONG) {
        convert_to_long(juggled);
    }
    lvalue = Z_LVAL_P(juggled);

    zend_hash_apply_with_arguments(enum_object->elements TSRMLS_CC, (apply_func_args_t)peg_enum_apply_set, 2, &lvalue, &found TSRMLS_CC);

    if (!found) {
        zend_throw_exception_ex(spl_ce_UnexpectedValueException, 0 TSRMLS_CC, "Value provided is not a const in enum %s", enum_object->std.ce->name);
    } else {
        enum_object->value = Z_LVAL_P(juggled);
    }

    if (Z_TYPE_P(value) == IS_OBJECT && Z_OBJ_HANDLER_P(value, get)) {
        zval_dtor(juggled);
        FREE_ZVAL(juggled);
    }
}
/* }}} */

/* {{{ peg_enum_cast */
static int peg_enum_cast(zval *readobj, zval *writeobj, int type TSRMLS_DC)
{
    peg_enum_object *enum_object = (peg_enum_object *) zend_object_store_get_object(readobj TSRMLS_CC);

    ZVAL_LONG(writeobj, enum_object->value);
    convert_to_explicit_type(writeobj, type);
    return SUCCESS;
}
/* }}} */

/* {{{ peg_enum_compare */
static int peg_enum_compare(zval *z1, zval *z2 TSRMLS_DC)
{
    if (Z_TYPE_P(z1) == IS_OBJECT && Z_TYPE_P(z2) == IS_OBJECT &&
        instanceof_function(Z_OBJCE_P(z1), peg_enum_ce TSRMLS_CC) &&
        instanceof_function(Z_OBJCE_P(z2), peg_enum_ce TSRMLS_CC)) {

            peg_enum_object *enum1 = (peg_enum_object *) zend_object_store_get_object(z1 TSRMLS_CC);
            peg_enum_object *enum2 = (peg_enum_object *) zend_object_store_get_object(z2 TSRMLS_CC);

            return (enum1->value == enum2->value) ? 0 : ((enum1->value < enum2->value) ? -1 : 1);
        }

    return 1;
}
/* }}} */

/* {{{ peg_enum_clone */
static zend_object_value peg_enum_clone(zval *zobject TSRMLS_DC)
{
    zend_object_value retval;
    peg_enum_object *new_object;
    peg_enum_object *old_object = (peg_enum_object *) zend_object_store_get_object(zobject TSRMLS_CC);

    retval = peg_enum_object_create(old_object->std.ce TSRMLS_CC);
    new_object = (peg_enum_object *) zend_object_store_get_object_by_handle(retval.handle TSRMLS_CC);

    zend_objects_clone_members(&new_object->std, retval, &old_object->std, Z_OBJ_HANDLE_P(zobject) TSRMLS_CC);

    new_object->value = old_object->value;

    return retval;
}
/* }}} */

/* {{{ peg_enum_debug_info */
static HashTable* peg_enum_debug_info(zval *obj, int *is_temp TSRMLS_DC)
{
    HashTable *debug_info, *std_props;
    zval *elements, *value;
    peg_enum_object *enum_object = (peg_enum_object *) zend_object_store_get_object(obj TSRMLS_CC);

    ALLOC_HASHTABLE(debug_info);
    zend_hash_init(debug_info, 2, NULL, ZVAL_PTR_DTOR, 0);

    std_props = zend_std_get_properties(obj TSRMLS_CC);
    zend_hash_copy(debug_info, std_props, (copy_ctor_func_t)zval_add_ref, NULL, sizeof(zval*));

    MAKE_STD_ZVAL(elements);
    array_init(elements);
    zend_hash_apply_with_arguments(enum_object->elements TSRMLS_CC, (apply_func_args_t)peg_enum_collect_elements, 1, elements);
    zend_hash_update(debug_info, "__elements", sizeof("__elements"), (void*)&elements, sizeof(zval *), NULL);

    MAKE_STD_ZVAL(value);
    ZVAL_LONG(value, enum_object->value);
    zend_hash_update(debug_info, "__value", sizeof("__value"), (void*)&value, sizeof(zval *), NULL);

    *is_temp = 1;
    return debug_info;
}
/* }}} */

/* ----------------------------------------------------------------
    Helper functions
------------------------------------------------------------------*/

/* {{{ peg_enum_object_free */
static void peg_enum_object_free(void *object TSRMLS_DC)
{
    peg_enum_object *enum_object = (peg_enum_object *)object;

    zend_object_std_dtor(&enum_object->std TSRMLS_CC);
    enum_object->is_constructed = FALSE;
    enum_object->value = 0;
    zend_hash_destroy(enum_object->elements);
    FREE_HASHTABLE(enum_object->elements);

    efree(enum_object);
}
/* }}} */

/* {{{ peg_enum_apply_set */
static int peg_enum_apply_set(long *option TSRMLS_DC, int num_args, va_list args, zend_hash_key *hash_key) 
{
    long *value = va_arg(args, long*);
    zend_bool *found = va_arg(args, zend_bool*);

    if(*value == *option) {
        *found = TRUE;
        return ZEND_HASH_APPLY_STOP;
    }

    return ZEND_HASH_APPLY_KEEP;
}
/* }}} */

/* {{{ peg_enum_collect_elements
       helper function for getElements call to collect all values */
static int peg_enum_collect_elements(long *value TSRMLS_DC, int num_args, va_list args, zend_hash_key *hash_key) /* {{{ */
{
    zval *return_value = va_arg(args, zval*);

    add_assoc_long(return_value, hash_key->arKey, *value);

    return ZEND_HASH_APPLY_KEEP;
}
/* }}} */

/* {{{ peg_enum_collect_constants */
static int peg_enum_collect_constants(zval **pzconst TSRMLS_DC, int num_args, va_list args, zend_hash_key *hash_key)
{
    HashTable *elements = va_arg(args, HashTable*);
    char *classname = va_arg(args, char*);

    if(Z_TYPE_PP(pzconst) != IS_LONG) {
        php_error_docref(NULL TSRMLS_CC, E_WARNING, "Constant %s is being cast to an integer in Enum subclass %s", hash_key->arKey, classname);
        convert_to_long(*pzconst);
    }
    zend_hash_add(elements, hash_key->arKey, hash_key->nKeyLength, &Z_LVAL_PP(pzconst), sizeof(long), NULL);

    return ZEND_HASH_APPLY_KEEP;
}
/* }}} */

/* ----------------------------------------------------------------
    Peg\Enum registration
------------------------------------------------------------------*/

zend_class_entry *peg_enum_ce;
zend_object_handlers peg_enum_handlers;

/* {{{ class methods */
static const zend_function_entry peg_enum_methods[] = {
    PHP_ME(Peg_Enum, __construct, Enum___construct_args, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
    PHP_ME(Peg_Enum, getName, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(Peg_Enum, getElements, NULL, ZEND_ACC_PUBLIC)
    ZEND_FE_END
};
/* }}} */

BEGIN_EXTERN_C()
/* {{{ registration function */
void register_enum_class(int module_number TSRMLS_DC)
{
    zend_class_entry ce;
    
    INIT_NS_CLASS_ENTRY(ce, "Peg", "Enum", peg_enum_methods);
    peg_enum_ce = zend_register_internal_class(&ce TSRMLS_CC);
    peg_enum_ce->ce_flags |= ZEND_ACC_EXPLICIT_ABSTRACT_CLASS;

    peg_enum_ce->create_object = peg_enum_object_create;
    memcpy(&peg_enum_handlers, &std_object_handlers, sizeof(zend_object_handlers));
    peg_enum_handlers.cast_object = peg_enum_cast;
    peg_enum_handlers.get_debug_info = peg_enum_debug_info;
    peg_enum_handlers.get = peg_enum_get;
    peg_enum_handlers.set = peg_enum_set;
    peg_enum_handlers.clone_obj = peg_enum_clone;
    peg_enum_handlers.compare_objects = peg_enum_compare;
}
/* }}} */
END_EXTERN_C()