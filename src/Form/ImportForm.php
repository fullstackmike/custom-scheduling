<?php
namespace Drupal\ipm_scheduling\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\File\FileSystemInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\node\Entity\Node;
use Drupal\Core\Messenger\MessengerInterface;

class ImportForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ipm_scheduling.import',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ipm_scheduling_import_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ipm_scheduling.import');
    $form = array(
      '#attributes' => array('enctype' => 'multipart/form-data'),
    );
    $form['intro'] = [
      '#markup' => '<h2>Scheduling Optionality eClinicalWorks Import</h2>',
    ];
    $validators = array(
      'file_validate_extensions' => array('csv'),
    );
    $form['import_csv'] = array(
      '#type' => 'file',
      '#title' => t('File *'),
      '#size' => 20,
      '#description' => t('CSV format only'),
      //'#upload_validators' => $validators,
      //'#upload_location' => 'public://ipm_scheduling_optionality/',
      '#element_validate' => ['::validateFileupload'],
    );
    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['import_csv'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }
  public static function validateFileupload(&$element, FormStateInterface $form_state, &$complete_form) {

    $validators = [
      'file_validate_extensions' => ['csv CSV'],
    ];

    // @TODO: File_save_upload will probably be deprecated soon as well.
    // @see https://www.drupal.org/node/2244513.
    if ($file = file_save_upload('import_csv', $validators, FALSE, 0, FILE_EXISTS_REPLACE)) {

      // The file was saved using file_save_upload() and was added to the
      // files table as a temporary file. We'll make a copy and let the
      // garbage collector delete the original upload.
      $csv_dir = 'temporary://ipm_scheduling_optionality';
      $directory_exists = \Drupal::service('file_system')
        ->prepareDirectory($csv_dir, FileSystemInterface::CREATE_DIRECTORY);

      if ($directory_exists) {
        $destination = $csv_dir . '/' . $file->getFilename();
        if (file_copy($file, $destination, FileSystemInterface::EXISTS_REPLACE)) {
          $form_state->setValue('import_csv', $destination);
        }
        else {
          $form_state->setErrorByName('import_csv', t('Unable to copy upload file to @dest', ['@dest' => $destination]));
        }
      }
    }
  }
  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    if ($form_state->getValue('import_csv') == NULL) {
      $form_state->setErrorByName('import_csv', $this->t('File.'));
    }


    if ($import_csv = $form_state->getValue('import_csv')) {

      if ($handle = fopen($import_csv, 'r')) {

        if ($line = fgetcsv($handle, 4096)) {

          // Validate the uploaded CSV here.
          // The example CSV happens to have cell A1 ($line[0]) as
          // below; we validate it only.
          //
          // You'll probably want to check several headers, eg:
          // @codingStandardsIgnoreStart
          // if ( $line[0] == 'Index' || $line[1] != 'Supplier' || $line[2] != 'Title' )
          // @codingStandardsIgnoreEnd
          if ($line[0] != 'Physician') {
            $form_state->setErrorByName('import_csv', $this->t('Sorry, this file does not match the expected format.'));
          }
        }
        fclose($handle);
      }
      else {
        $form_state->setErrorByName('import_csv', $this->t('Unable to read uploaded file @filepath', ['@filepath' => $import_csv]));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $database = \Drupal::database();

    $import_csv = $form_state->getValue('import_csv');
    if ($handle = fopen($import_csv, 'r')) {
      while (($data = fgetcsv($handle, 1000, ",")) !== false) {
        if (is_numeric($data[1])) {
          //$data[0] is the name of the Physician
          //$data[1] is the drupal ID
          //$data[2] is the ECW ID
          //delete existing doc asap scheduling option
          //add ecw scheduling option
          $arguments = [$data[2]];
          $serialize_array = [
            'soid' => 3,
            'arguments' => $arguments,
          ];
          $paragraph_arguments = serialize($serialize_array);
          $paragraph = Paragraph::create(['type' => 'scheduling_optionality']);
          $paragraph->set('field_scheduling_data', $paragraph_arguments);
          $paragraph->isNew();
          $paragraph->save();

          $node = Node::load($data[1]);
          //$current = $node->get('field_scheduling_option')->getValue();
          $schedule_option = array(
            'target_id' => $paragraph->id(),
            'target_revision_id' => $paragraph->getRevisionId(),
          );
          if (!empty($node)) {
            $node->set('field_scheduling_option', $schedule_option);
            $node->save();
          } else {
            //record Missing Node ID to message display
            $this->messenger->addWarning('Node not found : '.$data[1], MessengerInterface::TYPE_WARNING);
          }
        }
      }
      fclose($handle);
    }
    $dude = "import";


  }

}
