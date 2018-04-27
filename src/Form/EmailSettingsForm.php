<?php

namespace Drupal\playground_mail\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\NodeType;

/**
 * Configure Playground Mail settings for this site.
 */
class EmailSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'playground_mail_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['playground_mail.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['#attached']['library'][] = "playground_mail/defaults";

    $form['email_process'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Email Process Settings'),
    ];

    $form['email_process']['to_email'] = [
      '#type' => 'email',
      '#title' => $this->t('To Email'),
      '#default_value' => $this->config('playground_mail.settings')->get('to_email'),
    ];

    $form['email_process']['send_to_admins'] = [
        '#type' => 'checkbox',
        '#title'=> "Send emails to admins",
        '#description'=> 'This might be useful if you need to test other email settings such as the style settings below',
        '#default_value'=> $this->config('playground_mail.settings')->get('send_to_admins'),
    ];

      $options = [];
      $content_types = NodeType::loadMultiple();

      foreach($content_types as $content_type) {
          $options[$content_type->id()] = $content_type->label();
      }

    $form['email_process']['content_type_notify'] = [
      '#type'=> 'checkboxes',
      '#title'=> $this->t("Select which content types to notify you about updates on"),
      '#options'=> $options,
      '#default_value'=> $this->config('playground_mail.settings')->get('content_type_notify'),


    ];

    $form['styles'] = [
        '#type' => 'details',
        '#title' => $this->t('Style Settings'),
    ];

    $form['styles']['colors'] = [
        '#type'=> 'fieldgroup',
        '#title'=> $this->t("Color Settings"),
    ];

    $form['styles']['font'] = [
        '#type'=> 'fieldgroup',
        '#title'=> $this->t("Font Settings"),
    ];

    // Begin color style settings
    $form['styles']['colors']['border_color'] = [
        '#type' => 'color',
        '#title' => $this
            ->t('Border Color'),
        '#default_value' => $this->config('playground_mail.settings')->get('border_color') ?: '#000000',
    ];

    $form['styles']['colors']['background_color'] = [
        '#type' => 'color',
        '#title' => $this
            ->t('Background Color'),
        '#default_value' => $this->config('playground_mail.settings')->get('background_color') ?: '#ffffff',
    ];
    $form['styles']['colors']['text_color'] = [
        '#type' => 'color',
        '#title' => $this->t('Text Color'),
        '#default_value' => $this->config('playground_mail.settings')->get('text_color') ?: '#000000',
    ];

    $form['styles']['colors']['link_color'] = [
        '#type' => 'color',
        '#title' => $this->t('Link Color'),
        '#default_value' => $this->config('playground_mail.settings')->get('link_color') ?: '#0000EE',
    ];

    // Begin font style settings
    $form['styles']['font']['font_size'] = [
        '#type'=> 'number',
        '#title' => $this->t('Font Size'),
        '#default_value' => $this->config('playground_mail.settings')->get('font_size') ?: '18',
    ];

    $form['styles']['font']['font_unit']= [
        '#type'=> 'select',
        '#options' => [
            'px'=> 'px',
            'em'=>'em',
            'rem' => 'rem',
        ],
        '#default_value'=> $this->config('playground_mail.settings')->get('font_unit') ?: 'px',
    ];

    $form['key_differences'] = [
        '#type'=>'details',
        '#title'=> $this->t("Entity Change Display Settings"),
        '#description'=> $this->t('These settings are for emails sent after content has been changed and will affect how changes to this content are displayed to you.'),
    ];

      $form['key_differences']['color_highlight'] = [
        '#type'=> 'checkboxes',
        '#title'=> $this->t("Highlight Changes"),
        '#options' => [
            'field_key'=> $this->t("Highlight field key changes"),
            'field_value'=>$this->t("Highlight field value changes"),
        ],
        '#default_value'=> $this->config('playground_mail.settings')->get('color_highlight'),
      ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
      $email_config = $this->config('playground_mail.settings');

      // email address config
      $email_config->set('to_email', $form_state->getValue('to_email'))->save();
      $email_config->set('send_to_admins', $form_state->getValue('send_to_admins'))->save();
      $email_config->set('content_type_notify', $form_state->getValue('content_type_notify'))->save();

      // styles config
      $email_config->set('border_color', $form_state->getValue('border_color'))->save();
      $email_config ->set('background_color', $form_state->getValue('background_color'))->save();
      $email_config->set('text_color', $form_state->getValue('text_color'))->save();
      $email_config ->set('link_color', $form_state->getValue('link_color'))->save();
      $email_config ->set('font_size', $form_state->getValue('font_size'))->save();
      $email_config ->set('font_unit', $form_state->getValue('font_unit'))->save();


      $email_config->set('color_highlight', $form_state->getValue('color_highlight'))->save();

      parent::submitForm($form, $form_state);
  }


}
