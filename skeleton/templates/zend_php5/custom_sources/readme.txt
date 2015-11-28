On this directory you can place custom source files that are automatically
added to the root include and src directory as appended into the config.m4 and
config.w32 files. This custom source files should have a .php extension, 
for example:

    * mycustom.h -> mycustom.h.php
    * mycustom.c -> mycustom.c.php
    * mycustom.cpp -> mycustom.cpp.php

This files have access to following php variables:

    * $authors
    * $contributors
    * $extension
    * $version
    * $this->symbols
