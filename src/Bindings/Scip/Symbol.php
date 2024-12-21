<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# NO CHECKED-IN PROTOBUF GENCODE
# source: scip.proto

namespace Scip;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Symbol is similar to a URI, it identifies a class, method, or a local
 * variable. `SymbolInformation` contains rich metadata about symbols such as
 * the docstring.
 * Symbol has a standardized string representation, which can be used
 * interchangeably with `Symbol`. The syntax for Symbol is the following:
 * ```
 * # (<x>)+ stands for one or more repetitions of <x>
 * # (<x>)? stands for zero or one occurrence of <x>
 * <symbol>               ::= <scheme> ' ' <package> ' ' (<descriptor>)+ | 'local ' <local-id>
 * <package>              ::= <manager> ' ' <package-name> ' ' <version>
 * <scheme>               ::= any UTF-8, escape spaces with double space. Must not be empty nor start with 'local'
 * <manager>              ::= any UTF-8, escape spaces with double space. Use the placeholder '.' to indicate an empty value
 * <package-name>         ::= same as above
 * <version>              ::= same as above
 * <descriptor>           ::= <namespace> | <type> | <term> | <method> | <type-parameter> | <parameter> | <meta> | <macro>
 * <namespace>            ::= <name> '/'
 * <type>                 ::= <name> '#'
 * <term>                 ::= <name> '.'
 * <meta>                 ::= <name> ':'
 * <macro>                ::= <name> '!'
 * <method>               ::= <name> '(' (<method-disambiguator>)? ').'
 * <type-parameter>       ::= '[' <name> ']'
 * <parameter>            ::= '(' <name> ')'
 * <name>                 ::= <identifier>
 * <method-disambiguator> ::= <simple-identifier>
 * <identifier>           ::= <simple-identifier> | <escaped-identifier>
 * <simple-identifier>    ::= (<identifier-character>)+
 * <identifier-character> ::= '_' | '+' | '-' | '$' | ASCII letter or digit
 * <escaped-identifier>   ::= '`' (<escaped-character>)+ '`', must contain at least one non-<identifier-character>
 * <escaped-characters>   ::= any UTF-8, escape backticks with double backtick.
 * <local-id>             ::= <simple-identifier>
 * ```
 * The list of descriptors for a symbol should together form a fully
 * qualified name for the symbol. That is, it should serve as a unique
 * identifier across the package. Typically, it will include one descriptor
 * for every node in the AST (along the ancestry path) between the root of
 * the file and the node corresponding to the symbol.
 * Local symbols MUST only be used for entities which are local to a Document,
 * and cannot be accessed from outside the Document.
 *
 * Generated from protobuf message <code>scip.Symbol</code>
 */
class Symbol extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>string scheme = 1;</code>
     */
    protected $scheme = '';
    /**
     * Generated from protobuf field <code>.scip.Package package = 2;</code>
     */
    protected $package = null;
    /**
     * Generated from protobuf field <code>repeated .scip.Descriptor descriptors = 3;</code>
     */
    private $descriptors;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $scheme
     *     @type \Scip\Package $package
     *     @type array<\Scip\Descriptor>|\Google\Protobuf\Internal\RepeatedField $descriptors
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Scip::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>string scheme = 1;</code>
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Generated from protobuf field <code>string scheme = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setScheme($var)
    {
        GPBUtil::checkString($var, True);
        $this->scheme = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>.scip.Package package = 2;</code>
     * @return \Scip\Package|null
     */
    public function getPackage()
    {
        return $this->package;
    }

    public function hasPackage()
    {
        return isset($this->package);
    }

    public function clearPackage()
    {
        unset($this->package);
    }

    /**
     * Generated from protobuf field <code>.scip.Package package = 2;</code>
     * @param \Scip\Package $var
     * @return $this
     */
    public function setPackage($var)
    {
        GPBUtil::checkMessage($var, \Scip\Package::class);
        $this->package = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>repeated .scip.Descriptor descriptors = 3;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getDescriptors()
    {
        return $this->descriptors;
    }

    /**
     * Generated from protobuf field <code>repeated .scip.Descriptor descriptors = 3;</code>
     * @param array<\Scip\Descriptor>|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setDescriptors($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Scip\Descriptor::class);
        $this->descriptors = $arr;

        return $this;
    }

}

