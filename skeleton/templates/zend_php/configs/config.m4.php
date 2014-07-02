PHP_ARG_WITH(<?=strtolower($extension)?>,for <?=$extension?> support,
[  --with-<?=strtolower($extension)?>[=DIR]    enable <?=$extension?> extension (requires someLibrary >= 1.0).])

PHP_ARG_ENABLE(<?=strtolower($extension)?>-debug, whether to enable debugging support in <?=$extension?>,
[  --enable-<?=strtolower($extension)?>-debug
                          Enable debugging messages support in <?=$extension?>], no, no)

if test "$PHP_<?=strtoupper($extension)?>" != "no"; then

	dnl Instruct the PHP build system to use a C++ compiler
	PHP_REQUIRE_CXX()
	
	dnl Default lib-config command
	SOMELIBRARY_CONFIG_PATH=lib-config
	
	dnl Check for the installation path of lib-config
	AC_MSG_CHECKING([for lib-config existance and someLibrary version >= 1.0])
	for directory in "$PHP_<?=strtoupper($extension)?>" "$PHP_<?=strtoupper($extension)?>/bin" /usr /usr/bin /usr/local /usr/local/bin; do
		if test -e "$directory/lib-config"; then
			somelibrary_version=`$directory/lib-config --version`
			version_check=`echo $somelibrary_version | grep "1.0" && echo $somelibrary_version | grep "1.[0-9]"`
			if test -n "$version_check"; then
				SOMELIBRARY_CONFIG_PATH="$directory/lib-config"
				AC_MSG_RESULT([version $somelibrary_version found])
				break
			fi
		fi
	done
	
	dnl Show error if someLibrary was not found
	if test ! -e $SOMELIBRARY_CONFIG_PATH; then
		AC_MSG_RESULT([not found])
		AC_MSG_ERROR([A matching someLibrary installation was not found])
	fi
	
	dnl Check whether to enable debugging messages
	if test "$PHP_<?=strtoupper($extension)?>_DEBUG" != "no"; then
		dnl Yes, so set the C macro
		AC_DEFINE(USE_<?=strtoupper($extension)?>_DEBUG,1,[Include debugging support in <?=$extension?>])
	fi
	
	dnl Add additional includes directory
	if test -n "$ext_srcdir"; then
		PHP_<?=strtoupper($extension)?>_CFLAGS="-I$ext_srcdir/includes";
	else
		PHP_<?=strtoupper($extension)?>_CFLAGS="-Iincludes";
	fi
	
	dnl Retreive and store <?=$extension?> compiler flags
	<?=strtoupper($extension)?>_CONFIG_FLAGS=`$SOMELIBRARY_CONFIG_PATH --cxxflags`
	
	dnl Append lib-config flags to <?=$extension?> compiler flags 
	PHP_<?=strtoupper($extension)?>_CFLAGS="$PHP_<?=strtoupper($extension)?>_CFLAGS $<?=strtoupper($extension)?>_CONFIG_FLAGS"
	
	dnl Retreive and store <?=$extension?> library flags
	PHP_<?=strtoupper($extension)?>_LIBS=`$SOMELIBRARY_CONFIG_PATH --libs all`
	
	dnl Append <?=$extension?> flags to the compiler flags and suppress warning flags
	CXXFLAGS="$CXXFLAGS $PHP_<?=strtoupper($extension)?>_CFLAGS"

	dnl Add header search paths to the PHP build system
	PHP_EVAL_INCLINE($PHP_<?=strtoupper($extension)?>_CFLAGS)
	
	dnl Add libraries and or library search paths to the PHP build system 
  	PHP_EVAL_LIBLINE($PHP_<?=strtoupper($extension)?>_LIBS, <?=strtoupper($extension)?>_SHARED_LIBADD)

	dnl Adds variable with value into Makefile for example CC = gcc
	PHP_SUBST(<?=strtoupper($extension)?>_SHARED_LIBADD)
	
	dnl Link the C++ standard library
	PHP_ADD_LIBRARY(stdc++, 1 , <?=strtoupper($extension)?>_SHARED_LIBADD)

	dnl PHP_NEW_EXTENSION(extname, sources [, shared [, sapi_class [, extra-cflags [, cxx [, zend_ext]]]]])
	PHP_NEW_EXTENSION(<?=strtolower($extension)?>, <?=$source_files?> <?=strtolower($extension)?>.cpp, $ext_shared,,,1)
fi
