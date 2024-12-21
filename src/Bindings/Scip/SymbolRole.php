<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# NO CHECKED-IN PROTOBUF GENCODE
# source: scip.proto

namespace Scip;

use UnexpectedValueException;

/**
 * SymbolRole declares what "role" a symbol has in an occurrence. A role is
 * encoded as a bitset where each bit represents a different role. For example,
 * to determine if the `Import` role is set, test whether the second bit of the
 * enum value is defined. In pseudocode, this can be implemented with the
 * logic: `const isImportRole = (role.value & SymbolRole.Import.value) > 0`.
 *
 * Protobuf type <code>scip.SymbolRole</code>
 */
class SymbolRole
{
    /**
     * This case is not meant to be used; it only exists to avoid an error
     * from the Protobuf code generator.
     *
     * Generated from protobuf enum <code>UnspecifiedSymbolRole = 0;</code>
     */
    const UnspecifiedSymbolRole = 0;
    /**
     * Is the symbol defined here? If not, then this is a symbol reference.
     *
     * Generated from protobuf enum <code>Definition = 1;</code>
     */
    const Definition = 1;
    /**
     * Is the symbol imported here?
     *
     * Generated from protobuf enum <code>Import = 2;</code>
     */
    const Import = 2;
    /**
     * Is the symbol written here?
     *
     * Generated from protobuf enum <code>WriteAccess = 4;</code>
     */
    const WriteAccess = 4;
    /**
     * Is the symbol read here?
     *
     * Generated from protobuf enum <code>ReadAccess = 8;</code>
     */
    const ReadAccess = 8;
    /**
     * Is the symbol in generated code?
     *
     * Generated from protobuf enum <code>Generated = 16;</code>
     */
    const Generated = 16;
    /**
     * Is the symbol in test code?
     *
     * Generated from protobuf enum <code>Test = 32;</code>
     */
    const Test = 32;
    /**
     * Is this a signature for a symbol that is defined elsewhere?
     * Applies to forward declarations for languages like C, C++
     * and Objective-C, as well as `val` declarations in interface
     * files in languages like SML and OCaml.
     *
     * Generated from protobuf enum <code>ForwardDefinition = 64;</code>
     */
    const ForwardDefinition = 64;

    private static $valueToName = [
        self::UnspecifiedSymbolRole => 'UnspecifiedSymbolRole',
        self::Definition => 'Definition',
        self::Import => 'Import',
        self::WriteAccess => 'WriteAccess',
        self::ReadAccess => 'ReadAccess',
        self::Generated => 'Generated',
        self::Test => 'Test',
        self::ForwardDefinition => 'ForwardDefinition',
    ];

    public static function name($value)
    {
        if (!isset(self::$valueToName[$value])) {
            throw new UnexpectedValueException(sprintf(
                    'Enum %s has no name defined for value %s', __CLASS__, $value));
        }
        return self::$valueToName[$value];
    }


    public static function value($name)
    {
        $const = __CLASS__ . '::' . strtoupper($name);
        if (!defined($const)) {
            throw new UnexpectedValueException(sprintf(
                    'Enum %s has no value defined for name %s', __CLASS__, $name));
        }
        return constant($const);
    }
}

