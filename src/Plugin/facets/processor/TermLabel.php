<?php

namespace Drupal\os2web_search\Plugin\facets\processor;

use Drupal\Core\Form\FormStateInterface;
use Drupal\facets\FacetInterface;
use Drupal\facets\Processor\BuildProcessorInterface;
use Drupal\facets\Processor\ProcessorPluginBase;
use Drupal\taxonomy\Entity\Vocabulary;

/**
 * Processor that replaces terms ID with terms names.
 *
 * Useful on the computed fields (e.g. aggregated or dummy field), when the
 * fields is not directly mapped with taxonomy vocabulary.
 *
 * @FacetsProcessor(
 *   id = "os2web_search_term_label",
 *   label = @Translation("OS2Web Search terms labels"),
 *   description = @Translation("Allows to manually specify which vocabulary must be used for providing terms label in the facet."),
 *   stages = {
 *     "build" = 40
 *   }
 * )
 */
class TermLabel extends ProcessorPluginBase implements BuildProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet) {
    $processors = $facet->getProcessors();
    $config = isset($processors[$this->getPluginId()]) ?
      $processors[$this->getPluginId()] : NULL;

    $options = [];
    $vocabularies = Vocabulary::loadMultiple();
    foreach ($vocabularies as $vid => $vocab) {
      $options[$vid] = $vocab->label();
    }
    $build['bundle'] = [
      '#type' => 'select',
      '#title' => $this->t('Vocabulary'),
      '#default_value' => !is_null($config) ? $config->getConfiguration()['bundle'] : NULL,
      '#description' => $this->t("Choose vocabulary."),
      '#options' => $options,
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet, array $results) {
    $processors = $facet->getProcessors();
    $config = $processors[$this->getPluginId()];
    $vocabulary = $config->getConfiguration()['bundle'];

    try {
      /** @var \Drupal\taxonomy\TermInterface[] $market_tree_root_terms */
      $terms = \Drupal::entityTypeManager()
        ->getStorage('taxonomy_term')
        ->loadTree($vocabulary);

      $termsNames = [];
      foreach ($terms as $term) {
        $termsNames[$term->tid] = $term->name;
      }

      foreach ($results as $key => &$result) {
        $tid = $result->getRawValue();
        $result->setDisplayValue($termsNames[$tid]);
      }
    }
    catch (\Exception $e) {
      $results = [];
    }

    return $results;
  }

}
