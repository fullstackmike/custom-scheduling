<?php
namespace Drupal\ipm_scheduling\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Render\Markup;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SettingsForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ipm_scheduling.adminsettings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ipm_scheduling_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ipm_scheduling.adminsettings');
    $markup = "<h2>Welcome to the Scheduling Optionality Settings page</h2><p>Here, you can add new options for Physicians, Locations and the Site. The list below will show the current options and provide actions for editting and deleting.</p>";
    $current_options = "<h3>Find Your Site Options...</h3>";
    $header = ['ID', 'Label', 'Created', 'Changed', 'Actions'];//'Trigger','One', 'Two', 'Three'
    $result = _mysql_select();
    //Url::fromUri('https://www.webfoobar.com/taxonomy/term/23', $options);
    foreach ($result as $record) {
      $option = [];
      $arguments = unserialize($record->arguments);
      $edit_url = Url::fromUri('internal:/admin/config/system/scheduling/'.$record->soid.'/edit');
      $delete_url = Url::fromUri('internal:/admin/config/system/scheduling/'.$record->soid.'/delete');
      $option = [
        'soid' => $record->soid,
        'label' => $record->label,
        //'trigger' => $arguments['trigger'],
        //'arg1' => $arguments['arguments'][0],
        //'arg2' => $arguments['arguments'][1],
        //'arg3' => $arguments['arguments'][2],
        'created' => date("M d, Y", $record->created),
        'changed' => (!empty($record->changed)) ? date("M d, Y", $record->changed) : '---',
        'actions' => Markup::create(Link::fromTextAndUrl('Edit', $edit_url)->toString()." | ".Link::fromTextAndUrl('Delete', $delete_url)->toString()),
      ];
      $options[] = $option;
    }
    //https://www.drupal.org/node/1796238
    $table = array(
      '#theme' => 'table__schedule_options',
      '#attributes' => array('id' => 'table__schedule_options'),
      '#header' => $header,
      '#rows' => $options,
      '#responsive' => FALSE,
    );
    $current_options = render($table);
    //$current_options = theme('table', array('header' => $header, 'rows' => $options));
    $form['intro'] = [
      '#markup' => $markup,
    ];
    $form['wrapper_insert_new'] = [
      '#type' => 'details',
      '#title' => 'Add New Scheduling Option',
      '#open' => false,
    ];
    $form['wrapper_insert_new']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#description' => $this->t('Name the new option for this site.'),
      '#default_value' => '',
    ];
    $form['wrapper_insert_new']['trigger'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Trigger Event'),
      '#description' => $this->t('Code used to pass arguments to the integration'),
      '#default_value' => '',
      '#maxlength' => 1000,
    ];
    $form['wrapper_insert_new']['target'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Event Target'),
      '#description' => $this->t('Used to launch a link in a new window'),
      '#default_value' => '',
      '#maxlength' => 1000,
    ];
    $form['wrapper_insert_new']['button_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Button Label'),
      '#description' => $this->t('Label to Appear on the Button'),
      '#default_value' => '',
    ];
    $form['wrapper_insert_new']['button_style'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Button Style'),
      '#description' => $this->t('Enter the CSS Class Name to add to the button.'),
      '#default_value' => '',
    ];
    $form['wrapper_insert_new']['arg1'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Argument One'),
      '#description' => $this->t('Label of the first argument to use in the trigger'),
      '#default_value' => '',
    ];
    $form['wrapper_insert_new']['arg2'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Argument Two'),
      '#description' => $this->t('Label of the second argument to use in the trigger'),
      '#default_value' => '',
    ];
    $form['wrapper_insert_new']['arg3'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Argument Three'),
      '#description' => $this->t('Label of the third argument to use in the trigger'),
      '#default_value' => '',
    ];
    $form['wrapper_insert_new']['actions'] = [
      '#type' => 'actions',
    ];
    $form['wrapper_insert_new']['actions']['add_new'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    $form['current_options'] = [
      '#markup' => $current_options,
    ];
    //ecw/healow
    //https://healow.com/apps/provider/dina-winograd-1440400

    //doc asap : arguments (serialize)
    //script (https://docasap.com/l_js/white-label.js,https://docasap.com/l_js/environment.js)
    //param 1 (key_doc_id)
    //param 2 (key_level : options : 3,2,1, or zero : find out what they mean, maybe Primary or All Locations)
    //param 3 (key_partner_code : default : UHSIPM)
    //trigger event (with tokens for 3 params : default : key_doc_id/<token>/key_level/3/key_map/0/hide_header/1/hide_footer/1/hide_insurance/1/hide_location/1/hide_profile_infoset/0/hide_info_mssg/0/hide_profile/1/hide_partner_alert/1/hide_other_provider/1/key_type/POPUP/hide_phone/1/hide_profile_pic/1/hide_star_rating/1/key_partner_code/<token>/key_mobile_inline_button/1/hide_visitreason_other_providers/0/iframeWidth/800/iframeHeight/500)

    //Enter the Doctor or Practice ID for Doc ASAP. Add a trigger for displaying the primary or all the locations in the modal. :<1/0> (i.e. 45604:1 or 45604:0)
    //show_docasap_cobranding_iframe(document,'key_doc_id/151263/key_level/<token>>/key_map/0/hide_header/1/hide_footer/1/hide_insurance/1/hide_location/1/hide_profile_infoset/0/hide_info_mssg/0/hide_profile/1/hide_partner_alert/1/hide_other_provider/1/key_type/POPUP/hide_phone/1/hide_profile_pic/1/hide_star_rating/1/key_partner_code/UHSIPM/key_mobile_inline_button/1/hide_visitreason_other_providers/0/iframeWidth/800/iframeHeight/500')
    //<script src="https://docasap.com/l_js/white-label.js"></script>
    //<script src="https://docasap.com/l_js/environment.js"></script>

    //uid and weight
    //ability to select default in admin
    //
    //cache our options to avoid db query
    //can we show where each is used?
    //save block configuration with each block

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $database = \Drupal::database();

    $trigger = $form_state->getValue('trigger');
    $arguments = [$form_state->getValue('arg1'),$form_state->getValue('arg2'),$form_state->getValue('arg3')];
    $arguments_array = [
      'trigger' => $trigger,
      'arguments' => $arguments,
      'button_label' => $form_state->getValue('button_label'),
      'button_style' => $form_state->getValue('button_style'),
    ];
    $option = [
      'label' => $form_state->getValue('label'),
      'arguments_array' => $arguments_array,
    ];
    $result = _mysql_insert($option);

//    $this->config('ipm_scheduling.adminsettings')
//      ->set('xxx', $form_state->getValue('xxx'))
//      ->save();
  }

}
