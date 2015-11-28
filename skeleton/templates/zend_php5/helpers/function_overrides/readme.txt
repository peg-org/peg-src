On this directory you can place template files that override the default ones
on the parent directory.

1. For the templates that build the registration function of functions
   for a particular header file the scheme is as follows:

    function_{header|footer|decl|call}_header_name_h.php

    Examples:
        
        a) Lib/My-Header.h

            * function_header_lib_my_header_h.php
            * function_footer_lib_my_header_h.php
            * function_decl_lib_my_header_h.php
            * function_call_lib_my_header_h.php

        b) AnotherHeader.h

            * function_header_anotherheader_h.php
            * function_footer_anotherheader_h.php
            * function_decl_anotherheader_h.php
            * function_call_anotherheader_h.php

2. For templates that register a functions table entry
   the scheme is as follows:

    table_{begin|end|entry|register}[_name_space]_header_name_h.php

    Examples:

        a) Lib/My-Header.h

            * table_begin_lib_my_header_h.php
            * table_end_lib_my_header_h.php
            * table_entry_lib_my_header_h.php
            * table_register_lib_my_header_h.php

        b) AnotherHeader.h

            * table_begin_anotherheader_h.php
            * table_end_anotherheader_h.php
            * table_entry_anotherheader_h.php
            * table_entry_anotherheader_h.php

Variables available:

    * $authors
    * $contributors
    * $extension
    * $version
    * $this->symbols
    * $header_name
    * $header_define
    * $header_object
    * $php_header_name
    * $namespace_name
    * $namespace_name_cpp
    * $namespace_name_var
    * $namespace_object
    * $function_name
    * $function_object