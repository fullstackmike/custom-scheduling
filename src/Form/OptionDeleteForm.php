<?php

namespace Drupal\ipm_scheduling\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Render\Markup;
use Symfony\Component\HttpFoundation\RedirectResponse;

class OptionDeleteForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ipm_scheduling.adminoptiondelete',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ipm_scheduling_option_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $option_id = null) {
    if (!empty($form_state->getValue('cancel')) || empty($option_id)) {
      return new RedirectResponse(Url::fromRoute('ipm_scheduling.settings')->setAbsolute()->toString());
    }
    $config = $this->config('ipm_scheduling.adminoptionedelete');
    $database = \Drupal::database();
    $query = $database->select('ipm_scheduling_optionality', 'iso')
      ->fields('iso', ['soid', 'label', 'arguments', 'created', 'changed'])
      ->condition('iso.soid', $option_id, '=');
    $result = $query->execute();
    $row_count = $query->countQuery()->execute()->fetchField();
    if ($row_count > 0) {
      foreach ($result as $record) {
        $option = [];
        $arguments = unserialize($record->arguments);
        $option = [
          'soid' => $record->soid,
          'label' => $record->label,
          'trigger' => $arguments['trigger'],
          'arg1' => $arguments['arguments'][0],
          'arg2' => $arguments['arguments'][1],
          'arg3' => $arguments['arguments'][2],
          'created' => date("M d, Y", $record->created),
          'changed' => (!empty($record->changed)) ? date("M d, Y", $record->changed) : '---',
        ];
      }
      $form['soid'] = [
        '#type' => 'hidden',
        '#default_value' => $option_id,
      ];
      $form['intro'] = [
        '#markup' => "<h2>Delete Option : " . $option['label'] . '</h2>',
      ];
      $form['actions'] = [
        '#type' => 'actions',
      ];
      $form['actions']['edit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Delete'),
      ];
      $form['actions']['cancel'] = [
        '#type' => 'button',
        '#value' => $this->t('Cancel'),
      ];
    } else {
      return new RedirectResponse(Url::fromRoute('ipm_scheduling.settings')->setAbsolute()->toString());
    }
    return $form;//parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $database = \Drupal::database();

    $soid = $form_state->getValue('soid');
    if (!empty($soid)) {
      $query = $database->delete('ipm_scheduling_optionality')
        ->condition('soid', $soid, '=')
        ->execute();
      //set message
      return new RedirectResponse(Url::fromRoute('ipm_scheduling.settings')->setAbsolute()->toString());
    }
  }

}
