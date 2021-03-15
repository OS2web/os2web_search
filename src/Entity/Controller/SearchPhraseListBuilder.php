<?php

namespace Drupal\os2web_search\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Provides a list controller for os2web_search_phrase entity.
 *
 * @ingroup os2web_search
 */
class SearchPhraseListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   *
   * Building the header and content lines for the search phrases list.
   *
   * Calling the parent::buildHeader() adds a column for the possible actions
   * and inserts the 'edit' and 'delete' links as defined for the entity type.
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['phrase'] = $this->t('Text');
    $header['exclusive'] = $this->t('Exclusive');
    $header['period_start_date'] = $this->t('From');
    $header['period_end_date'] = $this->t('To');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\os2web_search\Entity\SearchPhrase */
    $row['id'] = $entity->id();
    $row['phrase'] = $entity->getPhrase();
    $row['exclusive'] = ($entity->isExclusive()) ? $this->t('Yes') : $this->t('No');

    $start_date = '';
    if ($entity->getStartDate()) {
      $start_date = \Drupal::service('date.formatter')->format($entity->getStartDate()->getTimestamp(), 'os2core_datetime_medium');
    }
    $end_date = '';
    if ($entity->getEndDate()) {
      $end_date = \Drupal::service('date.formatter')->format($entity->getEndDate()->getTimestamp(), 'os2core_datetime_medium');
    }

    $row['period_start_date'] = $start_date;
    $row['period_end_date'] = $end_date;

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = [];
    if ($entity->hasLinkTemplate('edit-form')) {
      $operations['edit'] = [
        'title' => $this->t('Edit'),
        'weight' => 10,
        'url' => $entity->toUrl('edit-form'),
      ];
    }
    if ($entity->hasLinkTemplate('delete-form')) {
      $operations['delete'] = [
        'title' => $this->t('Delete'),
        'weight' => 100,
        'url' => $entity->toUrl('delete-form'),
      ];
    }

    return $operations;
  }
}
