<?php

namespace Drupal\ipm_scheduling\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'Scheduling Widget' formatter.
 *
 * @FieldFormatter(
 *   id = "scheduling_widget",
 *   label = @Translation("Scheduling Widget"),
 *   field_types = {
 *     "string",
 *     "string_long",
 *     "text_long",
 *     "text",
 *     "list_string",
 *   }
 * )
 */
class IPMSchedulingWidgetFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        // Implement default settings.
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return [
        // Implement settings form.
      ] + parent::settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Displays Formatted Scheduling Option.');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      $option_raw = $item->value;
      // Render each element as markup.
      if (!is_numeric($option_raw)) {
        $data = unserialize($option_raw);
        $default_value = $data['soid'];
      }
      $result = process_scheduling_optionality_field($data);
      $element[$delta] = ['#markup' => $result];
    }

    return $element;
  }

  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return string
   *   The textual output generated.
   */
  protected function viewValue(FieldItemInterface $item) {
    // The text value has no text format assigned to it, so the user input
    // should equal the output, including newlines.
    return "dude";//nl2br(Html::escape($item->value));
  }

}
