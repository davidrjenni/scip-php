<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# NO CHECKED-IN PROTOBUF GENCODE
# source: scip.proto

namespace Scip;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>scip.ToolInfo</code>
 */
class ToolInfo extends \Google\Protobuf\Internal\Message
{
    /**
     * Name of the indexer that produced this index.
     *
     * Generated from protobuf field <code>string name = 1;</code>
     */
    protected $name = '';
    /**
     * Version of the indexer that produced this index.
     *
     * Generated from protobuf field <code>string version = 2;</code>
     */
    protected $version = '';
    /**
     * Command-line arguments that were used to invoke this indexer.
     *
     * Generated from protobuf field <code>repeated string arguments = 3;</code>
     */
    private $arguments;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $name
     *           Name of the indexer that produced this index.
     *     @type string $version
     *           Version of the indexer that produced this index.
     *     @type array<string>|\Google\Protobuf\Internal\RepeatedField $arguments
     *           Command-line arguments that were used to invoke this indexer.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Scip::initOnce();
        parent::__construct($data);
    }

    /**
     * Name of the indexer that produced this index.
     *
     * Generated from protobuf field <code>string name = 1;</code>
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Name of the indexer that produced this index.
     *
     * Generated from protobuf field <code>string name = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setName($var)
    {
        GPBUtil::checkString($var, True);
        $this->name = $var;

        return $this;
    }

    /**
     * Version of the indexer that produced this index.
     *
     * Generated from protobuf field <code>string version = 2;</code>
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Version of the indexer that produced this index.
     *
     * Generated from protobuf field <code>string version = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setVersion($var)
    {
        GPBUtil::checkString($var, True);
        $this->version = $var;

        return $this;
    }

    /**
     * Command-line arguments that were used to invoke this indexer.
     *
     * Generated from protobuf field <code>repeated string arguments = 3;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Command-line arguments that were used to invoke this indexer.
     *
     * Generated from protobuf field <code>repeated string arguments = 3;</code>
     * @param array<string>|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setArguments($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::STRING);
        $this->arguments = $arr;

        return $this;
    }

}

