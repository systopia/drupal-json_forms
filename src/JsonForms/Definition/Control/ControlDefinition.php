<?php

/*
 * Copyright (C) 2022 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Drupal\json_forms\JsonForms\Definition\Control;

use Assert\Assertion;
use Drupal\json_forms\Form\Control\Util\FormPropertyUtil;
use Drupal\json_forms\Form\Util\FieldNameUtil;
use Drupal\json_forms\JsonForms\Definition\DefinitionInterface;
use Drupal\json_forms\JsonForms\ScopePointer;

/**
 * @phpstan-consistent-constructor
 */
class ControlDefinition implements DefinitionInterface {

  protected \stdClass $controlSchema;

  protected \stdClass $objectSchema;

  private bool $parentUiReadonly;

  private ?string $propertyFormName = NULL;

  protected \stdClass $propertySchema;

  private DefinitionInterface $rootDefinition;

  private ?string $scopePrefix;

  /**
   * @param \Drupal\json_forms\JsonForms\Definition\Control\ControlDefinition $definition
   *
   * @return static
   */
  public static function fromDefinition(ControlDefinition $definition): self {
    return new static(
      $definition->controlSchema,
      $definition->objectSchema,
      $definition->parentUiReadonly,
      $definition->rootDefinition,
      $definition->scopePrefix
    );
  }

  /**
   * @param \stdClass $controlSchema
   * @param \stdClass $jsonSchema
   *
   * @throws \InvalidArgumentException
   */
  public static function fromJsonSchema(
    \stdClass $controlSchema,
    \stdClass $jsonSchema,
    bool $parentUiReadonly,
    ?DefinitionInterface $rootDefinition
  ): self {
    if ('#/' === $controlSchema->scope) {
      $objectSchema = (object) [
        'properties' => (object) [
          '' => $jsonSchema,
        ],
      ];
    }
    else {
      $objectPointer = ScopePointer::new($controlSchema->scope)->getParentPointer();
      $objectSchema = $objectPointer->getSchema($jsonSchema);
    }

    return static::fromObjectSchema($controlSchema, $objectSchema, $parentUiReadonly, $rootDefinition);
  }

  public static function fromObjectSchema(
    \stdClass $controlSchema,
    \stdClass $objectSchema,
    bool $parentUiReadonly,
    ?DefinitionInterface $rootDefinition
  ): self {
    $definition = new static($controlSchema, $objectSchema, $parentUiReadonly, $rootDefinition);
    if ('object' === $definition->getType()) {
      return ObjectControlDefinition::fromDefinition($definition);
    }

    return $definition;
  }

  public function __construct(
    \stdClass $controlSchema,
    \stdClass $objectSchema,
    bool $parentUiReadonly,
    ?DefinitionInterface $rootDefinition,
    ?string $scopePrefix = NULL
  ) {
    $this->controlSchema = $controlSchema;
    $this->objectSchema = $objectSchema;
    $this->parentUiReadonly = $parentUiReadonly;
    $this->rootDefinition = $rootDefinition ?? $this;
    $this->scopePrefix = $scopePrefix;
    $propertySchema = $this->objectSchema->properties->{$this->getPropertyName()} ?? NULL;
    Assertion::notNull(
      $propertySchema,
      sprintf('Property schema for "%s" is missing.', $this->getFullScope()),
      $this->getFullScope()
    );
    Assertion::isInstanceOf(
      $propertySchema,
      \stdClass::class,
      sprintf('Property schema for "%s" is invalid.', $this->getFullScope()),
      $this->getFullScope()
    );
    $this->propertySchema = $propertySchema;
  }

  /**
   * @return mixed
   */
  public function getConst() {
    if (property_exists($this->propertySchema, 'const')) {
      return $this->propertySchema->const;
    }

    $enum = $this->getEnum();
    if (NULL !== $enum && count($enum) === 1) {
      return $enum[0];
    }

    return NULL;
  }

  public function getControlFormat(): ?string {
    return $this->controlSchema->options->format ?? NULL;
  }

  /**
   * @param string $keyword
   * @param mixed $default
   *
   * @return mixed
   */
  public function getControlKeywordValue(string $keyword, $default = NULL) {
    return $this->controlSchema->{$keyword} ?? $default;
  }

  public function getControlSchema(): \stdClass {
    return $this->controlSchema;
  }

  /**
   * @return mixed
   */
  public function getDefault() {
    return $this->propertySchema->default ?? NULL;
  }

  public function getDescription(): ?string {
    return $this->controlSchema->description ?? $this->propertySchema->description ?? NULL;
  }

  /**
   * @return array<scalar|null>|null
   */
  public function getEnum(): ?array {
    return $this->propertySchema->enum ?? NULL;
  }

