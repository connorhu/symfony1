<?php

namespace Symfony1\Components\Generator;

use InvalidArgumentException;
use Symfony1\Components\Form\Form;
use Symfony1\Components\Util\Inflector;
use function sprintf;
use function preg_match;
use function str_replace;
use function array_merge;
use function ucfirst;
use function is_array;
use function reset;
use function explode;
use function count;
use function strpos;
use function substr;
use function array_keys;
use function preg_match_all;
use function strtolower;
use const PREG_PATTERN_ORDER;
/**
 * Model generator configuration.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version SVN: $Id$
 */
abstract class ModelGeneratorConfiguration
{
    /**
     * @var ModelGeneratorConfigurationField[][][]
     */
    protected $configuration = array();
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->compile();
    }
    public abstract function getActionsDefault();
    public abstract function getFormActions();
    public abstract function getNewActions();
    public abstract function getEditActions();
    public abstract function getListObjectActions();
    public abstract function getListActions();
    public abstract function getListBatchActions();
    public abstract function getListParams();
    public abstract function getListLayout();
    public abstract function getListTitle();
    public abstract function getEditTitle();
    public abstract function getNewTitle();
    public abstract function getFilterDisplay();
    public abstract function getFormDisplay();
    public abstract function getNewDisplay();
    public abstract function getEditDisplay();
    public abstract function getListDisplay();
    public abstract function getFieldsDefault();
    public abstract function getFieldsList();
    public abstract function getFieldsFilter();
    public abstract function getFieldsForm();
    public abstract function getFieldsEdit();
    public abstract function getFieldsNew();
    public abstract function getFormClass();
    public abstract function hasFilterForm();
    public abstract function getFilterFormClass();
    /**
     * @param string $context
     * @param (string[] | null) $fields
     *
     * @return (array | ModelGeneratorConfigurationField[])
     */
    public function getContextConfiguration($context, $fields = null)
    {
        if (!isset($this->configuration[$context])) {
            throw new InvalidArgumentException(sprintf('The context "%s" does not exist.', $context));
        }
        if (null === $fields) {
            return $this->configuration[$context];
        }
        $f = array();
        foreach ($fields as $field) {
            $f[$field] = $this->configuration[$context]['fields'][$field];
        }
        return $f;
    }
    public function getFieldConfiguration($context, $field)
    {
        if (!isset($this->configuration[$context])) {
            throw new InvalidArgumentException(sprintf('The context "%s" does not exist.', $context));
        }
        if (!isset($this->configuration[$context]['fields'][$field])) {
            throw new InvalidArgumentException(sprintf('Field "%s" does not exist.', $field));
        }
        return $this->configuration[$context]['fields'][$field];
    }
    /**
     * Gets the configuration for a given field.
     *
     * @param string $key The configuration key (title.list.name for example)
     * @param mixed $default The default value if none has been defined
     * @param bool $escaped Whether to escape single quote (false by default)
     *
     * @return mixed The configuration value
     */
    public function getValue($key, $default = null, $escaped = false)
    {
        if (preg_match('/^(?P<context>[^\\.]+)\\.fields\\.(?P<field>[^\\.]+)\\.(?P<key>.+)$/', $key, $matches)) {
            $v = $this->getFieldConfiguration($matches['context'], $matches['field'])->getConfig($matches['key'], $default);
        } elseif (preg_match('/^(?P<context>[^\\.]+)\\.(?P<key>.+)$/', $key, $matches)) {
            $v = ModelGeneratorConfiguration::getFieldConfigValue($this->getContextConfiguration($matches['context']), $matches['key'], $default);
        } else {
            throw new InvalidArgumentException(sprintf('Configuration key "%s" is invalid.', $key));
        }
        return $escaped ? str_replace("'", "\\'", $v) : $v;
    }
    /**
    * Gets the fields that represents the filters.
    *
    * If no filter.display parameter is passed in the configuration,
    all the fields from the form are returned (dynamically).
    *
    * @param Form $form The form with the fields
    *
    * @return array
    */
    public function getFormFilterFields(Form $form)
    {
        $config = $this->getConfig();
        if ($this->getFilterDisplay()) {
            $fields = array();
            foreach ($this->getFilterDisplay() as $name) {
                list($name, $flag) = ModelGeneratorConfigurationField::splitFieldWithFlag($name);
                if (!isset($this->configuration['filter']['fields'][$name])) {
                    $this->configuration['filter']['fields'][$name] = new ModelGeneratorConfigurationField($name, array_merge(isset($config['default'][$name]) ? $config['default'][$name] : array(), isset($config['filter'][$name]) ? $config['filter'][$name] : array(), array('is_real' => false, 'type' => 'Text', 'flag' => $flag)));
                }
                $field = $this->configuration['filter']['fields'][$name];
                $field->setFlag($flag);
                $fields[$name] = $field;
            }
            return $fields;
        }
        $fields = array();
        foreach ($form->getWidgetSchema()->getPositions() as $name) {
            $fields[$name] = new ModelGeneratorConfigurationField($name, array_merge(array('type' => 'Text'), isset($config['default'][$name]) ? $config['default'][$name] : array(), isset($config['filter'][$name]) ? $config['filter'][$name] : array(), array('is_real' => false)));
        }
        return $fields;
    }
    /**
    * Gets the fields that represents the form.
    *
    * If no form.display parameter is passed in the configuration,
    all the fields from the form are returned (dynamically).
    *
    * @param Form $form The form with the fields
    * @param string $context The display context
    *
    * @return array
    */
    public function getFormFields(Form $form, $context)
    {
        $config = $this->getConfig();
        $method = sprintf('get%sDisplay', ucfirst($context));
        if (!($fieldsets = $this->{$method}())) {
            $fieldsets = $this->getFormDisplay();
        }
        if ($fieldsets) {
            $fields = array();
            // with fieldsets?
            if (!is_array(reset($fieldsets))) {
                $fieldsets = array('NONE' => $fieldsets);
            }
            foreach ($fieldsets as $fieldset => $names) {
                if (!$names) {
                    continue;
                }
                $fields[$fieldset] = array();
                foreach ($names as $name) {
                    list($name, $flag) = ModelGeneratorConfigurationField::splitFieldWithFlag($name);
                    if (!isset($this->configuration[$context]['fields'][$name])) {
                        $this->configuration[$context]['fields'][$name] = new ModelGeneratorConfigurationField($name, array_merge(isset($config['default'][$name]) ? $config['default'][$name] : array(), isset($config['form'][$name]) ? $config['form'][$name] : array(), isset($config[$context][$name]) ? $config[$context][$name] : array(), array('is_real' => false, 'type' => 'Text', 'flag' => $flag)));
                    }
                    $field = $this->configuration[$context]['fields'][$name];
                    $field->setFlag($flag);
                    $fields[$fieldset][$name] = $field;
                }
            }
            return $fields;
        }
        $fields = array();
        foreach ($form->getWidgetSchema()->getPositions() as $name) {
            $fields[$name] = new ModelGeneratorConfigurationField($name, array_merge(array('type' => 'Text'), isset($config['default'][$name]) ? $config['default'][$name] : array(), isset($config['form'][$name]) ? $config['form'][$name] : array(), isset($config[$context][$name]) ? $config[$context][$name] : array(), array('is_real' => false)));
        }
        return array('NONE' => $fields);
    }
    /**
     * Gets the value for a given key.
     *
     * @param array $config The configuration
     * @param string $key The key name
     * @param mixed $default The default value
     *
     * @return mixed The key value
     */
    public static function getFieldConfigValue($config, $key, $default = null)
    {
        $ref =& $config;
        $parts = explode('.', $key);
        $count = count($parts);
        for ($i = 0; $i < $count; ++$i) {
            $partKey = $parts[$i];
            if (!isset($ref[$partKey])) {
                return $default;
            }
            if ($count == $i + 1) {
                return $ref[$partKey];
            }
            $ref =& $ref[$partKey];
        }
        return $default;
    }
    public function getCredentials($action)
    {
        if (0 === strpos($action, '_')) {
            $action = substr($action, 1);
        }
        return isset($this->configuration['credentials'][$action]) ? $this->configuration['credentials'][$action] : array();
    }
    public function getPager($model)
    {
        // TODO: Probably `getPagerClass()` method should be abstract here. As well as `getPagerMaxPerPage`
        $class = $this->getPagerClass();
        return new $class($model, $this->getPagerMaxPerPage());
    }
    /**
     * Gets a new form object.
     *
     * @param array $options An array of options to merge with the options returned by getFormOptions()
     * @param (mixed | null) $object
     *
     * @return Form
     */
    public function getForm($object = null, $options = array())
    {
        $class = $this->getFormClass();
        return new $class($object, array_merge($this->getFormOptions(), $options));
    }
    public function getFormOptions()
    {
        return array();
    }
    public function getFilterForm($filters)
    {
        $class = $this->getFilterFormClass();
        return new $class($filters, $this->getFilterFormOptions());
    }
    public function getFilterFormOptions()
    {
        return array();
    }
    public function getFilterDefaults()
    {
        return array();
    }
    protected function compile()
    {
        $config = $this->getConfig();
        // inheritance rules:
        // new|edit < form < default
        // list < default
        // filter < default
        $this->configuration = array('list' => array('fields' => array(), 'layout' => $this->getListLayout(), 'title' => $this->getListTitle(), 'actions' => $this->getListActions(), 'object_actions' => $this->getListObjectActions(), 'params' => $this->getListParams()), 'filter' => array('fields' => array()), 'form' => array('fields' => array()), 'new' => array('fields' => array(), 'title' => $this->getNewTitle(), 'actions' => $this->getNewActions() ?: $this->getFormActions()), 'edit' => array('fields' => array(), 'title' => $this->getEditTitle(), 'actions' => $this->getEditActions() ?: $this->getFormActions()));
        foreach (array_keys($config['default']) as $field) {
            $formConfig = array_merge($config['default'][$field], isset($config['form'][$field]) ? $config['form'][$field] : array());
            $this->configuration['list']['fields'][$field] = new ModelGeneratorConfigurationField($field, array_merge(array('label' => Inflector::humanize(Inflector::underscore($field))), $config['default'][$field], isset($config['list'][$field]) ? $config['list'][$field] : array()));
            $this->configuration['filter']['fields'][$field] = new ModelGeneratorConfigurationField($field, array_merge($config['default'][$field], isset($config['filter'][$field]) ? $config['filter'][$field] : array()));
            $this->configuration['new']['fields'][$field] = new ModelGeneratorConfigurationField($field, array_merge($formConfig, isset($config['new'][$field]) ? $config['new'][$field] : array()));
            $this->configuration['edit']['fields'][$field] = new ModelGeneratorConfigurationField($field, array_merge($formConfig, isset($config['edit'][$field]) ? $config['edit'][$field] : array()));
        }
        // "virtual" fields for list
        foreach ($this->getListDisplay() as $field) {
            list($field, $flag) = ModelGeneratorConfigurationField::splitFieldWithFlag($field);
            $this->configuration['list']['fields'][$field] = new ModelGeneratorConfigurationField($field, array_merge(array('type' => 'Text', 'label' => Inflector::humanize(Inflector::underscore($field))), isset($config['default'][$field]) ? $config['default'][$field] : array(), isset($config['list'][$field]) ? $config['list'][$field] : array(), array('flag' => $flag)));
        }
        // form actions
        foreach (array('edit', 'new') as $context) {
            foreach ($this->configuration[$context]['actions'] as $action => $parameters) {
                $this->configuration[$context]['actions'][$action] = $this->fixActionParameters($action, $parameters);
            }
        }
        // list actions
        foreach ($this->configuration['list']['actions'] as $action => $parameters) {
            $this->configuration['list']['actions'][$action] = $this->fixActionParameters($action, $parameters);
        }
        // list batch actions
        $this->configuration['list']['batch_actions'] = array();
        foreach ($this->getListBatchActions() as $action => $parameters) {
            $parameters = $this->fixActionParameters($action, $parameters);
            $action = 'batch' . ucfirst(0 === strpos($action, '_') ? substr($action, 1) : $action);
            $this->configuration['list']['batch_actions'][$action] = $parameters;
        }
        // list object actions
        foreach ($this->configuration['list']['object_actions'] as $action => $parameters) {
            $this->configuration['list']['object_actions'][$action] = $this->fixActionParameters($action, $parameters);
        }
        // list field configuration
        $this->configuration['list']['display'] = array();
        foreach ($this->getListDisplay() as $name) {
            list($name, $flag) = ModelGeneratorConfigurationField::splitFieldWithFlag($name);
            if (!isset($this->configuration['list']['fields'][$name])) {
                throw new InvalidArgumentException(sprintf('The field "%s" does not exist.', $name));
            }
            $field = $this->configuration['list']['fields'][$name];
            $field->setFlag($flag);
            $this->configuration['list']['display'][$name] = $field;
        }
        // parse the %%..%% variables, remove flags and add default fields where
        // necessary (fixes #7578)
        $this->parseVariables('list', 'params');
        $this->parseVariables('edit', 'title');
        $this->parseVariables('list', 'title');
        $this->parseVariables('new', 'title');
        // action credentials
        $this->configuration['credentials'] = array('list' => array(), 'new' => array(), 'create' => array(), 'edit' => array(), 'update' => array(), 'delete' => array());
        foreach ($this->getActionsDefault() as $action => $params) {
            if (0 === strpos($action, '_')) {
                $action = substr($action, 1);
            }
            $this->configuration['credentials'][$action] = isset($params['credentials']) ? $params['credentials'] : array();
            $this->configuration['credentials']['batch' . ucfirst($action)] = isset($params['credentials']) ? $params['credentials'] : array();
        }
        $this->configuration['credentials']['create'] = $this->configuration['credentials']['new'];
        $this->configuration['credentials']['update'] = $this->configuration['credentials']['edit'];
    }
    protected function parseVariables($context, $key)
    {
        preg_match_all('/%%([^%]+)%%/', $this->configuration[$context][$key], $matches, PREG_PATTERN_ORDER);
        foreach ($matches[1] as $name) {
            list($name, $flag) = ModelGeneratorConfigurationField::splitFieldWithFlag($name);
            if (!isset($this->configuration[$context]['fields'][$name])) {
                $this->configuration[$context]['fields'][$name] = new ModelGeneratorConfigurationField($name, array_merge(array('type' => 'Text', 'label' => Inflector::humanize(Inflector::underscore($name))), isset($config['default'][$name]) ? $config['default'][$name] : array(), isset($config[$context][$name]) ? $config[$context][$name] : array(), array('flag' => $flag)));
            } else {
                $this->configuration[$context]['fields'][$name]->setFlag($flag);
            }
            $this->configuration[$context][$key] = str_replace('%%' . $flag . $name . '%%', '%%' . $name . '%%', $this->configuration[$context][$key]);
        }
    }
    protected function mapFieldName(ModelGeneratorConfigurationField $field)
    {
        return $field->getName();
    }
    protected function fixActionParameters($action, $parameters)
    {
        if (null === $parameters) {
            $parameters = array();
        }
        if (!isset($parameters['params'])) {
            $parameters['params'] = array();
        }
        if ('_delete' == $action && !isset($parameters['confirm'])) {
            $parameters['confirm'] = 'Are you sure?';
        }
        $parameters['class_suffix'] = strtolower('_' == $action[0] ? substr($action, 1) : $action);
        // merge with defaults
        $defaults = $this->getActionsDefault();
        if (isset($defaults[$action])) {
            $parameters = array_merge($defaults[$action], $parameters);
        }
        if (isset($parameters['label'])) {
            $label = $parameters['label'];
        } elseif ('_' != $action[0]) {
            $label = $action;
        } else {
            $label = '_list' == $action ? 'Back to list' : substr($action, 1);
        }
        $parameters['label'] = Inflector::humanize($label);
        return $parameters;
    }
    protected function getConfig()
    {
        return array('default' => $this->getFieldsDefault(), 'list' => $this->getFieldsList(), 'filter' => $this->getFieldsFilter(), 'form' => $this->getFieldsForm(), 'new' => $this->getFieldsNew(), 'edit' => $this->getFieldsEdit());
    }
}
class_alias(ModelGeneratorConfiguration::class, 'sfModelGeneratorConfiguration', false);