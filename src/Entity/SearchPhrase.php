<?php

/**
 * @file
 * Contains \Drupal\os2web_search\Entity\SearchPhrase.
 */
namespace Drupal\os2web_search\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the ContentEntityExample entity.
 * @ingroup os2web_search
 * @ContentEntityType(
 *   id = "os2web_search_phrase",
 *   label = @Translation("OS2Web Search Phrase"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\os2web_search\Entity\Controller\SearchPhraseListBuilder",
 *     "form" = {
 *       "add" = "Drupal\os2web_search\Form\SearchPhraseForm",
 *       "edit" = "Drupal\os2web_search\Form\SearchPhraseForm",
 *       "delete" = "Drupal\os2web_search\Form\SearchPhraseDeleteForm",
 *     },
 *   },
 *   list_cache_contexts = { "user" },
 *   base_table = "os2web_search_search_phrases",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "phrase" = "phrase",
 *     "items_referring" = "items_referring",
 *     "exclusive" = "exclusive",
 *   },
 *   links = {
 *     "edit-form" = "/admin/structure/{os2web_search_phrase}/edit",
 *     "delete-form" = "/admin/structure/{os2web_search_phrase}/delete",
 *     "collection" = "/admin/structure/searchphrase"
 *   },
 * )
 */
class SearchPhrase extends ContentEntityBase implements ContentEntityInterface {
  /**
   * Determines the schema for the base_table property defined above.
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    // Standard field, used as unique if primary index.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the SearchPhrase entity.'))
      ->setReadOnly(TRUE);

    // Standard field, unique outside of the scope of the current project.
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the SearchPhrase entity.'))
      ->setReadOnly(TRUE);

    // Email field for the subscription.
    $fields['phrase'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Phrase'))
      ->setDescription(t('The search phrase that is used.'))
      ->setSettings(array(
        'max_length' => 255,
        'not null' => TRUE,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => 0,
      ));

    // Exclusive field for the search phrase.
    $fields['exclusive'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Exclusive'))
      ->setDescription(t('If exclusive, only the specified results will be returned'))
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('form', array(
          'type' => 'boolean_checkbox',
          'settings' => array(
            'display_label' => TRUE,
          ),
          'weight' => 1,
        )
      );

    // Node IDs of the items that are referencing this phrase.
    $fields['items_referring'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Referring items'))
      ->setDescription(t('The node ID of the referenced node. Change the weight by reordering elements.'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setSettings(array(
        'target_type' => 'node',
      ))
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
        'weight' => 2,
      ))
      ->setDisplayConfigurable('form', TRUE);

    // From date.
    $fields['period'] = BaseFieldDefinition::create('daterange')
      ->setLabel(t('Period'))
      ->setDescription(t('Datetime when this search phrase is active'))
      ->setDisplayOptions('form', array(
        'type' => 'daterange_default',
        'weight' => 3,
      ))
      ->setDisplayConfigurable('form', TRUE);

    return $fields;
  }

  /**
   * Returns phrase text.
   *
   * @return string
   *   Phrase value as text.
   */
  public function getPhrase() {
    return $this->phrase->value;
  }

  /**
   * Returns is phrase is set exclusive
   *
   * @return boolean
   *    Phrase is exclusive.
   */
  public function isExclusive() {
    return $this->exclusive->value;
  }

  /**
   * Returns phrase start date.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   Start datetime.
   */
  public function getStartDate() {
    return $this->period->start_date;
  }

  /**
   * Returns phrase end date.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   End datetime.
   */
  public function getEndDate() {
    return $this->period->end_date;
  }

  /**
   * {@inheritdoc}
   */
  public function save() {
    //TODO: think about view cache
    return parent::save();
  }

}
