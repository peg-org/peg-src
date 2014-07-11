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

extern zend_class_entry *peg_enum_ce;
extern zend_object_handlers peg_enum_handlers;
    
/* {{{ exported function to take a zval** enum instance and give you back the long value */
BEGIN_EXTERN_C()
ZEND_API long php_peg_get_enum_value(zval** enumclass TSRMLS_DC);
ZEND_API void php_peg_set_enum_value(zval** enumclass, long value TSRMLS_DC);
END_EXTERN_C()
/* }}} */

/* {{{ enum registration */
BEGIN_EXTERN_C()
void register_enum_class(int module_number TSRMLS_DC);
END_EXTERN_C()
/* }}} */
    
#endif //PEG_ENUMS_H