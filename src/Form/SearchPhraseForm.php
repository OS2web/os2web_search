<?php

namespace Drupal\os2web_search\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the os2web_search_phrase entity edit forms.
 * @ingroup bc_search
 */
class SearchPhraseForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $entity = $this->getEntity();

    /* @var $entity \Drupal\os2web_search\Entity\SearchPhrase */
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $form_state->setRedirect('entity.os2web_search_phrase.collection');
    $entity = $this->getEntity();
    $entity->save();
  }
}
