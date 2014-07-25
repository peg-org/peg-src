class <?=$class_name?>_php<?php if(!$class_object->forward_declaration){ ?>: public <?=$class_name?><?php } ?> 
{

public:
<?php if($constructor_code){ ?>

    <?=$constructor_code?>
<?php } ?>
<?php if($virtual_methods_code){ ?>

    <?=$virtual_methods_code?>
<?php } ?>

<?php if($class_object->HasProperties()){?>
    <?=$properties_init_code?>
    <?=$properties_uninit_code?>
    
    void** properties;
<?php } ?>
    zval* php_object;
    <?=$extension?>_ObjectReferences references;
    
#ifdef ZTS
    TSRMLS_D;
#endif

};
