On this directory you can place template files that override the default ones
on the parent directory.

1. For the templates that code before and and after parse parameters code
   for a particular function:

    parameters_{header|footer}[_name_space]_function_name.php

    Examples:
        
        a) MyFunction(Object var)

            * parameters_header_myfunction.php
            * parameters_footer_myfunction.php

        b) Namespace::MyFunction(int var)

            * parameters_header_namespace_myfunction.php
            * parameters_footer_namespace_myfunction.php

2. For the templates that build the parse parameters code
   for a particular function:

    parse_{header|footer|body|no_body|references}[_name_space]_function_name.php

    Examples:
        
        a) MyFunction(Object var)

            * parse_header_myfunction.php
            * parse_footer_myfunction.php
            * parse_body_myfunction.php
            * parse_no_body_myfunction.php
            * parse_references_myfunction.php

        b) Namespace::MyFunction(int var)

            * parse_header_namespace_myfunction.php
            * parse_footer_namespace_myfunction.php
            * parse_body_namespace_myfunction.php
            * parse_no_body_namespace_myfunction.php
            * parse_references_namespace_myfunction.php

3. For the templates that build the call and return code
   for a particular function:

    return_{header|footer|body_header|body_footer}[_name_space]_function_name.php

    Examples:
        
        a) MyFunction(Object var)

            * return_header_myfunction.php
            * return_footer_myfunction.php
            * return_body_header_myfunction.php
            * return_body_footer_myfunction.php

        b) Namespace::MyFunction(int var)

            * return_header_namespace_myfunction.php
            * return_footer_namespace_myfunction.php
            * return_body_header_namespace_myfunction.php
            * return_body_footer_namespace_myfunction.php

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
    * $function_arginfo
    * $overload
    * $overload_object
    * $overloads_count
    * $parameters_code
    * $parse_code
    * $return_code
    * $proto_header
    * $proto_footer
    * $parameters_count
    * $required_parameters