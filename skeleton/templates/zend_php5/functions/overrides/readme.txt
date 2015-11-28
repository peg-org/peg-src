On this directory you can place custom templates for functions.
The template filename should be all lowercase.

1. The scheme is as follows:

    {head|footer|body}[_name_space]_function_name.php

    Examples:

        a) MyFunction()
            
            * head_myfunction.php
            * footer_myfunction.php
            * body_myfunction.php

        b) Namespace::My_Function()

            * head_namespace_myfunction.php
            * footer_namespace_myfunction.php
            * body_namespace_myfunction.php

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
    * $proto_header
    * $proto_footer
    * $parameters_code
    * $parse_code
    * $return_code
