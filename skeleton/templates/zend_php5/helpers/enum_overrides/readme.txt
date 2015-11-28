On this directory you can place template files that override the default ones
on the parent directory.

1. For the templates that build the registration function of enumerations
   for a particular header file the scheme is as follows:

    function_{header|footer|decl}_header_name_h.php

    Examples:
        
        a) Lib/My-Header.h

            * function_header_lib_my_header_h.php
            * function_footer_lib_my_header_h.php
            * function_decl_lib_my_header_h.php

        b) AnotherHeader.h

            * function_header_anotherheader_h.php
            * function_footer_anotherheader_h.php
            * function_decl_anotherheader_h.php

2. For templates that register a named enum and its options 
   the scheme is as follows:

    declare_[class|constant]_[name_space_]enum_name.php

    Examples:

        a) Store::Books::BookType

            * declare_class_store_books_booktype.php
            * declare_constant_store_books_booktype.php

        b) HairStyle

            * declare_class_hairstyle.php
            * declare_constant_hairstyle.php

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
    * $enum_name
    * $enum_object
    * $enum_option