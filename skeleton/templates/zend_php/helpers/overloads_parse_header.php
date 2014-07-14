<?php 
$clause = $required_parameters == $parameters_count ? 
    "arguments_received == $required_parameters" 
    : 
    "arguments_received >= $required_parameters  && arguments_received <= $parameters_count"
;
?>
//Overload $overload
overload_<?=$overload?>:
if(!already_called && <?=$clause?>)
{
    