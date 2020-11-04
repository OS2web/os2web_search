<?php

namespace Drupal\os2web_search\Plugin\search_api\processor;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Query\QueryInterface;
use Drupal\os2web_search\Entity\SearchPhrase;

/**
 * Provides a Phrase Catcher handler plugin.
 *
 * @SearchApiProcessor(
 *   id = "os2web_search_phrase_catcher",
 *   label = @Translation("OS2Web Search Phrase Catcher"),
 *   description = @Translation("Adds OS2Web Search Phrase Catcher to Apache Solr index using elevate and exclude query parameters."),
 *   stages = {
 *     "preprocess_query" = 0,
 *   }
 * )
 */
class PhraseCatcher extends ProcessorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function preprocessSearchQuery(QueryInterface $query) {
    $index = $query->getIndex();
    $indexId = $index->id();
    $server = $index->getServerInstance();
    /** @var \Drupal\search_api_solr\Plugin\search_api\backend\SearchApiSolrBackend $backend */
    $backend = $server->getBackend();
    $siteHash = $backend->getTargetedSiteHash($index);

    $nowDateTime = DrupalDateTime::createFromTimestamp(time(), new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));

    // Using native query in order to tke advantage of reverse LIKE operator.
    $ids = \Drupal::database()
      ->query("SELECT id FROM {os2web_search_search_phrases} ph WHERE :phrase LIKE ph.phrase AND (ph.period__value IS NULL or ph.period__value <= :now)
	AND  (ph.period__end_value IS NULL or ph.period__end_value >= :now)", [
        ':phrase' => $query->getOriginalKeys(),
        ':now' => $nowDateTime->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      ])->fetchCol();

    $searchPhrases = SearchPhrase::loadMultiple($ids);

    $elevateDocuments = [];
    $i = 0;
    foreach ($searchPhrases as $searchPhrase) {
      /** @var \Drupal\Core\Entity\EntityInterface $refItem */
      foreach($searchPhrase->get('items_referring')->referencedEntities() as $refItem) {
        $elevateDocuments["$siteHash-$indexId-entity:node/" . $refItem->id() . ':' . $refItem->language()->getId()] = $i;
        $i++;
      }

      if ($searchPhrase->exclusive->value) {
        $query->setOption('solr_param_exclusive', 'true');
      }
    }

    // Add the documents ids to be elevated to the search query.
    if (!empty($elevateDocuments)) {
      $query->setOption('solr_param_elevateIds', implode(',', array_keys($elevateDocuments)));
    }
  }

}
