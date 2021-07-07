<?php
namespace Drupal\ipm_scheduling\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Render\Markup;
use Symfony\Component\HttpFoundation\RedirectResponse;

class OptionEditForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ipm_scheduling.adminoptionedit',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ipm_scheduling_option_edit_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $option_id = null) {
    if (!empty($form_state->getValue('cancel')) || empty($option_id)) {
      return new RedirectResponse(Url::fromRoute('ipm_scheduling.settings')->setAbsolute()->toString());
    }
    $config = $this->config('ipm_scheduling.adminoptionedit');
    if (!empty($option_id)) {
      $args = ['option_id' => $option_id];
      $result = _mysql_select($args);

      foreach ($result as $record) {
        $option = [];
        $arguments = unserialize($record->arguments);
        $option = [
          'soid' => $record->soid,
          'label' => $record->label,
          'trigger' => $arguments['trigger'],
          'target' => $arguments['target'],
          'button_label' => $arguments['button_label'],
          'button_style' => $arguments['button_style'],
          'arg1' => $arguments['arguments'][0],
          'arg2' => $arguments['arguments'][1],
          'arg3' => $arguments['arguments'][2],
          'created' => date("M d, Y", $record->created),
          'changed' => (!empty($record->changed)) ? date("M d, Y", $record->changed) : '---',
          'uid' => $record->uid,
        ];
      }
      $form['soid'] = [
        '#type' => 'hidden',
        '#default_value' => $option_id,
      ];
      $form['label'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Label'),
        '#description' => $this->t('Name the new option for this site.'),
        '#default_value' => $option['label'],
      ];
      $form['trigger'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Trigger Event'),
        '#description' => $this->t('Code used to pass arguments to the integration'),
        '#default_value' => $option['trigger'],
        '#maxlength' => 1000,
      ];
      $form['target'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Event Target'),
        '#description' => $this->t('Used to launch a link in a new window'),
        '#default_value' => $option['target'],
        '#maxlength' => 1000,
      ];
      $form['wrapper_insert_new']['button_label'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Button Label'),
        '#description' => $this->t('Label to Appear on the Button'),
        '#default_value' => $option['button_label'],
      ];
      $form['wrapper_insert_new']['button_style'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Button Style'),
        '#description' => $this->t('Enter the CSS Class Name to add to the button.'),
        '#default_value' => $option['button_style'],
      ];
      $form['arg1'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Argument One'),
        '#description' => $this->t('Label of the first argument to use in the trigger'),
        '#default_value' => $option['arg1'],
      ];
      $form['arg2'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Argument Two'),
        '#description' => $this->t('Label of the second argument to use in the trigger'),
        '#default_value' => $option['arg2'],
      ];
      $form['arg3'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Argument Three'),
        '#description' => $this->t('Label of the third argument to use in the trigger'),
        '#default_value' => $option['arg3'],
      ];
      $form['uid'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Author'),
        '#description' => $this->t('Author of the Option'),
        '#default_value' => $option['uid'],
      ];
      $form['actions'] = [
        '#type' => 'actions',
      ];
      $form['actions']['edit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Save'),
      ];
      $form['actions']['cancel'] = [
        '#type' => 'button',
        '#value' => $this->t('Cancel'),
      ];
    } else {
      $form['intro'] = [
        '#markup' => "No Soid Found",
      ];
    }
    return $form;//parent::buildForm($form, $form_state);
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $database = \Drupal::database();

    $soid = $form_state->getValue('soid');
    $trigger = $form_state->getValue('trigger');
    $target = $form_state->getValue('target');
    $button_label = $form_state->getValue('button_label');
    $button_style = $form_state->getValue('button_style');
    $arguments = [$form_state->getValue('arg1'), $form_state->getValue('arg2'), $form_state->getValue('arg3')];
    $arguments_array = [
      'trigger' => $trigger,
      'target' => $target,
      'button_label' => $button_label,
      'button_style' => $button_style,
      'arguments' => $arguments,
    ];
    $query = $database->update('ipm_scheduling_optionality')
      ->fields([
        'label' => $form_state->getValue('label'),
        'arguments' => serialize($arguments_array),
        //'created' => \Drupal::time()->getRequestTime(),
        'changed' => \Drupal::time()->getRequestTime(),
        //'uid' => $form_state->getValue('uid'),
      ])
      ->condition('soid', $soid, '=')
      ->execute();
    $form_state->setRedirectUrl(Url::fromRoute('ipm_scheduling.settings')->setAbsolute());
  }

}
