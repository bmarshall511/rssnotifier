<?php

/**
 * This code was generated by
 * \ / _    _  _|   _  _
 * | (_)\/(_)(_|\/| |(/_  v1.0.0
 * /       /
 */

namespace Twilio\Rest\Numbers\V2\RegulatoryCompliance;

use Twilio\Options;
use Twilio\Values;

abstract class EndUserOptions {
    /**
     * @param array $attributes The set of parameters that compose the End User
     *                          resource
     * @return CreateEndUserOptions Options builder
     */
    public static function create(array $attributes = Values::NONE): CreateEndUserOptions {
        return new CreateEndUserOptions($attributes);
    }

    /**
     * @param string $friendlyName The string that you assigned to describe the
     *                             resource
     * @param array $attributes The set of parameters that compose the End User
     *                          resource
     * @return UpdateEndUserOptions Options builder
     */
    public static function update(string $friendlyName = Values::NONE, array $attributes = Values::NONE): UpdateEndUserOptions {
        return new UpdateEndUserOptions($friendlyName, $attributes);
    }
}

class CreateEndUserOptions extends Options {
    /**
     * @param array $attributes The set of parameters that compose the End User
     *                          resource
     */
    public function __construct(array $attributes = Values::NONE) {
        $this->options['attributes'] = $attributes;
    }

    /**
     * The set of parameters that are the attributes of the End User resource which are derived End User Types.
     *
     * @param array $attributes The set of parameters that compose the End User
     *                          resource
     * @return $this Fluent Builder
     */
    public function setAttributes(array $attributes): self {
        $this->options['attributes'] = $attributes;
        return $this;
    }

    /**
     * Provide a friendly representation
     *
     * @return string Machine friendly representation
     */
    public function __toString(): string {
        $options = [];
        foreach ($this->options as $key => $value) {
            if ($value !== Values::NONE) {
                $options[] = "$key=$value";
            }
        }
        return '[Twilio.Numbers.V2.CreateEndUserOptions ' . \implode(' ', $options) . ']';
    }
}

class UpdateEndUserOptions extends Options {
    /**
     * @param string $friendlyName The string that you assigned to describe the
     *                             resource
     * @param array $attributes The set of parameters that compose the End User
     *                          resource
     */
    public function __construct(string $friendlyName = Values::NONE, array $attributes = Values::NONE) {
        $this->options['friendlyName'] = $friendlyName;
        $this->options['attributes'] = $attributes;
    }

    /**
     * The string that you assigned to describe the resource.
     *
     * @param string $friendlyName The string that you assigned to describe the
     *                             resource
     * @return $this Fluent Builder
     */
    public function setFriendlyName(string $friendlyName): self {
        $this->options['friendlyName'] = $friendlyName;
        return $this;
    }

    /**
     * The set of parameters that are the attributes of the End User resource which are derived End User Types.
     *
     * @param array $attributes The set of parameters that compose the End User
     *                          resource
     * @return $this Fluent Builder
     */
    public function setAttributes(array $attributes): self {
        $this->options['attributes'] = $attributes;
        return $this;
    }

    /**
     * Provide a friendly representation
     *
     * @return string Machine friendly representation
     */
    public function __toString(): string {
        $options = [];
        foreach ($this->options as $key => $value) {
            if ($value !== Values::NONE) {
                $options[] = "$key=$value";
            }
        }
        return '[Twilio.Numbers.V2.UpdateEndUserOptions ' . \implode(' ', $options) . ']';
    }
}