  /**
   * {@inheritDoc}
   */
  public function getKeywordValue(string $keyword, $default = NULL) {
    return $this->uiSchema->{$keyword} ?? $default;
  }

  public function getLabel(): string {
    return $this->controlSchema->label ?? $this->propertySchema->title ?? ucfirst($this->getPropertyName());
  }

  /**
   * @param string $keyword
   * @param mixed $default
   *
   * @return mixed
   */
  public function getObjectKeywordValue(string $keyword, $default = NULL) {
    return $this->objectSchema->{$keyword} ?? $default;
  }

  public function getObjectSchema(): \stdClass {
    return $this->objectSchema;
  }

  /**
   * @return array<\stdClass>|null
   */
  public function getOneOf(): ?array {
    return $this->propertySchema->oneOf ?? NULL;
  }

  public function getOptions(): ?\stdClass {
    return $this->controlSchema->options ?? NULL;
  }

  /**
   * @param string $key
   * @param mixed $default
   *
   * @return mixed
   */
  public function getOptionsValue(string $key, $default = NULL) {
    return $this->controlSchema->options->{$key} ?? $default;
  }

  /**
   * @return string|null Not a standardized property.
   */
  public function getPrefix(): ?string {
    return $this->controlSchema->prefix ?? NULL;
  }

  public function getPropertyName(): string {
    $pos = strrpos($this->getScope(), '/');
    Assertion::integer($pos);

    return substr($this->getScope(), $pos + 1);
  }

  public function getPropertyFormat(): ?string {
    return $this->propertySchema->format ?? NULL;
  }

  /**
   * @param string $keyword
   * @param mixed $default
   *
   * @return mixed
   */
  public function getPropertyKeywordValue(string $keyword, $default = NULL) {
    return $this->propertySchema->{$keyword} ?? $default;
  }

  /**
   * @return string Value of the attribute "name" in HTML.
   */
  public function getPropertyFormName(): string {
    if (NULL == $this->propertyFormName) {
      $this->propertyFormName = FormPropertyUtil::getFormNameForPropertyPath($this->getPropertyPath());
    }

    return $this->propertyFormName;
  }

  /**
   * @return array<int|string> Value to be used as #parents in the form array.
   */
  public function getPropertyFormParents(): array {
    return FieldNameUtil::toFormParents($this->getPropertyPath());
  }

  /**
   * @return array<string|int>
   */
  public function getPropertyPath(): array {
    return ScopePointer::new($this->getFullScope())->getPropertyPath();
  }

  public function getPropertySchema(): \stdClass {
    return $this->propertySchema;
  }

  public function getRootDefinition(): DefinitionInterface {
    return $this->rootDefinition;
  }

  public function getRule(): ?\stdClass {
    return $this->controlSchema->rule ?? NULL;
  }

  public function getFullScope(): string {
    if (NULL !== $this->scopePrefix) {
      return $this->scopePrefix . ltrim($this->getScope(), '#');
    }

    return $this->getScope();
  }

  public function getScope(): string {
    return $this->controlSchema->scope;
  }

  public function getScopePrefix(): ?string {
    return $this->scopePrefix;
  }

  /**
   * @return string|null Not a standardized property.
   */
  public function getSuffix(): ?string {
    return $this->controlSchema->suffix ?? NULL;
  }

  /**
   * If multiple types are set in the property schema, the first non "null"
   * value is returned, or "null" there's no such value. If a single type is
   * set, it is returned as is.
   */
  public function getType(): string {
    if (is_array($this->propertySchema->type)) {
      foreach ($this->propertySchema->type as $type) {
        if ('null' !== $type) {
          return $type;
        }
      }

      return 'null';
    }

    return $this->propertySchema->type;
  }

  public function isNullable(): bool {
    return in_array('null', (array) $this->propertySchema->type, TRUE);
  }

  public function isReadOnly(): bool {
    return $this->controlSchema->options->readonly ?? $this->propertySchema->readOnly
      ?? (property_exists($this->propertySchema, '$calculate') || $this->parentUiReadonly);
  }

  /**
   * @return bool
   *   TRUE if the control schema is marked readonly or its parent UI element,
   *   FALSE otherwise.
   */
  public function isUiReadonly(): bool {
    return $this->controlSchema->options->readonly ?? $this->parentUiReadonly;
  }

  public function isRequired(): bool {
    return in_array($this->getPropertyName(), $this->objectSchema->required ?? [], TRUE);
  }

  /**
   * {@inheritDoc}
   */
  public function withScopePrefix(string $scopePrefix): DefinitionInterface {
    if (NULL !== $this->getScopePrefix()) {
      $scopePrefix = $this->getScopePrefix() . ltrim($scopePrefix, '#');
    }

    return new static(
      $this->controlSchema, $this->objectSchema, $this->parentUiReadonly, $this->rootDefinition, $scopePrefix
    );
  }

}
