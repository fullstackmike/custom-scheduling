<?php

namespace Drupal\ipm_scheduling\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id = "ipm_scheduling_optionality_block",
 *   admin_label = @Translation("Scheduling Optionality Block"),
 * )
 */
class IPMSchedulingOptionalityBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $option = @unserialize($config['ipm_scheduling_optionality_block_settings']);
    $row = (object)['type'=>'block','associated_option'=>$option];
    $button = process_scheduling_optionality_field($row);
    return [
      '#theme' => 'scheduling_option_block',
      '#button' => $button,
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $block_settings = [];
    $config = $this->getConfiguration();
    if (!empty($config['ipm_scheduling_optionality_block_settings'])) {
      $block_settings = $config['ipm_scheduling_optionality_block_settings'];
    }
    //grab field and deserialize them and associate them to values for the selected option
    $option_array = [];
    $form_array = [];
    $arg_array = [];
    $data = [];
    $default_value = 0;
    if (!empty($block_settings)) {
      $data = @unserialize($block_settings);
      $default_value = $data['soid'];
    }
    $result = _mysql_select();
    foreach ($result as $record) {
      if (!empty($record->arguments)) {
        $fields = unserialize($record->arguments);
      }
      $option_array[$record->soid] = $record->label;
      //build forms for each option
      if (!empty($fields)) {
        foreach ($fields['arguments'] as $key => $arg) {
          if (!empty($arg)) {
            $default = "";
            if ($default_value == $record->soid) {
              if (!empty($data['arguments'][$key])) {
                $default = $data['arguments'][$key];
              }
            }
            $form_array[$record->soid][] = [
              'title' => $arg,
              'default' => $default,
            ];
          }
        }
      }
    }
    $form['field_scheduling_data']['#title'] = 'Scheduling Optionality';
    $form['field_scheduling_data']['#type'] = 'select';
    $form['field_scheduling_data']['#options'] = $option_array;
    $form['field_scheduling_data']['#default_value'] = $default_value;
    $form['field_scheduling_data']['#attributes'] = ["class"=>["field--name-field-scheduling-data"]];
    $form['field_scheduling_data']['#attached']['library'][] = "ipm_scheduling/admin_js";
    /*$form['button_label'] = [
      '#type' => 'textfield',
      '#title' => 'Button Label',
      '#default_value' => '',
    ];*/
    //get argument labels
    foreach($form_array AS $formkey => $formarguments) {
      $arg_array[$formkey] = [];
      foreach($formarguments AS $argkey => $arg) {
        $string_array = ["/[ .()-]/i"];
        $arg_machine_name = preg_replace($string_array, "", strtolower($arg['title']));
        $form['field_so' . $formkey . '_arg_' . $arg_machine_name] = [
          '#type' => 'textfield',
          '#title' => $arg['title'],
          //'#attributes' => array('class' => array('ipm_scheduling_form_element','form_'.$formkey)),
          '#default_value' => $arg['default'],
          '#wrapper_attributes' => ['class' => array('ipm_scheduling_form_element','form_'.$formkey)],
          //'#markup' => 'field_so' . $formkey . '_arg_' . $arg_machine_name,
        ];
        $arg_array[$formkey][] = $arg_machine_name;
        //write JS to hide form elements based on selection
      }
    }
    if (!empty($arg_array)) {
      $form['argument_fields'] = [
        '#type' => 'hidden',
        '#value' => serialize($arg_array),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $argument_array = [];
    $soid = $form_state->getValue('field_scheduling_data');
    $argument_fields = unserialize($form_state->getValue('argument_fields'));
    if (!empty($soid)) {
      if (!empty($argument_fields[$soid])) {
        foreach($argument_fields[$soid] AS $arg) {
          $argument_array[] =  $form_state->getValue('field_so' . $soid . '_arg_' . $arg);
        }
      }
    }
    $option_storage = [
      'soid' => $soid,
      'arguments' => $argument_array
    ];
    $this->configuration['ipm_scheduling_optionality_block_settings'] = serialize($option_storage);
  }
}
