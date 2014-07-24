/*
 * @author <?=$authors?> 
 * @contributors <?=$contributors?> 
 * 
 * @license 
 * This file is part of <?=$extension?> check the LICENSE file for information.
 * 
 * @description
 * <?=$extension?> php extension declarations file
 * 
 * @note
 * This file is auto-generated by PEG.
*/

//Prevent double inclusion 
#ifndef <?=strtoupper($extension)?>_PHP_<?=strtoupper($extension)?>_H_GUARD
#define <?=strtoupper($extension)?>_PHP_<?=strtoupper($extension)?>_H_GUARD

#include "common.h"

/**
 * Define Extension Properties 
 */
#define PHP_<?=strtoupper($extension)?>_EXTNAME   "<?=$extension?>"
#define PHP_<?=strtoupper($extension)?>_EXTVER    "0.1"

/**
 * Import configure options when building outside 
 * of the PHP source tree 
 */
#ifdef HAVE_CONFIG_H
    #include "config.h"
#endif

/** 
 * Include PHP Standard Header 
 */
#include "php.h"

/**
 * Define the entry point symbol
 * Zend will use when loading this module
 */
extern zend_module_entry <?=$extension?>_module_entry;
#define phpext_<?=$extension?>_ptr &<?=$extension?>_module_entry

#endif //<?=strtoupper($extension)?>_PHP_<?=strtoupper($extension)?>_H_GUARD
