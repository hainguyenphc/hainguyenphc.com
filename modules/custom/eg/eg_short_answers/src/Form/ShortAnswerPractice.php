<?php

namespace Drupal\eg_short_answers\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\Messenger;

class ShortAnswerPractice extends FormBase {

  static $seconds_in_a_day = 86400;

  protected $short_answer_node = NULL;

  protected $any_failed_trials = FALSE;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    $form_id = 'short_answer_practice';
    return $form_id;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\node\Entity\Node */
    $short_answer_node = $this->loadShortAnswer() ?: NULL;

    if ($short_answer_node === NULL) {
      return $form;
    }

    $last_seen_on = $short_answer_node->field_last_seen_on;
    if (!$last_seen_on->isEmpty()) {
      $timestamp = $past = $last_seen_on[0]->value;
      $timestamp = substr($timestamp, 0, 10);
      $timestamp = $this->embold($timestamp);
      $past = strtotime($past);
      $now = time();
      $days_ago = intval(round(($now - $past) / ShortAnswerPractice::$seconds_in_a_day));
      $days_ago .= ($days_ago > 1) ? ' days ago' : ' day ago';
      $form['last_seen_on'] = [
        '#markup' => $this->t('You last saw this question on ' . $timestamp . " ($days_ago)." . '<br/>'),
      ];
    }

    $correct_counts = $short_answer_node->field_correct_counts;
    if (!$correct_counts->isEmpty()) {
      $correct_counts = intval($correct_counts[0]->value);

      $suffix = ' ';
      $suffix .= $correct_counts > 1 ? 'times' : 'time';
      $suffix = $this->embold($suffix);

      $correct_counts = $this->embold($correct_counts);
      $form['correct_counts'] = [
        '#markup' => $this->t('You have correctly answered this ' . $correct_counts . $suffix . '<br/>'),
      ];
    }

    $wrong_counts = $short_answer_node->field_wrong_counts;
    if (!$wrong_counts->isEmpty()) {
      $wrong_counts = intval($wrong_counts[0]->value);

      $suffix = ' ';
      $suffix .= $wrong_counts > 1 ? 'times' : 'time';
      $suffix = $this->embold($suffix);

      $wrong_counts = $this->embold($wrong_counts);
      $form['wrong_counts'] = [
        '#markup' => $this->t('You have wrongly answered this ' . $wrong_counts . $suffix),
      ];
    }

    $form['student_answer'] = [
      '#type' => 'textarea',
      '#required' => TRUE,
      '#title' => $this->t('Answer:'),
      '#description' => $this->t('Please be as concise as possible.'),
    ];

    $learning_source = $short_answer_node->field_learning_source;
    if (!$learning_source->isEmpty()) {
      $learning_source = '<strong>Source</strong>: ' . $learning_source[0]->value;
      $form['learning_source'] = [
        '#markup' => $this->t($learning_source),
      ];
    }

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    /** @var \Drupal\node\Entity\Node */
    $short_answer_node = $this->loadShortAnswer() ?: NULL;
    $correct_answer = $short_answer_node->body[0]->value;

    if (array_key_exists('student_answer', $form)) {
      $student_answer = $form['student_answer']['#value'];
      $correct = $correct_answer == $student_answer
        || str_contains($correct_answer, $student_answer) === TRUE;
      if (!$correct) {
        $message = $this->t('Sorry this is not correct.');
        $form_state->setErrorByName('student_answer', $message);
        $this->updateWrongCounts();
      }
      else {
        $message = $this->t('Congratulations!');
        \Drupal::messenger()->addMessage($message, Messenger::TYPE_STATUS);
        $this->updateCorrectCounts();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // The form is successfully submitted i.e., the answer is eventually correct.
    if ($this->any_failed_trials) {
    // @todo: carries out some logic to bring this question up more often.
    }
    else {
    // @todo: carries out some logic to bring this question up long while later.
    }
    // @todo: updates 'Last seen on' field.
  }

  /**
   * Constructs the form title.
   *
   * @return string
   */
  public function getTitle() {
    /** @var \Drupal\node\Entity\Node */
    $short_answer_node = $this->loadShortAnswer() ?: NULL;
    /** @var string */
    $title = $short_answer_node->label();

    return [
      '#markup' => $this->t($title),
    ];
  }

  /**
   * Loads the Short Answer node from route.
   *
   * @return \Drupal\node\Entity\Node
   */
  protected function loadShortAnswer() {
    /** @var string */
    $short_answer_id = \Drupal::routeMatch()->getParameter('short_answer');
    /** @var \Drupal\node\Entity\Node */
    $this->short_answer_node = \Drupal\node\Entity\Node::load($short_answer_id);

    return $this->short_answer_node;
  }

  /**
   * Makes the content bolder.
   */
  protected function embold($content) {
    return "<strong>$content</strong>";
  }

  /**
   * Updates the wrong counts for this question.
   */
  protected function updateWrongCounts() {
    if ($this->short_answer_node) {
      $wrong_counts = $this->short_answer_node->field_wrong_counts;
      $wrong_counts =
        (!$wrong_counts->isEmpty())
        ? intval($wrong_counts[0]->value) + 1
        : 1;
      $this->short_answer_node->set('field_wrong_counts', $wrong_counts);
      $this->short_answer_node->save();
      // A failed attempt, flag this to make sure this question comes up more often.
      $this->any_failed_trials = TRUE;
    }
  }

  /**
   * Updates the correct counts for this question.
   */
  protected function updateCorrectCounts() {
    if ($this->short_answer_node) {
      $correct_counts = $this->short_answer_node->field_correct_counts;
      $correct_counts =
        (!$correct_counts->isEmpty())
        ? intval($correct_counts[0]->value) + 1
        : 1;
      $this->short_answer_node->set('field_correct_counts', $correct_counts);
      $this->short_answer_node->save();
    }
  }

}
