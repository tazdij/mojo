# Mojo Extensions

Mojo supports Extensions which allow developers to add additional functionality
or override certain logic in Mojo without altering the MojoFramework code. This
is how CentroCMS is built.

## Class and Pattern

An extension is at a minimum, a class which is loaded and executed during the
bootstrap phase of Mojo. Mojo will read the autoload configuration file and
include, then execute the extension. This is the first part of the bootload
after the class_autoloader is defined.
