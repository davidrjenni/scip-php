<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# NO CHECKED-IN PROTOBUF GENCODE
# source: scip.proto

namespace Scip\Descriptor;

use UnexpectedValueException;

/**
 * Protobuf type <code>scip.Descriptor.Suffix</code>
 */
class Suffix
{
    /**
     * Generated from protobuf enum <code>UnspecifiedSuffix = 0;</code>
     */
    const UnspecifiedSuffix = 0;
    /**
     * Unit of code abstraction and/or namespacing.
     * NOTE: This corresponds to a package in Go and JVM languages.
     *
     * Generated from protobuf enum <code>Namespace = 1;</code>
     */
    const PBNamespace = 1;
    /**
     * Use Namespace instead.
     *
     * Generated from protobuf enum <code>Package = 1 [deprecated = true];</code>
     */
    const Package = 1;
    /**
     * Generated from protobuf enum <code>Type = 2;</code>
     */
    const Type = 2;
    /**
     * Generated from protobuf enum <code>Term = 3;</code>
     */
    const Term = 3;
    /**
     * Generated from protobuf enum <code>Method = 4;</code>
     */
    const Method = 4;
    /**
     * Generated from protobuf enum <code>TypeParameter = 5;</code>
     */
    const TypeParameter = 5;
    /**
     * Generated from protobuf enum <code>Parameter = 6;</code>
     */
    const Parameter = 6;
    /**
     * Can be used for any purpose.
     *
     * Generated from protobuf enum <code>Meta = 7;</code>
     */
    const Meta = 7;
    /**
     * Generated from protobuf enum <code>Local = 8;</code>
     */
    const Local = 8;
    /**
     * Generated from protobuf enum <code>Macro = 9;</code>
     */
    const Macro = 9;

    private static $valueToName = [
        self::UnspecifiedSuffix => 'UnspecifiedSuffix',
        self::PBNamespace => 'Namespace',
        self::Package => 'Package',
        self::Type => 'Type',
        self::Term => 'Term',
        self::Method => 'Method',
        self::TypeParameter => 'TypeParameter',
        self::Parameter => 'Parameter',
        self::Meta => 'Meta',
        self::Local => 'Local',
        self::Macro => 'Macro',
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
            $pbconst =  __CLASS__. '::PB' . strtoupper($name);
            if (!defined($pbconst)) {
                throw new UnexpectedValueException(sprintf(
                        'Enum %s has no value defined for name %s', __CLASS__, $name));
            }
            return constant($pbconst);
        }
        return constant($const);
    }
}

