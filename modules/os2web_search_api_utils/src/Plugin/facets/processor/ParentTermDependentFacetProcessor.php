<?php

namespace Drupal\os2web_search_api_utils\Plugin\facets\processor;

use Drupal\Core\Cache\UnchangingCacheableDependencyTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\facets\FacetInterface;
use Drupal\facets\Processor\BuildProcessorInterface;
use Drupal\facets\Processor\ProcessorPluginBase;
use Drupal\facets\FacetManager\DefaultFacetManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\taxonomy\Entity\Vocabulary;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a processor that combines results of different facets.
 *
 * @FacetsProcessor(
 *   id = "os2web_search_search_api_parent_term_dependent_processor",
 *   label = @Translation("OS2Web search parent term dependent facet processor"),
 *   description = @Translation("todo"),
 *   stages = {
 *     "build" = 5
 *   }
 * )
 */
class ParentTermDependentFacetProcessor extends ProcessorPluginBase implements BuildProcessorInterface, ContainerFactoryPluginInterface {

  use UnchangingCacheableDependencyTrait;

  /**
   * The language manager.
   *
   * @var \Drupal\facets\FacetManager\DefaultFacetManager
   */
  protected $facetsManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $facetStorage;

  /**
   * Constructs a new object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\facets\FacetManager\DefaultFacetManager $facets_manager
   *   The language manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, DefaultFacetManager $facets_manager, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->facetsManager = $facets_manager;
    $this->facetStorage = $entity_type_manager->getStorage('facets_facet');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('facets.manager'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $current_facet) {
    $build = [];

    $config = $this->getConfiguration();

    // Loop over all defined blocks and filter them by provider, this builds an
    // array of blocks that are provided by the facets module.
    /** @var \Drupal\facets\Entity\Facet[] $facets */
    $facets = $this->facetStorage->loadMultiple();

    $options = [];

    foreach ($facets as $facet) {
      if ($facet->id() === $current_facet->id()) {
        continue;
      }

      $options[$facet->id()] = $facet->getName() . ' (' . $facet->getFacetSourceId() . ')';
    }

    $build['parent_facet_id'] = [
      '#title' => $this->t('Parent facet'),
      '#type' => 'radios',
      '#options' => $options,
      '#default_value' => $config['parent_facet_id'] ?? NULL,
    ];

    $vocabOptions = [];
    $vocabularies = Vocabulary::loadMultiple();
    foreach ($vocabularies as $vid => $vocab) {
      $vocabOptions[$vid] = $vocab->label();
    }
    $build['bundle'] = [
      '#type' => 'select',
      '#title' => $this->t('Vocabulary'),
      '#default_value' => $config['bundle'] ?? NULL,
      '#description' => $this->t("Choose vocabulary."),
      '#options' => $vocabOptions,
    ];

    return parent::buildConfigurationForm($form, $form_state, $current_facet) + $build;
  }

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet, array $results) {
    $configuration = $this->getConfiguration();
    $vocabulary = $configuration['bundle'];

    $parentFacetID = $configuration['parent_facet_id'];
    /** @var \Drupal\facets\Entity\Facet $parentFacet */
    $parentFacet = $this->facetStorage->load($parentFacetID);
    $parentFacet = $this->facetsManager->returnBuiltFacet($parentFacet);
    $facet->addCacheableDependency($parentFacet);

    $parentFacetActiveItems = $parentFacet->getActiveItems();

    $allowedTermsIds = [];
    foreach ($parentFacetActiveItems as $activeTerm) {
      try {
        /** @var \Drupal\taxonomy\TermInterface[] $market_tree_root_terms */
        $childTerms = \Drupal::entityTypeManager()
          ->getStorage('taxonomy_term')
          ->loadTree($vocabulary, $activeTerm);

        $allowedTermsIds += array_column($childTerms, 'tid');
      }
      catch (\Exception $e) {
        $results = [];
      }
    }

    /** @var \Drupal\facets\Result\ResultInterface $result */
    $filtered_results = array_filter($results, function ($result) use ($allowedTermsIds) {
      return in_array($result->getRawValue(), $allowedTermsIds);
    });

    $results = $filtered_results;

    return $results;
  }

}
