<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: scip.proto

namespace Scip;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Occurrence associates a source position with a symbol and/or highlighting
 * information.
 * If possible, indexers should try to bundle logically related information
 * across occurrences into a single occurrence to reduce payload sizes.
 *
 * Generated from protobuf message <code>scip.Occurrence</code>
 */
class Occurrence extends \Google\Protobuf\Internal\Message
{
    /**
     * Source position of this occurrence. Must be exactly three or four
     * elements:
     * - Four elements: `[startLine, startCharacter, endLine, endCharacter]`
     * - Three elements: `[startLine, startCharacter, endCharacter]`. The end line
     *   is inferred to have the same value as the start line.
     * Line numbers and characters are always 0-based. Make sure to increment the
     * line/character values before displaying them in an editor-like UI because
     * editors conventionally use 1-based numbers.
     * Historical note: the original draft of this schema had a `Range` message
     * type with `start` and `end` fields of type `Position`, mirroring LSP.
     * Benchmarks revealed that this encoding was inefficient and that we could
     * reduce the total payload size of an index by 50% by using `repeated int32`
     * instead.  The `repeated int32` encoding is admittedly more embarrassing to
     * work with in some programming languages but we hope the performance
     * improvements make up for it.
     *
     * Generated from protobuf field <code>repeated int32 range = 1;</code>
     */
    private $range;
    /**
     * (optional) The symbol that appears at this position. See
     * `SymbolInformation.symbol` for how to format symbols as strings.
     *
     * Generated from protobuf field <code>string symbol = 2;</code>
     */
    protected $symbol = '';
    /**
     * (optional) Bitset containing `SymbolRole`s in this occurrence.
     * See `SymbolRole`'s documentation for how to read and write this field.
     *
     * Generated from protobuf field <code>int32 symbol_roles = 3;</code>
     */
    protected $symbol_roles = 0;
    /**
     * (optional) CommonMark-formatted documentation for this specific range. If
     * empty, the `Symbol.documentation` field is used instead. One example
     * where this field might be useful is when the symbol represents a generic
     * function (with abstract type parameters such as `List<T>`) and at this
     * occurrence we know the exact values (such as `List<String>`).
     * This field can also be used for dynamically or gradually typed languages,
     * which commonly allow for type-changing assignment.
     *
     * Generated from protobuf field <code>repeated string override_documentation = 4;</code>
     */
    private $override_documentation;
    /**
     * (optional) What syntax highlighting class should be used for this range?
     *
     * Generated from protobuf field <code>.scip.SyntaxKind syntax_kind = 5;</code>
     */
    protected $syntax_kind = 0;
    /**
     * (optional) Diagnostics that have been reported for this specific range.
     *
     * Generated from protobuf field <code>repeated .scip.Diagnostic diagnostics = 6;</code>
     */
    private $diagnostics;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type array<int>|\Google\Protobuf\Internal\RepeatedField $range
     *           Source position of this occurrence. Must be exactly three or four
     *           elements:
     *           - Four elements: `[startLine, startCharacter, endLine, endCharacter]`
     *           - Three elements: `[startLine, startCharacter, endCharacter]`. The end line
     *             is inferred to have the same value as the start line.
     *           Line numbers and characters are always 0-based. Make sure to increment the
     *           line/character values before displaying them in an editor-like UI because
     *           editors conventionally use 1-based numbers.
     *           Historical note: the original draft of this schema had a `Range` message
     *           type with `start` and `end` fields of type `Position`, mirroring LSP.
     *           Benchmarks revealed that this encoding was inefficient and that we could
     *           reduce the total payload size of an index by 50% by using `repeated int32`
     *           instead.  The `repeated int32` encoding is admittedly more embarrassing to
     *           work with in some programming languages but we hope the performance
     *           improvements make up for it.
     *     @type string $symbol
     *           (optional) The symbol that appears at this position. See
     *           `SymbolInformation.symbol` for how to format symbols as strings.
     *     @type int $symbol_roles
     *           (optional) Bitset containing `SymbolRole`s in this occurrence.
     *           See `SymbolRole`'s documentation for how to read and write this field.
     *     @type array<string>|\Google\Protobuf\Internal\RepeatedField $override_documentation
     *           (optional) CommonMark-formatted documentation for this specific range. If
     *           empty, the `Symbol.documentation` field is used instead. One example
     *           where this field might be useful is when the symbol represents a generic
     *           function (with abstract type parameters such as `List<T>`) and at this
     *           occurrence we know the exact values (such as `List<String>`).
     *           This field can also be used for dynamically or gradually typed languages,
     *           which commonly allow for type-changing assignment.
     *     @type int $syntax_kind
     *           (optional) What syntax highlighting class should be used for this range?
     *     @type array<\Scip\Diagnostic>|\Google\Protobuf\Internal\RepeatedField $diagnostics
     *           (optional) Diagnostics that have been reported for this specific range.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Scip::initOnce();
        parent::__construct($data);
    }

    /**
     * Source position of this occurrence. Must be exactly three or four
     * elements:
     * - Four elements: `[startLine, startCharacter, endLine, endCharacter]`
     * - Three elements: `[startLine, startCharacter, endCharacter]`. The end line
     *   is inferred to have the same value as the start line.
     * Line numbers and characters are always 0-based. Make sure to increment the
     * line/character values before displaying them in an editor-like UI because
     * editors conventionally use 1-based numbers.
     * Historical note: the original draft of this schema had a `Range` message
     * type with `start` and `end` fields of type `Position`, mirroring LSP.
     * Benchmarks revealed that this encoding was inefficient and that we could
     * reduce the total payload size of an index by 50% by using `repeated int32`
     * instead.  The `repeated int32` encoding is admittedly more embarrassing to
     * work with in some programming languages but we hope the performance
     * improvements make up for it.
     *
     * Generated from protobuf field <code>repeated int32 range = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getRange()
    {
        return $this->range;
    }

    /**
     * Source position of this occurrence. Must be exactly three or four
     * elements:
     * - Four elements: `[startLine, startCharacter, endLine, endCharacter]`
     * - Three elements: `[startLine, startCharacter, endCharacter]`. The end line
     *   is inferred to have the same value as the start line.
     * Line numbers and characters are always 0-based. Make sure to increment the
     * line/character values before displaying them in an editor-like UI because
     * editors conventionally use 1-based numbers.
     * Historical note: the original draft of this schema had a `Range` message
     * type with `start` and `end` fields of type `Position`, mirroring LSP.
     * Benchmarks revealed that this encoding was inefficient and that we could
     * reduce the total payload size of an index by 50% by using `repeated int32`
     * instead.  The `repeated int32` encoding is admittedly more embarrassing to
     * work with in some programming languages but we hope the performance
     * improvements make up for it.
     *
     * Generated from protobuf field <code>repeated int32 range = 1;</code>
     * @param array<int>|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setRange($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::INT32);
        $this->range = $arr;

        return $this;
    }

    /**
     * (optional) The symbol that appears at this position. See
     * `SymbolInformation.symbol` for how to format symbols as strings.
     *
     * Generated from protobuf field <code>string symbol = 2;</code>
     * @return string
     */
    public function getSymbol()
    {
        return $this->symbol;
    }

    /**
     * (optional) The symbol that appears at this position. See
     * `SymbolInformation.symbol` for how to format symbols as strings.
     *
     * Generated from protobuf field <code>string symbol = 2;</code>
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
     * (optional) Bitset containing `SymbolRole`s in this occurrence.
     * See `SymbolRole`'s documentation for how to read and write this field.
     *
     * Generated from protobuf field <code>int32 symbol_roles = 3;</code>
     * @return int
     */
    public function getSymbolRoles()
    {
        return $this->symbol_roles;
    }

    /**
     * (optional) Bitset containing `SymbolRole`s in this occurrence.
     * See `SymbolRole`'s documentation for how to read and write this field.
     *
     * Generated from protobuf field <code>int32 symbol_roles = 3;</code>
     * @param int $var
     * @return $this
     */
    public function setSymbolRoles($var)
    {
        GPBUtil::checkInt32($var);
        $this->symbol_roles = $var;

        return $this;
    }

    /**
     * (optional) CommonMark-formatted documentation for this specific range. If
     * empty, the `Symbol.documentation` field is used instead. One example
     * where this field might be useful is when the symbol represents a generic
     * function (with abstract type parameters such as `List<T>`) and at this
     * occurrence we know the exact values (such as `List<String>`).
     * This field can also be used for dynamically or gradually typed languages,
     * which commonly allow for type-changing assignment.
     *
     * Generated from protobuf field <code>repeated string override_documentation = 4;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getOverrideDocumentation()
    {
        return $this->override_documentation;
    }

    /**
     * (optional) CommonMark-formatted documentation for this specific range. If
     * empty, the `Symbol.documentation` field is used instead. One example
     * where this field might be useful is when the symbol represents a generic
     * function (with abstract type parameters such as `List<T>`) and at this
     * occurrence we know the exact values (such as `List<String>`).
     * This field can also be used for dynamically or gradually typed languages,
     * which commonly allow for type-changing assignment.
     *
     * Generated from protobuf field <code>repeated string override_documentation = 4;</code>
     * @param array<string>|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setOverrideDocumentation($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::STRING);
        $this->override_documentation = $arr;

        return $this;
    }

    /**
     * (optional) What syntax highlighting class should be used for this range?
     *
     * Generated from protobuf field <code>.scip.SyntaxKind syntax_kind = 5;</code>
     * @return int
     */
    public function getSyntaxKind()
    {
        return $this->syntax_kind;
    }

    /**
     * (optional) What syntax highlighting class should be used for this range?
     *
     * Generated from protobuf field <code>.scip.SyntaxKind syntax_kind = 5;</code>
     * @param int $var
     * @return $this
     */
    public function setSyntaxKind($var)
    {
        GPBUtil::checkEnum($var, \Scip\SyntaxKind::class);
        $this->syntax_kind = $var;

        return $this;
    }

    /**
     * (optional) Diagnostics that have been reported for this specific range.
     *
     * Generated from protobuf field <code>repeated .scip.Diagnostic diagnostics = 6;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getDiagnostics()
    {
        return $this->diagnostics;
    }

    /**
     * (optional) Diagnostics that have been reported for this specific range.
     *
     * Generated from protobuf field <code>repeated .scip.Diagnostic diagnostics = 6;</code>
     * @param array<\Scip\Diagnostic>|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setDiagnostics($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Scip\Diagnostic::class);
        $this->diagnostics = $arr;

        return $this;
    }

}
