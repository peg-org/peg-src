<?php if(!$overload_object->static && $class_name && !$is_constructor){ ?>
references->AddReference(
    <?=$parameter_name?>_<?=$overload?>,
    "<?=$class_name?>::<?=$function_name?> at call with <?=$required_parameters?> argument(s)"
);

<?php } elseif($is_constructor){ ?>
((<?=$class_name?>_php*) native_object)->references.AddReference(
    <?=$parameter_name?>_<?=$overload?>,
    "<?=$class_name?>::<?=$function_name?> at call with $required_parameters argument(s)"
);

<?php } ?>