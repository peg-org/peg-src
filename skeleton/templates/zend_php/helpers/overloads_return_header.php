if(overload_<?=$overload?>_called)
{
<?php if(trim($call_declare_code)){ ?>
    <?=$call_declare_code?>
    
<?php } ?>
    switch(arguments_received)
    {
        