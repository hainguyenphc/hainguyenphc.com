# Surgery module

## What's up with it?

It is a productivity boost module which is inspired by the [Coffee](https://www.drupal.org/project/coffee) contrib module. 

Features:
- It enables us to easily navigate the site with keystrokes, saving us time and brain power traversing the menus and sub-menus.
- It allows us to see available fields in a bundle.
- It offers querying nodes and bundles in place. We could "softly" inspect a node or query a <strong>field value</strong> and <strong>all field values</strong> of a node easily, all in one place, no mouse click.

## The `inspect` command

Syntax:
```shell
inspect <NID>
```
where `<NID>` is a node ID.

For example, `inspect 51` to softly check general information of the node #51.

## The `field-value` command

Syntax:
```shell
# if we know exactly the field name:
field-value <NID> <FIELD_MACHINE_NAME>

# if we remember part of the field name:
field-value <NID> <PART_OF_FIELD_MACHINE_NAME>

# if we feel like seeing all fields:
field-value <NID> *
```

## The `list-fields` command

Syntax:
```shell
# if we know the exact name of the content type:
list-fields <BUNDLE_MACHINE_NAME>

# else, just type the command, 
# wait for suggestions to show up and click on the correct one.
list-fields
```
