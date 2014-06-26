ARG_WITH("<?=strtolower($extension)?>", "enable <?=$extension?> extension, example C:\\someLibrary,shared", "no");
ARG_ENABLE("<?=strtolower($extension)?>-debug", "enable <?=$extension?> debugging messages", "no");

if (PHP_<?=strtoupper($extension)?> != "no") {
	if (
		CHECK_LIB("somelibrary.lib", "<?=strtolower($extension)?>", PHP_<?=strtoupper($extension)?> + "\\lib\\vc_lib") &&
		CHECK_LIB("msvcrt.lib", "<?=strtolower($extension)?>") &&
		CHECK_HEADER_ADD_INCLUDE("somelibrary\\header.h", "CFLAGS_<?=strtoupper($extension)?>", PHP_<?=strtoupper($extension)?> + "\\include") &&
		CHECK_HEADER_ADD_INCLUDE("php_<?=strtolower($extension)?>.h", "CFLAGS_<?=strtoupper($extension)?>", configure_module_dirname)) {
		
		//Compiler flags
		ADD_FLAG("CFLAGS_<?=strtoupper($extension)?>", "/Ox ");
		ADD_FLAG("CFLAGS_<?=strtoupper($extension)?>", "/TP ");
		ADD_FLAG("CFLAGS_<?=strtoupper($extension)?>", "/DNDEBUG ");
		ADD_FLAG("CFLAGS_<?=strtoupper($extension)?>", "/MD ");
		ADD_FLAG("CFLAGS_<?=strtoupper($extension)?>", "/O2 ");
		ADD_FLAG("CFLAGS_<?=strtoupper($extension)?>", "/EHs ");
		ADD_FLAG("CFLAGS_<?=strtoupper($extension)?>", "/EHc ");
		ADD_FLAG("CFLAGS_<?=strtoupper($extension)?>", "/W3 ");
		ADD_FLAG("CFLAGS_<?=strtoupper($extension)?>", "/Zc:wchar_t ");
		
		//Macro definitions
		ADD_FLAG("CFLAGS_<?=strtoupper($extension)?>", "/DWIN32 ");
		ADD_FLAG("CFLAGS_<?=strtoupper($extension)?>", "/D_CRT_SECURE_DEPRECATE ");
		ADD_FLAG("CFLAGS_<?=strtoupper($extension)?>", "/D_CRT_NONSTDC_NO_DEPRECATE ");
		ADD_FLAG("CFLAGS_<?=strtoupper($extension)?>", "/D_CRT_SECURE_NO_WARNINGS ");
		ADD_FLAG("CFLAGS_<?=strtoupper($extension)?>", "/D_VC80_UPGRADE=0x0600 ");
		ADD_FLAG("CFLAGS_<?=strtoupper($extension)?>", "/DISOLATION_AWARE_ENABLED ");
		
		//Enable messages to help debug the code
		if (PHP_<?=strtoupper($extension)?>_DEBUG != "no") {
			ADD_FLAG("CFLAGS_<?=strtoupper($extension)?>", "/DUSE_<?=strtoupper($extension)?>_DEBUG ");
			MESSAGE("<?=$extension?> debugging messages enabled");
		}
		
		//Linker flags
		ADD_FLAG("LDFLAGS_<?=strtoupper($extension)?>", "/NODEFAULTLIB:libcmt.lib");
		ADD_FLAG("LDFLAGS_<?=strtoupper($extension)?>", "/INCREMENTAL:NO");
		ADD_FLAG("LDFLAGS_<?=strtoupper($extension)?>", "/SUBSYSTEM:WINDOWS");
		
		//Declare extension
		EXTENSION("<?=strtolower($extension)?>", "<?=strtolower($extension)?>.cpp", true, null, null);
		
		//Add extra sources
		ADD_SOURCES(configure_module_dirname + "\\src", "<?=str_replace("src/", "", $source_files)?>", "<?=strtolower($extension)?>");
	}
	else {
		WARNING("<?=$extension?> not enabled; libraries and headers not found");
	}
}
