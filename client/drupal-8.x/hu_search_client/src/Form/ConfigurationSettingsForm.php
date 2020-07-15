<?php

namespace Drupal\hu_search_client\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures forms module settings.
 */
class ConfigurationSettingsForm extends ConfigFormBase
{

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'hu_search_client_admin_settings';
    }

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames()
    {
        return [
            'hu_search_client.settings',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $config = $this->config('hu_search_client.settings');

        $form['search_page_title'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Search Page Title'),
            '#default_value' => $config->get('search_page_title'),
        ];

        $form['show_page_title'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Show Search Page Title'),
            '#default_value' => $config->get('show_page_title'),
        ];

        $form['results_per_page_limit'] = [
            '#type' => 'number',
            '#title' => $this->t('Maximum Results Per Page'),
            '#default_value' => $config->get('results_per_page_limit'),
        ];

        $form['default_search_api_index'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Local Solr index name to reference.'),
            '#default_value' => $config->get('default_search_api_index'),
        ];
		
		$form['howard_search_endpoint_config_json'] = [
            '#type' => 'textarea',
            '#title' => $this->t('External Search Endpoint Configuration'),
            '#default_value' => $config->get('howard_search_endpoint_config_json'),
        ];
		
		$form['service_account_username'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Search Service Account Username'),
            '#default_value' => $config->get('service_account_username'),
        ];
		
		$form['service_account_password'] = [
            '#type' => 'password',
            '#title' => $this->t('Search Service Account Password'),
            '#default_value' => $config->get('service_account_password'),
        ];

        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        // Retrieve the configuration.
        $this->configFactory->getEditable('hu_search_client.settings')
            ->set('search_page_title', $form_state->getValue('search_page_title'))
			->set('show_page_title', $form_state->getValue('show_page_title'))
            ->set('results_per_page_limit', $form_state->getValue('results_per_page_limit'))
			->set('default_search_api_index', $form_state->getValue('default_search_api_index'))
            ->set('howard_search_endpoint_config_json', $form_state->getValue('howard_search_endpoint_config_json'))
			->set('service_account_username', $form_state->getValue('service_account_username'))
			->set('service_account_password', $form_state->getValue('service_account_password'))
            ->save();

        parent::submitForm($form, $form_state);
    }
}
