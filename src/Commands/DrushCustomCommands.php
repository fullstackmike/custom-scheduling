<?php

namespace Drupal\ipm_scheduling\Commands;

use Drush\Commands\DrushCommands;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\node\Entity\Node;

/**
 * A drush command file.
 *
 * @package Drupal\ipm_scheduling\Commands
 */
class DrushCustomCommands extends DrushCommands {

  /**
   * Drush command that displays the given text.
   *
   * @param string $text
   *   Argument with message to be displayed.
   * @command ipm_scheduling:message
   * @aliases d-message d-msg
   * @option uppercase
   *   Uppercase the message.
   * @option reverse
   *   Reverse the message.
   * @usage ipm_scheduling:message --uppercase --reverse drupal8
   */
  public function message($text = 'Hello world!', $options = ['uppercase' => FALSE, 'reverse' => FALSE]) {
    if ($options['uppercase']) {
      $text = strtoupper($text);
    }
    if ($options['reverse']) {
      $text = strrev($text);
    }
    $this->output()->writeln($text);
  }

  /**
   * Drush command that migrates Doc ASAP code.
   *
   * @command ipm_scheduling:asap
   * @aliases go-asap
   * @usage ipm_scheduling:asap
   */
  public function migrate_doc_asap() {
    $this->output()->writeln('grabbing all current doc asap records');
    //get all records
    $database = \Drupal::database();
    $query = $database->select('node__field_docasap_practice_id', 'doc')
      ->fields('doc', ['bundle', 'entity_id', 'revision_id', 'langcode', 'delta', 'field_docasap_practice_id_value']);
    if (!empty($args['option_id'])) {
      $query->condition('doc.bundle', 'physician', '=');
    }
    $result = $query->execute();
    $this->output()->writeln('writing records to Scheduling Data table');

    foreach ($result as $record) {
      $level = "1";
      $asap = explode(":",$record->field_docasap_practice_id_value);
      if (count($asap)) {
        if (count($asap) == 1) {
          $asap_id = $asap[0];
        } else {
          $asap_id = $asap[0];
          $level = $asap[1];
        }
      } else {
        $this->output()->writeln('how is the array empty?');
      }
      $arguments = [$asap_id, 'UHSIPM', $level];
      $serialize_array = [
        'soid' => 2,
        'arguments' => $arguments,
      ];
      $paragraph_arguments = serialize($serialize_array);
      $this->output()->writeln($paragraph_arguments);
      $paragraph = Paragraph::create(['type' => 'scheduling_optionality']);
      $paragraph->set('field_scheduling_data', $paragraph_arguments);
      $paragraph->isNew();
      $paragraph->save();

      $node = Node::load($record->entity_id);
      //$current = $node->get('field_scheduling_option')->getValue();
      $schedule_option = array(
        'target_id' => $paragraph->id(),
        'target_revision_id' => $paragraph->getRevisionId(),
      );
      $node->set('field_scheduling_option', $schedule_option);
      $node->save();
      //$this->output()->writeln('saved node : '.$record->entity_id.' and paragraph : '.$paragraph->id());
    }
    $this->output()->writeln('Done is Done.');
  }

  /**
   * Drush command that migrates non Doc ASAP Physicans to a Webform.
   *
   * @command ipm_scheduling:webform
   * @aliases go-webform
   * @usage ipm_scheduling:webform
   */
  public function migrate_webform() {
    $this->output()->writeln('grabbing all non doc asap physician');
    //get all records
    $database = \Drupal::database();
    $query = $database->select('node__field_docasap_practice_id', 'doc')
      ->fields('doc', ['bundle', 'entity_id']);
    $query->condition('doc.bundle', 'physician', '=');
    $result = $query->execute();
    $this->output()->writeln('writing records to Scheduling Data table');
    $physicians = [];
    foreach ($result as $record) {
      $physicians[] = $record->entity_id;
    }
    //reset for new query
    $query = "";
    $result = "";
    $this->output()->writeln('Exclude NIDs : '.implode(",",$physicians));
    $query = $database->select('node', 'n')
      ->fields('n', ['type', 'nid']);
    $query->condition('n.type', 'physician', '=');
    if (!empty($physicians)) {
      $query->condition('n.nid', $physicians, 'NOT IN');
    }
    $result = $query->execute();
    foreach ($result as $record) {
      $this->output()->writeln("record : ".$record->nid);
      $arguments = [];
      $physician_name = "";
      $physician_creds = "";
      $node = Node::load($record->nid);
      $req_appointment_dest = theme_get_setting('request_appointment_button');
      $physician_name_array = $node->get('title')->getValue();
      $physician_creds_array = $node->get('field_physician_credentials')->getValue();
      if (!empty($physician_name_array[0]["value"])) {
        $physician_name = $physician_name_array[0]["value"];
      }
      if (!empty($physician_creds_array[0]["value"])) {
        $physician_creds = $physician_creds_array[0]["value"];
      }
      $arguments = [
        $record->nid,
        $physician_name . " " . $physician_creds,
      ];
      $serialize_array = [
        'soid' => 4,
        'arguments' => $arguments,
      ];
      $paragraph_arguments = serialize($serialize_array);
      $this->output()->writeln($paragraph_arguments);
      $paragraph = Paragraph::create(['type' => 'scheduling_optionality']);
      $paragraph->set('field_scheduling_data', $paragraph_arguments);
      $paragraph->isNew();
      $paragraph->save();

      $schedule_option = array(
        'target_id' => $paragraph->id(),
        'target_revision_id' => $paragraph->getRevisionId(),
      );
      $node->set('field_scheduling_option', $schedule_option);
      $node->save();
      $this->output()->writeln('saved node : '.$record->nid.' and paragraph : '.$paragraph->id());
    }
    $this->output()->writeln('Done is Done.');
  }

  /**
   * Drush command that migrates non Doc ASAP Physicans to a Webform.
   *
   * @command ipm_scheduling:ecw
   * @aliases import-ecw
   * @usage ipm_scheduling:ecw
   */
  public function import_ecw() {
    $this->output()->writeln('grabbing all non doc asap physician');

    $this->output()->writeln('Done is Done.');
  }

}
