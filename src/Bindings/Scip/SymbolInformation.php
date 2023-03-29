<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: scip.proto

namespace Scip;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * SymbolInformation defines metadata about a symbol, such as the symbol's
 * docstring or what package it's defined it.
 *
 * Generated from protobuf message <code>scip.SymbolInformation</code>
 */
class SymbolInformation extends \Google\Protobuf\Internal\Message
{
    /**
     * Identifier of this symbol, which can be referenced from `Occurence.symbol`.
     * The string must be formatted according to the grammar in `Symbol`.
     *
     * Generated from protobuf field <code>string symbol = 1;</code>
     */
    protected $symbol = '';
    /**
     * (optional, but strongly recommended) The markdown-formatted documentation
     * for this symbol. This field is repeated to allow different kinds of
     * documentation.  For example, it's nice to include both the signature of a
     * method (parameters and return type) along with the accompanying docstring.
     *
     * Generated from protobuf field <code>repeated string documentation = 3;</code>
     */
    private $documentation;
    /**
     * (optional) Relationships to other symbols (e.g., implements, type definition).
     *
     * Generated from protobuf field <code>repeated .scip.Relationship relationships = 4;</code>
     */
    private $relationships;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $symbol
     *           Identifier of this symbol, which can be referenced from `Occurence.symbol`.
     *           The string must be formatted according to the grammar in `Symbol`.
     *     @type array<string>|\Google\Protobuf\Internal\RepeatedField $documentation
     *           (optional, but strongly recommended) The markdown-formatted documentation
     *           for this symbol. This field is repeated to allow different kinds of
     *           documentation.  For example, it's nice to include both the signature of a
     *           method (parameters and return type) along with the accompanying docstring.
     *     @type array<\Scip\Relationship>|\Google\Protobuf\Internal\RepeatedField $relationships
     *           (optional) Relationships to other symbols (e.g., implements, type definition).
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Scip::initOnce();
        parent::__construct($data);
    }

    /**
     * Identifier of this symbol, which can be referenced from `Occurence.symbol`.
     * The string must be formatted according to the grammar in `Symbol`.
     *
     * Generated from protobuf field <code>string symbol = 1;</code>
     * @return string
     */
    public function getSymbol()
    {
        return $this->symbol;
    }

    /**
     * Identifier of this symbol, which can be referenced from `Occurence.symbol`.
     * The string must be formatted according to the grammar in `Symbol`.
     *
     * Generated from protobuf field <code>string symbol = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setSymbol($var)
    {
        GPBUtil::checkString($var, True);
        $this->symbol = $var;

        return $this;
    }

    /**
     * (optional, but strongly recommended) The markdown-formatted documentation
     * for this symbol. This field is repeated to allow different kinds of
     * documentation.  For example, it's nice to include both the signature of a
     * method (parameters and return type) along with the accompanying docstring.
     *
     * Generated from protobuf field <code>repeated string documentation = 3;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getDocumentation()
    {
        return $this->documentation;
    }

    /**
     * (optional, but strongly recommended) The markdown-formatted documentation
     * for this symbol. This field is repeated to allow different kinds of
     * documentation.  For example, it's nice to include both the signature of a
     * method (parameters and return type) along with the accompanying docstring.
     *
     * Generated from protobuf field <code>repeated string documentation = 3;</code>
     * @param array<string>|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setDocumentation($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::STRING);
        $this->documentation = $arr;

        return $this;
    }

    /**
     * (optional) Relationships to other symbols (e.g., implements, type definition).
     *
     * Generated from protobuf field <code>repeated .scip.Relationship relationships = 4;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getRelationships()
    {
        return $this->relationships;
    }

    /**
     * (optional) Relationships to other symbols (e.g., implements, type definition).
     *
     * Generated from protobuf field <code>repeated .scip.Relationship relationships = 4;</code>
     * @param array<\Scip\Relationship>|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setRelationships($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Scip\Relationship::class);
        $this->relationships = $arr;

        return $this;
    }

}
