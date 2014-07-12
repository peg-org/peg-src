On this directory you can place template files that override the default ones
on the parent directory.

1. For constants which were declared in C/C++ as a #define 
   the scheme is as follows:

    define_constant_name.php

    Examples:
        
        a) PI

            * define_pi.php

        b) MAX_INT

            * define_max_int.php

2. For constants which were declared in C/C++ from regular variables
   the scheme is as follows:

    type_[ptr]_[ref]_[arr].php

    Examples:

        a) MyType

            * mytype.php

        b) MyType*

            * mytype_ptr.php

        c) MyType**

            * mytype_ptr_ptr.php

        d) MyType&

            * mytype_ref.php

        e) MyType[]

            * mytype_arr.php