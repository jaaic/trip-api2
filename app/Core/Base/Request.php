<?php

namespace App\Core\Base;

/**
 * Class Request
 *
 * @package App\Core\Base
 */
abstract class Request
{

    /**
     * @var array error list
     */
    protected $errors = [];

    /**
     * @var array attributes list
     */
    protected $attributes = [];

    /** @var array filters list */
    protected $filters = [];

    /**
     * @var array error messages
     */
    protected $messages = [];

    /** @var array */
    protected $requestData;


    /** Funtions to be implemented per request */
    abstract function attributes(): array;

    abstract function process(): array;


    /**
     *
     * @return $this
     */
    public function load()
    {
        $attributes = $this->attributes();
        foreach ($attributes as $attribute) {
            if (isset($this->requestData[$attribute])) {
                $this->attributes[$attribute] = $this->requestData[$attribute];
            } else {
                $this->attributes[$attribute] = null;
            }
        }

        return $this;
    }


    /**
     * @return array
     */
    public function messages(): array
    {
        return $this->messages;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param string $attribute
     * @param        $value
     *
     * @return $this
     */
    public function setAttribute(string $attribute, $value)
    {
        if (in_array($attribute, $this->attributes())) {
            $this->attributes[$attribute] = $value;
        }

        return $this;
    }

    /**
     * @param array $attributes
     *
     * @return $this
     */
    public function setAttributes(array $attributes)
    {
        foreach ($attributes as $attribute => $value) {
            $this->setAttribute($attribute, $value);
        }

        return $this;
    }


    /**
     * @param string $attribute attribute name
     *
     * @return mixed|null
     */
    public function getAttribute(string $attribute)
    {
        $value = $this->attributes[$attribute] ?? null;

        return $value;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * Set Errors
     *
     * @param array $errors
     */
    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    /**
     * Get errors array
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

}