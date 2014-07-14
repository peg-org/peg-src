char parse_parameters_string[] = "<?=$parse_string?>";

if(
    zend_parse_parameters_ex(
        ZEND_PARSE_PARAMS_QUIET,
        arguments_received TSRMLS_CC,
        parse_parameters_string,
        <?=trim($parse_parameters, ", ")?> 
    ) == SUCCESS
)
{
<?php if($object_validate_code){ ?>
    <?=$object_validate_code?>

<?php } ?>
    overload_<?=$overload?>_called = true; 
    already_called = true;
<?php if($parse_references){ ?>
    
    <?=$parse_references?> 
<?php } ?>
}