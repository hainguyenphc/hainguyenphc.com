<?php

namespace Drupal\rest_api_authentication;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\rest_api_authentication\Utilities;

class MoHeadlessIntegration {

  public static function insertForm(array &$form, FormStateInterface $form_state) {

    $form['markup_library_1'] = [
      '#attached' => [
        'library' => [
          "rest_api_authentication/rest_api_authentication.style_settings",
        ],
      ],
    ];

    $form['headless_sso_details'] = [
      '#type' => 'details',
      '#title' => t('Headless SSO'),
      '#open' => TRUE,
      '#group' => 'verticaltabs',
    ];


    self::headlessSSOFieldset($form,$form_state);

    return $form;
  }

  private static function headlessSSOFieldset(array &$form, FormStateInterface $form_state) {
    $base_url = \Drupal::request()->getSchemeAndHttpHost().\Drupal::request()->getBasePath();

    $form['headless_sso_details']['headless_sso'] = [
      '#markup' => t('<b>Headless SSO (Single Sign On) </b><a href = ":upgradePlan" style="font-size: small" >PREMIUM</a><a style="float: right;" href=":guideUrl" target="_blank" class="button button--small" >setup guide</a>',
        [
          ':guideUrl' => 'https://www.drupal.org/docs/contributed-modules/api-authentication',
          ':upgradePlan' => $base_url . '/admin/config/people/rest_api_authentication/auth_settings?tab=edit-upgrade-plans',
        ]),
    ];

    $form ['headless_sso_details']['headless_sso']['sso_protocol'] = [
      '#prefix' => t('<p  style="font-size: small"> This section help you to setup the headless sso with the help of the <a href=":oauthClient" target="_blank">Drupal OAuth Client</a> or <a href=":saml" target="_blank">miniOrange SAML module</a>.</p> <hr>',
        [
          ':oauthClient' => 'https://www.drupal.org/project/miniorange_oauth_client',
          ':saml' => 'https://www.drupal.org/project/miniorange_saml',
        ] ),
    ];

    $form['headless_sso_details']['headless_sso']['headless_sso_table'] = [
      '#type' => 'table',
      '#responsive' => TRUE ,
      '#attributes' => ['style' => 'border-collapse: separate;',],
    ];

    $configurations  = Utilities::getHeadlessTableAttr();

    foreach ($configurations as $key => $value) {
      $row = self::generateHeadlessSooTableRow($key, $value);
      $form['headless_sso_details']['headless_sso']['headless_sso_table'][$key] = $row;
    }

    $form['headless_sso_details']['headless_sso']['save_button'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => 'Save Settings',
      '#disabled' => true,

    ];

  }
  private static function generateHeadlessSooTableRow(string $key, string $value) {
    $config = \Drupal::config('rest_api_authentication');
    $row[$key.$value] = [
      '#markup' => '<div class="container-inline" ><strong>'.$value.'</strong>',
    ];
    $base_url = \Drupal::request()->getSchemeAndHttpHost().\Drupal::request()->getBasePath();
    if($key == 'module'){
      $row[$key] = [
        '#type' => 'radios',
        '#title' => '',
        '#options' => [0 => t('OAuth Client module'), 1 => t('SAML SP module')],
        '#attributes' => [
          'class' => ['container-inline'],
        ],
        '#disabled' => true,
      ];
    }else if($key == 'frontend_url'){
      $row[$key] = [
        '#type' => 'textfield',
        '#description' => t('Enter the frontend URL where the user will be redirected after SSO.'),
        '#attributes' => ['style' => 'width:50%'],
        '#disabled' => true,

      ];
    }else if( $key == 'get_token_url'){
      $row[$key] = [
        '#markup' => Markup::create('<span id = "'.$key.'">' . $base_url . '/get-token</span>&nbsp;
                '),
      ];
    }else {
      $row[$key] = [
        '#type' => 'radios',
        '#title' => '',
        '#states' => ['visible' => [':input[name = "module"]' => ['value' => 0 ], ],],
        '#options' => [
          0 => t('Send JWT created by the module'),
          1 => t('send JWT received from the OAuth Server'),
        ],
        '#attributes' => [
          'class' => ['container-inline'],
        ],
        '#disabled' => true,
      ];
    }
    return $row;
  }
}
