<?php

namespace Drupal\ipm_scheduling\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'field_ipm_scheduling' widget.
 *
 * @FieldWidget(
 *   id = "field_ipm_scheduling",
 *   module = "ipm_scheduling",
 *   label = @Translation("Scheduling Optionality"),
 *   field_types = {
 *     "string",
 *     "string_long",
 *     "text_long",
 *     "text",
 *     "list_string",
 *   }
 * )
 */
class IPMSchedulingWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $default_value = 0;
    $list = $items->getValue('list');
    // Set up the form element for this widget in the Field Edit Settings.
    $element += [
      '#type' => 'textfield',
      '#default_value' => $list[0]['value'],
    ];

    return ['value' => $element];
  }

  /**
   * Validate the fields and convert them into a single value as text.
   */
  public function validate($element, FormStateInterface $form_state) {
    $value = $element['#value'];
    //$field_scheduling_option = $form_state->getValueForElement('field_scheduling_option');

    //$form_state->setValueForElement($element, 'Fun');
  }

}
