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

#ifndef PEG_ENUMS_H
#define PEG_ENUMS_H

#include "common.h"
#include <zend_exceptions.h>
#include <ext/spl/spl_exceptions.h>

zend_class_entry *peg_enum_ce;
zend_object_handlers peg_enum_handlers;

static int peg_enum_apply_set(long *option TSRMLS_DC, int num_args, va_list args, zend_hash_key *hash_key);

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
    
/* {{{ methods prototype */
PHP_METHOD(Peg_Enum, __construct);
PHP_METHOD(Peg_Enum, getName);
PHP_METHOD(Peg_Enum, getElements);
/* }}} */
    
/* {{{ class methods */
static const zend_function_entry peg_enum_methods[] = {
    PHP_ME(Peg_Enum, __construct, Enum___construct_args, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
    PHP_ME(Peg_Enum, getName, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(Peg_Enum, getElements, NULL, ZEND_ACC_PUBLIC)
    ZEND_FE_END
};
/* }}} */
    
#endif //PEG_ENUMS_H