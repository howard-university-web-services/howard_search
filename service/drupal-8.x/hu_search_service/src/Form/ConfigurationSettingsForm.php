<?php

namespace Drupal\hu_search_service\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings key for Solr index reference.
 */
const FORM_SOLR_INDEX_KEY = 'hu_search_service_solr_index';

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
        return 'hu_search_service_admin_settings';
    }

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames()
    {
        return [
            'hu_search_service.settings',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $config = $this->config('hu_search_service.settings');
		
		$form[FORM_SOLR_INDEX_KEY] = [
            '#type' => 'textfield',
            '#title' => $this->t('Solr index name to reference.'),
            '#default_value' => $config->get(FORM_SOLR_INDEX_KEY),
        ];

        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        // Retrieve the configuration.
        $this->configFactory->getEditable('hu_search_service.settings')
            ->set(FORM_SOLR_INDEX_KEY, $form_state->getValue(FORM_SOLR_INDEX_KEY))
            ->save();

        parent::submitForm($form, $form_state);
    }
}
