On this directory you can place template files that override the default ones
on the parent directory.

1. For the templates that build the wrapping header file for a particular 
   header file the scheme is as follows:

    {header|footer}_header_name_h.php

    Examples:
        
        a) Lib/My-Header.h

            * header_lib_my_header_h.php
            * footer_lib_my_header_h.php

        b) AnotherHeader.h

            * header_anotherheader_h.php
            * footer_anotherheader_h.php

Variables available:

    * $authors
    * $contributors
    * $extension
    * $version
    * $this->symbols
    * $header_name
    * $header_define
    * $header_object