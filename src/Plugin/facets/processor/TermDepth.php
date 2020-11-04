<?php

namespace Drupal\os2web_search\Plugin\facets\processor;

use Drupal\Core\Form\FormStateInterface;
use Drupal\facets\FacetInterface;
use Drupal\facets\Processor\BuildProcessorInterface;
use Drupal\facets\Processor\ProcessorPluginBase;
use Drupal\taxonomy\Entity\Vocabulary;

/**
 * Processor that filters out results if they are deeper than the allowed depth.
 *
 * @FacetsProcessor(
 *   id = "os2web_search_term_depth",
 *   label = @Translation("OS2Web Search max term depth"),
 *   description = @Translation("Show only terms for above select level of depth inclusively"),
 *   stages = {
 *     "build" = 40
 *   }
 * )
 */
class TermDepth extends ProcessorPluginBase implements BuildProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet) {
    $processors = $facet->getProcessors();
    $config = isset($processors[$this->getPluginId()]) ?
      $processors[$this->getPluginId()] : NULL;

    $min_level = 1;
    $build['level'] = [
      '#title' => $this->t('Level'),
      '#type' => 'number',
      '#min' => $min_level,
      '#default_value' => !is_null($config) ? $config->getConfiguration()['level'] : $min_level,
      '#description' => $this->t("Only show results for this level and above being 1 the root level."),
    ];

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
    $level = $config->getConfiguration()['level'];
    $depth = $level - 1;
    $vocabulary = $config->getConfiguration()['bundle'];

    try {
      /** @var \Drupal\taxonomy\TermInterface[] $market_tree_root_terms */
      $terms = \Drupal::entityTypeManager()
        ->getStorage('taxonomy_term')
        ->loadTree($vocabulary, 0, $level, FALSE);

      $valid_terms = [];
      foreach ($terms as $term) {
        if ($term->depth <= $depth) {
          $valid_terms[] = $term->tid;
        }
      }

      /** @var \Drupal\facets\Result\ResultInterface $result */
      $filtered_results = array_filter($results, function ($result) use ($valid_terms) {
        return in_array($result->getRawValue(), $valid_terms);
      });

      $results = $filtered_results;
    }
    catch (\Exception $e) {
      $results = [];
    }

    return $results;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet) {
    parent::validateConfigurationForm($form, $form_state, $facet);
  }
}
