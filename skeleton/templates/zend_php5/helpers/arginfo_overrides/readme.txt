On this directory you can place template files that override the default ones
on the parent directory.

1. For the templates that build the args info structure
   for a particular function the scheme is as follows:

    {header|footer}[_name_space]_function_name.php

    Examples:
        
        a) MyFunction(Object var)

            * header_myfunction.php
            * footer_myfunction.php

        b) Namespace::MyFunction(int var)

            * header_namespace_myfunction.php
            * footer_namespace_myfunction.php

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
    * $required_parameters